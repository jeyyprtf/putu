<?php
// Hanya CLI: worker tidak boleh dijalankan lewat browser.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/telegram.php';
require __DIR__ . '/../app/telegram-api.php';
require __DIR__ . '/../app/notification-channels.php';
require __DIR__ . '/../app/notifications.php';

if (isset($argv[1]) && $argv[1] !== '--check') {
    fwrite(STDERR, "Pemakaian: php bin/telegram-worker.php [--check]\n");
    exit(1);
}

try {
    db_one('SELECT update_offset FROM putu_bot.state WHERE singleton');
    db_one('SELECT channel FROM putu_bot.deliveries LIMIT 1');
    db_one('SELECT aktif FROM public.tugas LIMIT 1');
    if (($argv[1] ?? '') === '--check') {
        $me = telegram_api('getMe');
        if (strcasecmp($me['username'], telegram_config('TELEGRAM_BOT_USERNAME')) !== 0) {
            throw new RuntimeException('Username konfigurasi tidak cocok dengan token bot.');
        }
        if (telegram_api('getWebhookInfo')['url'] !== '') {
            throw new RuntimeException('Bot masih memakai webhook.');
        }
        $gateway = notification_gateway(['action' => 'status']);
        echo "OK: Telegram dan database siap. Web Push " . (!empty($gateway['publicKey']) ? 'siap' : 'belum siap')
            . ', WhatsApp ' . (!empty($gateway['sender']) ? 'siap' : 'belum terhubung') . ".\n";
        exit;
    }
    $lock = db_one('SELECT pg_try_advisory_lock(1886745717, 1) AS locked');
    if (!$lock['locked']) {
        throw new RuntimeException('Worker PUTU lain sudah berjalan pada database ini.');
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[telegram] ' . $e->getMessage() . "\n");
    exit(1);
}

// Batasi laju global dan tiap chat tanpa antrean/dependensi tambahan.
$lastSent = [];
$send = function (int $chatId, string $text, ?string $parseMode = null) use (&$lastSent): void {
    $wait = max(0, ($lastSent[$chatId] ?? 0) + 1.1 - microtime(true));
    if ($wait > 0) {
        usleep((int) ($wait * 1000000));
    }
    try {
        $params = ['chat_id' => $chatId, 'text' => $text, 'link_preview_options' => ['is_disabled' => true]];
        if ($parseMode !== null) {
            $params['parse_mode'] = $parseMode;
        }
        telegram_api('sendMessage', $params);
    } finally {
        $lastSent[$chatId] = microtime(true);
        usleep(50000);
    }
};

echo "Worker notifikasi PUTU berjalan.\n";
$retryAt = [];
while (true) {
    try {
        $channels = [];
        if (telegram_configured() && time() >= ($retryAt['telegram'] ?? 0)) {
            try {
                $state = db_one('SELECT update_offset FROM putu_bot.state WHERE singleton');
                $updates = telegram_api('getUpdates', ['offset' => (int) $state['update_offset'],
                    'timeout' => 20, 'allowed_updates' => ['message']]);
                foreach ($updates as $update) {
                    try {
                        telegram_handle_update($update, $send);
                    } catch (TelegramApiException $error) {
                        if ($error->getCode() !== 403) throw $error;
                    }
                    db_exec('UPDATE putu_bot.state SET update_offset = :offset WHERE singleton',
                        ['offset' => $update['update_id'] + 1]);
                }
                $channels[] = 'telegram';
            } catch (TelegramApiException $error) {
                error_log($error->getMessage());
                $retryAt['telegram'] = time() + max(20, $error->retryAfter);
            }
        }
        $gateway = notification_gateway(['action' => 'status']);
        if (!empty($gateway['publicKey'])) $channels[] = 'web';
        if (!empty($gateway['sender'])) $channels[] = 'whatsapp';
        notification_deliver($channels, function (array $item, array $message, int $ttl) use ($send): void {
            if ($item['channel'] === 'telegram') {
                $send((int) $item['target'], notification_telegram_text($message), 'HTML');
                return;
            }
            notification_send_gateway($item['channel'], $item['target'], $message, $item['wa_sender'] ?? '', $ttl);
        }, $retryAt);
        if (!in_array('telegram', $channels, true)) sleep(5);
    } catch (Throwable) {
        fwrite(STDERR, "[notifikasi] Worker gagal. Periksa koneksi database dan konfigurasi.\n");
        exit(1);
    }
}
