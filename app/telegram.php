<?php
/** Telegram: koneksi akun, preferensi, dan pemilihan notifikasi dari data PUTU. */

function telegram_config(string $key): string
{
    static $local = null;
    if ($local === null) {
        $file = __DIR__ . '/config/telegram.local.php';
        $local = is_file($file) ? require $file : [];
    }
    return env($key) ?? (string) ($local[$key] ?? '');
}

function telegram_configured(): bool
{
    return preg_match('/^\d+:[A-Za-z0-9_-]+$/D', telegram_config('TELEGRAM_BOT_TOKEN')) === 1
        && preg_match('/^[A-Za-z0-9_]{5,32}$/D', telegram_config('TELEGRAM_BOT_USERNAME')) === 1;
}

function telegram_account(int $idUser): array
{
    return db_one('SELECT * FROM putu_bot.accounts WHERE id_user = :id', ['id' => $idUser])
        ?? ['chat_id' => null, 'reminder_hours' => 24, 'reminders_enabled' => true, 'news_enabled' => true];
}

function telegram_preferences(int $idUser, string $hours, bool $reminders, bool $news): void
{
    if (!ctype_digit($hours) || strlen($hours) > 3 || (int) $hours < 1 || (int) $hours > 168) {
        throw new InvalidArgumentException('Waktu pengingat harus berupa angka bulat 1–168 jam.');
    }
    db_exec(
        'INSERT INTO putu_bot.accounts (id_user, reminder_hours, reminders_enabled, news_enabled)
         VALUES (:id, :hours, :reminders, :news)
         ON CONFLICT (id_user) DO UPDATE SET reminder_hours = EXCLUDED.reminder_hours,
             reminders_enabled = EXCLUDED.reminders_enabled, news_enabled = EXCLUDED.news_enabled',
        ['id' => $idUser, 'hours' => (int) $hours, 'reminders' => $reminders ? 'true' : 'false', 'news' => $news ? 'true' : 'false']
    );
}

function telegram_connect_url(int $idUser): string
{
    if (!telegram_configured()) {
        throw new RuntimeException('Konfigurasi bot Telegram belum lengkap.');
    }
    $token = bin2hex(random_bytes(24));
    db_exec(
        "INSERT INTO putu_bot.accounts (id_user, connect_hash, connect_expires)
         VALUES (:id, :hash, CURRENT_TIMESTAMP + INTERVAL '10 minutes')
         ON CONFLICT (id_user) DO UPDATE SET connect_hash = EXCLUDED.connect_hash,
             connect_expires = EXCLUDED.connect_expires",
        ['id' => $idUser, 'hash' => hash('sha256', $token)]
    );
    return 'https://t.me/' . telegram_config('TELEGRAM_BOT_USERNAME') . '?start=' . $token;
}

function telegram_disconnect(int $idUser): void
{
    db_exec(
        'UPDATE putu_bot.accounts SET chat_id = NULL, connected_at = NULL,
             connect_hash = NULL, connect_expires = NULL WHERE id_user = :id',
        ['id' => $idUser]
    );
}

/** Pesan tes selalu menuju akun yang terhubung ke user login, bukan chat_id dari form. */
function telegram_test_notification(int $idUser, callable $send): void
{
    if (!telegram_configured()) {
        throw new InvalidArgumentException('Bot Telegram belum diaktifkan oleh pengelola PUTU.');
    }
    $account = telegram_account($idUser);
    if ($account['chat_id'] === null) {
        throw new InvalidArgumentException('Hubungkan akun Telegram terlebih dahulu sebelum mengirim pesan tes.');
    }
    $time = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('d-m-Y H:i:s');
    $send((int) $account['chat_id'], "🔔 Tes notifikasi PUTU\n\nKalau pesan ini masuk, koneksi notifikasi Telegram kamu sudah berfungsi.\nWaktu tes: {$time} WIB\n\nIni pesan percobaan, bukan pengingat deadline.");
}

