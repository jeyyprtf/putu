<?php

/** Satu aturan deadline dan pengumuman untuk semua kanal. */
function notification_pending(array $channels, ?array $previous = null): array
{
    $params = ['channels' => '{' . implode(',', $channels) . '}'];
    $only = '';
    if ($previous !== null) {
        $only = 'AND (n.id_user, n.kind, n.source_id, n.version, n.channel) = (:user, :kind, :source, :version, :channel)';
        $params += ['user' => $previous['id_user'], 'kind' => $previous['kind'], 'source' => $previous['source_id'],
            'version' => $previous['version'], 'channel' => $previous['channel']];
    }
    return db_all(
        "WITH recipients AS (
            SELECT a.id_user, a.reminders_enabled, a.news_enabled, a.reminder_hours,
                   u.id_kelas, u.nama, c.channel, c.target, c.connected, a.wa_sender
            FROM putu_bot.accounts a
            JOIN public.users u USING (id_user)
            CROSS JOIN LATERAL (VALUES
                ('telegram', a.chat_id::text, a.connected_at),
                ('whatsapp', a.wa_chat, a.wa_connected_at),
                ('web', a.web_subscription::text, a.web_connected_at)
            ) AS c(channel, target, connected)
            WHERE c.target IS NOT NULL AND c.channel = ANY(CAST(:channels AS text[]))
        ), candidates AS (
            SELECT r.*, 'deadline' AS kind, t.id_tugas AS source_id,
                   to_char(t.deadline AT TIME ZONE 'UTC', 'YYYY-MM-DD\"T\"HH24:MI:SS.US') AS version,
                   t.judul_tugas AS title, m.nama_matkul AS course, t.deadline, NULL AS body, t.deadline AS priority
            FROM recipients r
            JOIN public.mata_kuliah m ON m.id_kelas = r.id_kelas
            JOIN public.tugas t USING (id_matkul)
            LEFT JOIN public.progres_tugas p ON p.id_user = r.id_user AND p.id_tugas = t.id_tugas
            WHERE r.reminders_enabled AND COALESCE(p.status, 'todo') <> 'done'
              AND t.deadline > CURRENT_TIMESTAMP
              AND t.deadline <= CURRENT_TIMESTAMP + r.reminder_hours * INTERVAL '1 hour'
            UNION ALL
            SELECT r.*, 'pengumuman', p.id_pengumuman, '', p.judul, NULL, NULL, p.isi_pengumuman, p.created_at
            FROM recipients r
            JOIN public.pengumuman p ON p.id_kelas = r.id_kelas
            WHERE r.news_enabled AND p.created_at >= r.connected
        )
        SELECT n.* FROM candidates n
        WHERE NOT EXISTS (
            SELECT 1 FROM putu_bot.deliveries d
            WHERE (d.id_user, d.kind, d.source_id, d.version, d.channel) =
                  (n.id_user, n.kind, n.source_id, n.version, n.channel)
        ) $only
        ORDER BY n.priority, n.id_user, n.kind, n.source_id, n.channel LIMIT 100",
        $params
    );
}

function notification_text(array $item): string
{
    if ($item['kind'] === 'deadline') {
        $deadline = (new DateTimeImmutable($item['deadline']))->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $text = "Hai {$item['nama']}, ada tugas yang mendekati deadline.\n\n{$item['title']}\n{$item['course']}\nDeadline: "
            . $deadline->format('d-m-Y H:i') . " WIB\n\nTandai selesai di PUTU kalau sudah beres.";
        $page = 'tugas';
    } else {
        // Sisakan ruang agar judul, nama, dan tautan tetap di bawah batas pesan Telegram.
        $limit = $item['channel'] === 'web' ? 600 : 2500;
        preg_match('/^.{0,' . $limit . '}/su', $item['body'], $match);
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

/** Periksa ulang akses dan pilihan terbaru sebelum setiap kiriman. */
function notification_deliver(array $channels, callable $send): int
{
    $sent = 0;
    $paused = [];
    foreach (notification_pending($channels) as $item) {
        $channel = $item['channel'];
        if (isset($paused[$channel])) {
            continue;
        }
        $current = notification_pending([$channel], $item)[0] ?? null;
        if ($current === null || $current['target'] !== $item['target'] || $current['connected'] !== $item['connected']) {
            continue;
        }
        try {
            $send($current, notification_text($current));
        } catch (RuntimeException $error) {
            $code = $error->getCode();
            if ($channel === 'telegram' && $code === 403) {
                db_exec('UPDATE putu_bot.accounts SET chat_id = NULL, connected_at = NULL
                         WHERE id_user = :id AND chat_id = :target', ['id' => $item['id_user'], 'target' => $item['target']]);
            } elseif ($channel === 'web' && in_array($code, [404, 410], true)) {
                db_exec('UPDATE putu_bot.accounts SET web_subscription = NULL, web_session = NULL, web_connected_at = NULL
                         WHERE id_user = :id AND web_subscription = :target', ['id' => $item['id_user'], 'target' => $item['target']]);
            } elseif (in_array($code, [401, 409, 429, 503], true)) {
                $paused[$channel] = true;
            }
            error_log('[notifikasi] ' . $channel . ' gagal, kode ' . $code . '.');
            continue;
        }
        // shortcut: respons yang hilang setelah kiriman diterima bisa menyebabkan duplikat saat dicoba ulang.
        db_exec(
            'INSERT INTO putu_bot.deliveries (id_user, kind, source_id, version, channel)
             VALUES (:user, :kind, :source, :version, :channel) ON CONFLICT DO NOTHING',
            ['user' => $item['id_user'], 'kind' => $item['kind'], 'source' => $item['source_id'],
                'version' => $item['version'], 'channel' => $channel]
        );
        $sent++;
    }
    return $sent;
}
