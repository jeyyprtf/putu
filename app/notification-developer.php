<?php

function notification_developer_https(): bool
{
    return in_array(strtolower((string) ($_SERVER['HTTPS'] ?? '')), ['on', '1'], true)
        || (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
            && env('PUTU_DEVELOPER_TUNNEL_HOST', '') !== ''
            && strtolower($_SERVER['HTTP_HOST'] ?? '') === env('PUTU_DEVELOPER_TUNNEL_HOST')
            && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            && isset($_SERVER['HTTP_CF_RAY']));
}

function notification_developer_local(): bool
{
    return in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
        && preg_match('/^(?:localhost|127\.0\.0\.1|\[::1\])(?::\d{1,5})?$/iD', $_SERVER['HTTP_HOST'] ?? '') === 1
        && !isset($_SERVER['HTTP_FORWARDED']) && !isset($_SERVER['HTTP_X_FORWARDED_FOR']);
}

function notification_check_telegram(string $token): string
{
    $bot = telegram_api_request($token, 'getMe');
    $username = $bot['username'] ?? '';
    $botId = explode(':', $token, 2)[0];
    if (!is_string($username) || !preg_match('/^[A-Za-z0-9_]{5,32}$/D', $username)
        || (string) ($bot['id'] ?? '') !== $botId) {
        throw new RuntimeException('Identitas bot tidak dapat diperiksa.');
    }
    $webhook = telegram_api_request($token, 'getWebhookInfo');
    if (($webhook['url'] ?? null) !== '') {
        throw new RuntimeException('Bot masih memakai webhook. Nonaktifkan webhook bot sebelum memakai worker PUTU.');
    }
    return $username;
}

function notification_save_telegram(string $token): string
{
    $username = notification_check_telegram($token);
    $botId = explode(':', $token, 2)[0];
    $directory = notification_private_dir();
    $lock = @fopen($directory . '/telegram-bot.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('Konfigurasi Telegram tidak bisa dikunci.');
    }
    $temporary = null;
    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException('Konfigurasi Telegram sedang dipakai.');
        }
        telegram_saved_bot(true);
        $previousId = explode(':', telegram_config('TELEGRAM_BOT_TOKEN'), 2)[0];
        if (ctype_digit($previousId) && $previousId !== $botId) {
            throw new RuntimeException('Gunakan token bot yang sama agar tautan Telegram pengguna tetap berlaku.');
        }
        $json = json_encode(['TELEGRAM_BOT_TOKEN' => $token, 'TELEGRAM_BOT_USERNAME' => $username], JSON_THROW_ON_ERROR);
        $temporary = $directory . '/telegram-' . bin2hex(random_bytes(16));
        if (@touch($temporary) === false || !@chmod($temporary, 0600)
            || @file_put_contents($temporary, $json) !== strlen($json)
            || !@rename($temporary, $directory . '/telegram-bot.json')) {
            throw new RuntimeException('Konfigurasi Telegram gagal disimpan.');
        }
    } finally {
        if ($temporary !== null && is_file($temporary)) {
            @unlink($temporary);
        }
        fclose($lock);
    }
    return $username;
}

function notification_developer_status(): array
{
    $wa = notification_gateway(['action' => 'wa_debug']);
    return [
        'label' => match ($wa['state'] ?? '') {
            'ready' => 'Terhubung',
            'starting' => 'Sedang menyambungkan…',
            'pairing' => 'Menunggu scan QR',
            'offline' => 'Koneksi terputus',
            'disabled' => 'Belum diaktifkan',
            default => 'Gateway tidak dapat dihubungi',
        },
        'reconnect' => ($wa['reconnect'] ?? false) === true,
        'image' => $wa['image'] ?? null,
        'sender' => $wa['sender'] ?? '',
    ];
}
