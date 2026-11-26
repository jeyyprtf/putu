<?php

final class TelegramApiException extends RuntimeException
{
    public function __construct(int $code, public readonly int $retryAfter = 5)
    {
        parent::__construct('Telegram API gagal (kode ' . $code . ').', $code);
    }
}

/** POST HTTPS tanpa dependensi tambahan; jangan log URL yang mengandung token. */
function telegram_api(string $method, array $params = []): mixed
{
    if (!telegram_configured()) {
        throw new RuntimeException('Isi konfigurasi token dan username bot terlebih dahulu.');
    }
    return telegram_api_request(telegram_config('TELEGRAM_BOT_TOKEN'), $method, $params);
}

function telegram_api_request(string $token, string $method, array $params = []): mixed
{
    if (strlen($token) > 128 || !preg_match('/^\d+:[A-Za-z0-9_-]+$/D', $token)) {
        throw new InvalidArgumentException('Format token Telegram tidak valid.');
    }
    if (!in_array($method, ['getMe', 'getWebhookInfo', 'getUpdates', 'sendMessage'], true)) {
        throw new InvalidArgumentException('Metode Telegram tidak diizinkan.');
    }
    $context = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode((object) $params, JSON_THROW_ON_ERROR),
        'timeout' => in_array($method, ['getMe', 'getWebhookInfo'], true) ? 8 : 35,
        'ignore_errors' => true,
        'follow_location' => 0,
    ]]);
    $raw = @file_get_contents('https://api.telegram.org/bot' . $token . '/' . $method, false, $context);
    if ($raw === false) {
        throw new TelegramApiException(0);
    }
    try {
        $result = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new TelegramApiException(0);
    }
    if (!is_array($result) || ($result['ok'] ?? false) !== true) {
        throw new TelegramApiException((int) ($result['error_code'] ?? 0), max(1, (int) ($result['parameters']['retry_after'] ?? 5)));
    }
    return $result['result'];
}
