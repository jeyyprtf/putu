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
            WHERE r.reminders_enabled AND t.aktif = TRUE AND COALESCE(p.status, 'todo') <> 'done'
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

function notification_excerpt(string $text, int $limit): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    if (preg_match('/^(.{' . $limit . '})./u', $text, $match)) {
        return rtrim($match[1]) . '…';
    }
    return $text;
}

function notification_message(array $item): array
{
    $web = $item['channel'] === 'web';
    $timezone = new DateTimeZone('Asia/Jakarta');
    if ($item['kind'] === 'test') {
        $time = (new DateTimeImmutable('now', $timezone))->format('H:i:s');
        return [
            'title' => 'Tes notifikasi PUTU',
            'body' => "Notifikasi aktif · {$time} WIB",
            'path' => 'app/pages/pengaturan.php',
            'action' => 'Buka pengaturan',
        ];
    }

    $subject = notification_excerpt($item['title'], $web ? 70 : 200);
    if ($item['kind'] === 'deadline') {
        $deadline = (new DateTimeImmutable($item['deadline']))->setTimezone($timezone)->format('d-m-Y H:i');
        $course = notification_excerpt($item['course'], $web ? 60 : 150);
        $details = "Deadline {$deadline} WIB\n{$course}";
        return [
            'title' => $web ? "PUTU · {$subject}" : '⏰ PUTU · Pengingat tugas',
            'body' => $web ? $details : "{$subject}\n{$details}",
            'path' => 'app/pages/tugas.php',
            'action' => 'Buka tugas di PUTU',
        ];
    }

    $preview = notification_excerpt($item['body'], $web ? 100 : 320);
    return [
        'title' => $web ? "PUTU · {$subject}" : '📢 PUTU · Pengumuman kelas',
        'body' => $web ? $preview : "{$subject}\n\n{$preview}",
        'path' => 'app/pages/pengumuman.php',
        'action' => 'Buka pengumuman di PUTU',
    ];
}

function notification_url(string $path): string
{
    $base = rtrim(trim(telegram_config('APP_URL')), '/');
    if (strlen($base) > 500 || !filter_var($base, FILTER_VALIDATE_URL)
        || !preg_match('~^https?://~i', $base) || strpbrk($base, '?#') !== false
        || parse_url($base, PHP_URL_USER) !== null) {
        $base = 'https://putu.juan.web.id';
    }
    return $base . '/' . $path;
}

function notification_telegram_text(array $message): string
{
    $title = htmlspecialchars($message['title'], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $body = htmlspecialchars($message['body'], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $url = notification_url($message['path']);
    $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    // Tampilkan alamat lokal sebagai teks saat tes.
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $link = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true)
        ? "{$message['action']}\n{$escapedUrl}"
        : "<a href=\"{$escapedUrl}\">{$message['action']}</a>";
    return "<b>{$title}</b>\n{$body}\n\n{$link}";
}

/** Periksa ulang akses dan pilihan terbaru sebelum setiap kiriman. */
function notification_deliver(array $channels, callable $send, array &$retryAt = []): int
{
    $sent = 0;
    foreach ($channels as $channel) {
        if (($retryAt[$channel] ?? 0) > time()) {
            continue;
        }
        foreach (notification_pending([$channel]) as $item) {
            $current = notification_pending([$channel], $item)[0] ?? null;
            if ($current === null || $current['target'] !== $item['target'] || $current['connected'] !== $item['connected']) {
                continue;
            }
            $ttl = $current['kind'] === 'deadline' ? min(3600, strtotime($current['deadline']) - time()) : 3600;
            if ($ttl < 1) {
                continue;
            }
            try {
                $send($current, notification_message($current), $ttl);
            } catch (RuntimeException $error) {
                $code = $error->getCode();
                error_log('[notifikasi] ' . $channel . ' gagal, kode ' . $code . '.');
                if ($channel === 'telegram' && $code === 403) {
                    db_exec('UPDATE putu_bot.accounts SET chat_id = NULL, connected_at = NULL
                             WHERE id_user = :id AND chat_id = :target AND connected_at = :connected',
                            ['id' => $item['id_user'], 'target' => $item['target'], 'connected' => $item['connected']]);
                } elseif ($channel === 'web' && in_array($code, [400, 404, 410], true)) {
                    notification_web_expire((int) $item['id_user'], $item['target'], $item['connected']);
                } elseif ($code >= 500 || in_array($code, [0, 401, 409, 429], true)) {
                    $retryAfter = $error instanceof NotificationGatewayException
                        || ($code === 429 && $error instanceof TelegramApiException)
                        ? $error->retryAfter : 0;
                    $retryAt[$channel] = time() + ($retryAfter > 0 ? $retryAfter : 30);
                    break;
                }
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
    }
    return $sent;
}
