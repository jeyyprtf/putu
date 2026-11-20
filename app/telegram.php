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