/** Hanya private chat yang boleh menghubungkan akun atau menerima data pribadi. */
function telegram_handle_update(array $update, callable $send): void
{
    $message = $update['message'] ?? [];
    $chat = $message['chat'] ?? [];
    $chatId = $chat['id'] ?? null;
    if (($chat['type'] ?? '') !== 'private' || !is_int($chatId) || $chatId < 1
        || ($message['from']['id'] ?? null) !== $chatId || !is_string($message['text'] ?? null)) {
        return;
    }
    $text = trim($message['text']);
    if (preg_match('/^\/start ([a-f0-9]{48})$/D', $text, $match)) {
        // Satu UPDATE mengonsumsi token; akun Telegram lain tidak dapat menimpa koneksi yang ada.
        $account = db_one(
            'UPDATE putu_bot.accounts SET chat_id = :chat, connected_at = CURRENT_TIMESTAMP,
                 connect_hash = NULL, connect_expires = NULL
             WHERE connect_hash = :hash AND connect_expires > CURRENT_TIMESTAMP
               AND NOT EXISTS (SELECT 1 FROM putu_bot.accounts WHERE chat_id = :existing)
             RETURNING id_user',
            ['chat' => $chatId, 'hash' => hash('sha256', $match[1]), 'existing' => $chatId]
        );
        $send($chatId, $account === null
            ? 'Tautan tidak berlaku atau Telegram ini sudah terhubung. Cek Pengaturan PUTU; putuskan koneksi lama sebelum menghubungkan ulang.'
            : 'Telegram berhasil terhubung ke PUTU! Atur waktu pengingat di Pengaturan PUTU. Ketik /stop untuk memutus koneksi.');
    } elseif ($text === '/stop') {
        $account = db_one('SELECT id_user FROM putu_bot.accounts WHERE chat_id = :chat', ['chat' => $chatId]);
        if ($account !== null) {
            telegram_disconnect((int) $account['id_user']);
        }
        $send($chatId, 'Koneksi PUTU diputus. Kamu tidak akan menerima notifikasi lagi. Hubungkan kembali lewat Pengaturan PUTU.');
    } else {
        $send($chatId, "Halo! Aku bot pengingat PUTU.\n\nHubungkan akun lewat Pengaturan PUTU → Hubungkan Telegram, lalu tekan Start.\nAtur pengingat deadline dan notifikasi pengumuman di web.\n/stop — putuskan koneksi\n\nFitur chat asisten belum tersedia.");
    }
}

/** Gunakan timestamp asli; tanggal yang dipotong untuk tampilan web tidak cukup. */
function telegram_pending(): array
{
    $reminders = db_all(
        "SELECT a.id_user, a.chat_id, 'deadline' AS kind, t.id_tugas AS source_id,
                to_char(t.deadline AT TIME ZONE 'UTC', 'YYYY-MM-DD\"T\"HH24:MI:SS.US') AS version,
                u.nama, t.judul_tugas AS title, m.nama_matkul AS course, t.deadline
         FROM putu_bot.accounts a
         JOIN public.users u ON u.id_user = a.id_user
         JOIN public.mata_kuliah m ON m.id_kelas = u.id_kelas
         JOIN public.tugas t ON t.id_matkul = m.id_matkul
         LEFT JOIN public.progres_tugas p ON p.id_user = u.id_user AND p.id_tugas = t.id_tugas
         WHERE a.chat_id IS NOT NULL AND a.reminders_enabled
           AND COALESCE(p.status, 'todo') <> 'done'
           AND t.deadline > CURRENT_TIMESTAMP
           AND t.deadline <= CURRENT_TIMESTAMP + a.reminder_hours * INTERVAL '1 hour'
           AND NOT EXISTS (
               SELECT 1 FROM putu_bot.deliveries d WHERE d.id_user = a.id_user
                 AND d.kind = 'deadline' AND d.source_id = t.id_tugas
                 AND d.version = to_char(t.deadline AT TIME ZONE 'UTC', 'YYYY-MM-DD\"T\"HH24:MI:SS.US'))
         ORDER BY t.deadline, a.id_user LIMIT 100"
    );
    $news = db_all(
        "SELECT a.id_user, a.chat_id, 'pengumuman' AS kind, p.id_pengumuman AS source_id,
                '' AS version, u.nama, p.judul AS title, p.isi_pengumuman AS body
         FROM putu_bot.accounts a
         JOIN public.users u ON u.id_user = a.id_user
         JOIN public.pengumuman p ON p.id_kelas = u.id_kelas
         WHERE a.chat_id IS NOT NULL AND a.news_enabled AND p.created_at >= a.connected_at
           AND NOT EXISTS (
               SELECT 1 FROM putu_bot.deliveries d WHERE d.id_user = a.id_user
                 AND d.kind = 'pengumuman' AND d.source_id = p.id_pengumuman AND d.version = '')
         ORDER BY p.created_at, a.id_user LIMIT 100"
    );
    return array_merge($reminders, $news);
}

