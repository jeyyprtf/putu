<?php

function notification_gateway(array $input): array
{
    $directory = env('PUTU_NOTIFICATION_DIR') ?? getenv('HOME') . '/.config/putu/notifications';
    $socket = @stream_socket_client('unix://' . $directory . '/gateway.sock', $errno, $error, 0.5);
    if ($socket === false) {
        return ['code' => 503];
    }
    try {
        stream_set_timeout($socket, 8);
        $request = json_encode($input, JSON_THROW_ON_ERROR) . "\n";
        while ($request !== '') {
            $written = @fwrite($socket, $request);
            if (!$written) {
                return ['code' => 503];
            }
            $request = substr($request, $written);
        }
        return json_decode((string) fgets($socket, 4096), true) ?? ['code' => 503];
    } finally {
        fclose($socket);
    }
}

function notification_whatsapp_url(int $userId, string $sender): string
{
    if (!preg_match('/^\d{8,15}$/D', $sender)) {
        throw new InvalidArgumentException('WhatsApp PUTU belum siap.');
    }
    $token = bin2hex(random_bytes(24));
    db_exec(
        "INSERT INTO putu_bot.accounts (id_user, wa_hash, wa_expires)
         VALUES (:id, :hash, CURRENT_TIMESTAMP + INTERVAL '10 minutes')
         ON CONFLICT (id_user) DO UPDATE SET wa_hash = EXCLUDED.wa_hash, wa_expires = EXCLUDED.wa_expires
         WHERE putu_bot.accounts.wa_chat IS NULL",
        ['id' => $userId, 'hash' => hash('sha256', $token)]
    );
    return 'https://wa.me/' . $sender . '?text=' . rawurlencode('PUTU ' . $token);
}

function notification_whatsapp_command(string $sender, string $chat, string $command): string
{
    if (!preg_match('/^\d{8,15}$/D', $sender) || !preg_match('/^\d{5,30}@(c\.us|lid)$/D', $chat)) {
        throw new InvalidArgumentException('WhatsApp hanya dapat dihubungkan dari chat pribadi.');
    }
    if (strcasecmp($command, 'STOP') === 0) {
        db_exec('UPDATE putu_bot.accounts SET wa_chat = NULL, wa_sender = NULL, wa_connected_at = NULL,
                 wa_hash = NULL, wa_expires = NULL WHERE wa_chat = :chat AND wa_sender = :sender',
                ['chat' => $chat, 'sender' => $sender]);
        return 'Notifikasi WhatsApp PUTU dihentikan.';
    }
    if (!preg_match('/^PUTU ([a-f0-9]{48})$/iD', $command, $match)) {
        throw new InvalidArgumentException('Kode koneksi WhatsApp tidak valid.');
    }
    $linked = db_exec(
        'UPDATE putu_bot.accounts SET wa_chat = :chat, wa_sender = :sender, wa_connected_at = CURRENT_TIMESTAMP,
             wa_hash = NULL, wa_expires = NULL
         WHERE wa_hash = :hash AND wa_expires > CURRENT_TIMESTAMP AND wa_chat IS NULL
           AND NOT EXISTS (SELECT 1 FROM putu_bot.accounts WHERE wa_chat = :existing)',
        ['chat' => $chat, 'sender' => $sender, 'hash' => hash('sha256', strtolower($match[1])), 'existing' => $chat]
    );
    return $linked ? 'WhatsApp terhubung ke PUTU. Kirim STOP untuk berhenti.' : 'Tautan tidak berlaku atau WhatsApp sudah terhubung.';
}

function notification_web_save(int $userId, string $session, string $input): void
{
    $value = strlen($input) < 4096 ? json_decode($input, true) : null;
    $endpoint = $value['endpoint'] ?? null;
    $keys = $value['keys'] ?? [];
    $p256dh = $keys['p256dh'] ?? null;
    $auth = $keys['auth'] ?? null;
    if (!is_string($endpoint) || !filter_var($endpoint, FILTER_VALIDATE_URL)
        || !str_starts_with($endpoint, 'https://') || strlen($endpoint) > 2048
        || !is_string($p256dh) || !preg_match('/^[A-Za-z0-9_-]{87}$/D', $p256dh)
        || !is_string($auth) || !preg_match('/^[A-Za-z0-9_-]{22}$/D', $auth)) {
        throw new InvalidArgumentException('Langganan notifikasi browser tidak valid.');
    }
    $subscription = json_encode(['endpoint' => $endpoint, 'keys' => ['p256dh' => $p256dh, 'auth' => $auth]], JSON_THROW_ON_ERROR);
    try {
        db_exec(
            'INSERT INTO putu_bot.accounts (id_user, web_subscription, web_session, web_connected_at)
             VALUES (:id, :subscription, :session, CURRENT_TIMESTAMP)
             ON CONFLICT (id_user) DO UPDATE SET web_subscription = EXCLUDED.web_subscription,
                web_session = EXCLUDED.web_session, web_connected_at = EXCLUDED.web_connected_at',
            ['id' => $userId, 'subscription' => $subscription, 'session' => $session]
        );
    } catch (PDOException $error) {
        if ($error->getCode() !== '23505') {
            throw $error;
        }
        throw new InvalidArgumentException('Browser ini masih terhubung ke akun PUTU lain.');
    }
}

function notification_disconnect(int $userId, string $channel, ?string $session = null): void
{
    if ($channel === 'web') {
        db_exec('UPDATE putu_bot.accounts SET web_subscription = NULL, web_session = NULL, web_connected_at = NULL
                 WHERE id_user = :id AND (CAST(:session AS text) IS NULL OR web_session = :browser)',
                ['id' => $userId, 'session' => $session, 'browser' => $session]);
    } elseif ($channel === 'whatsapp') {
        db_exec('UPDATE putu_bot.accounts SET wa_chat = NULL, wa_sender = NULL, wa_connected_at = NULL,
                 wa_hash = NULL, wa_expires = NULL WHERE id_user = :id', ['id' => $userId]);
    }
}

function notification_send_gateway(string $channel, string $target, string $text, string $path, string $sender = ''): void
{
    $result = $channel === 'whatsapp'
        ? notification_gateway(['action' => $channel, 'target' => $target, 'sender' => $sender, 'text' => $text])
        : notification_gateway(['action' => $channel, 'subscription' => json_decode($target, true),
            'payload' => ['title' => 'PUTU', 'body' => $text, 'path' => $path]]);
    if ($result['code'] !== 200) {
        throw new RuntimeException('Pengiriman notifikasi gagal.', (int) $result['code']);
    }
}