function telegram_notification_text(array $item): string
{
    if ($item['kind'] === 'deadline') {
        $deadline = (new DateTimeImmutable($item['deadline']))->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $text = "Hai {$item['nama']}, ada tugas yang mendekati deadline.\n\n{$item['title']}\n{$item['course']}\nDeadline: "
            . $deadline->format('d-m-Y H:i') . " WIB\n\nTandai selesai di PUTU kalau sudah beres.";
        $page = 'tugas';
    } else {
        // Sisakan ruang agar judul, nama, dan tautan tetap di bawah batas pesan Telegram.
        preg_match('/^.{0,2500}/su', $item['body'], $match);
        $body = $match[0] ?? '';
        $text = "Kabar kelas PUTU\n\n{$item['title']}\n\n" . $body . ($body !== $item['body'] ? '…' : '');
        $page = 'pengumuman';
    }
    $base = rtrim(telegram_config('APP_URL'), '/');
    if (filter_var($base, FILTER_VALIDATE_URL) && preg_match('~^https?://~', $base) && strlen($base) <= 500) {
        $text .= "\n\n" . $base . '/app/pages/' . $page . '.php';
    }
    return $text;
}

/** Dipanggil satu worker; callback memisahkan pengiriman HTTP dari logika data. */
function telegram_deliver(callable $send): int
{
    $sent = 0;
    foreach (telegram_pending() as $item) {
        // Baca ulang sebelum kirim: putus koneksi/ganti kelas/selesaikan tugas membatalkan antrean lama.
        $account = db_one(
            'SELECT a.chat_id, a.reminders_enabled, a.news_enabled, u.id_kelas
             FROM putu_bot.accounts a JOIN public.users u USING (id_user) WHERE a.id_user = :id',
            ['id' => $item['id_user']]
        );
        if ($account === null || $account['chat_id'] !== $item['chat_id']) {
            continue;
        }
        if ($item['kind'] === 'deadline') {
            $valid = $account['reminders_enabled'] && db_one(
                "SELECT 1 FROM public.tugas t JOIN public.mata_kuliah m USING (id_matkul)
                 JOIN putu_bot.accounts a ON a.id_user = :user
                 LEFT JOIN public.progres_tugas p ON p.id_tugas = t.id_tugas AND p.id_user = a.id_user
                 WHERE t.id_tugas = :task AND m.id_kelas = :kelas AND COALESCE(p.status, 'todo') <> 'done'
                   AND t.deadline > CURRENT_TIMESTAMP
                   AND t.deadline <= CURRENT_TIMESTAMP + a.reminder_hours * INTERVAL '1 hour'
                   AND to_char(t.deadline AT TIME ZONE 'UTC', 'YYYY-MM-DD\"T\"HH24:MI:SS.US') = :version",
                ['user' => $item['id_user'], 'task' => $item['source_id'], 'kelas' => $account['id_kelas'], 'version' => $item['version']]
            ) !== null;
        } else {
            $valid = $account['news_enabled'] && db_one(
                'SELECT 1 FROM public.pengumuman WHERE id_pengumuman = :id AND id_kelas = :kelas',
                ['id' => $item['source_id'], 'kelas' => $account['id_kelas']]
            ) !== null;
        }
        if (!$valid) {
            continue;
        }
        try {
            $send((int) $item['chat_id'], telegram_notification_text($item));
        } catch (RuntimeException $e) {
            if ($e->getCode() === 403) {
                telegram_disconnect((int) $item['id_user']);
            } elseif (in_array($e->getCode(), [401, 409, 429], true)) {
                throw $e; // token, polling, dan cooldown berlaku untuk seluruh bot
            }
            error_log('[telegram] Pengiriman gagal untuk user ' . $item['id_user'] . ' (kode ' . $e->getCode() . ').');
            continue;
        }
        // shortcut: crash setelah pesan diterima bisa menghasilkan duplikat; tambah rekonsiliasi bila pengiriman harus diaudit.
        db_exec(
            'INSERT INTO putu_bot.deliveries (id_user, kind, source_id, version)
             VALUES (:user, :kind, :source, :version) ON CONFLICT DO NOTHING',
            ['user' => $item['id_user'], 'kind' => $item['kind'], 'source' => $item['source_id'], 'version' => $item['version']]
        );
        $sent++;
    }
    return $sent;
}
