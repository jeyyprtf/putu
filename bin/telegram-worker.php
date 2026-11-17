<?php
// Hanya CLI: worker tidak boleh dijalankan lewat browser.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/telegram.php';
require __DIR__ . '/../app/telegram-api.php';

if (isset($argv[1]) && $argv[1] !== '--check') {
    fwrite(STDERR, "Pemakaian: php bin/telegram-worker.php [--check]\n");
    exit(1);
}

try {
    if (!telegram_configured()) {
        throw new RuntimeException('Isi app/config/telegram.local.php sesuai contoh terlebih dahulu.');
    }
    $me = telegram_api('getMe');
    if (strcasecmp($me['username'], telegram_config('TELEGRAM_BOT_USERNAME')) !== 0) {
        throw new RuntimeException('Username konfigurasi tidak cocok dengan token bot.');
    }
    $webhook = telegram_api('getWebhookInfo');
    if ($webhook['url'] !== '') {
        throw new RuntimeException('Bot masih memakai webhook. Nonaktifkan webhook bot ini sebelum menggunakan worker polling.');
    }
    db_one('SELECT update_offset FROM putu_bot.state WHERE singleton');
    if (($argv[1] ?? '') === '--check') {
        echo "OK: bot @{$me['username']}, koneksi database, dan tabel Telegram siap.\n";
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
$send = function (int $chatId, string $text) use (&$lastSent): void {
    $wait = max(0, ($lastSent[$chatId] ?? 0) + 1.1 - microtime(true));
    if ($wait > 0) {
        usleep((int) ($wait * 1000000));
    }
    try {
        telegram_api('sendMessage', ['chat_id' => $chatId, 'text' => $text,
            'link_preview_options' => ['is_disabled' => true]]);
    } finally {
        $lastSent[$chatId] = microtime(true);
        usleep(50000);
    }
};

echo "Bot @{$me['username']} berjalan. Ctrl+C untuk berhenti.\n";
while (true) {
    try {
        $state = db_one('SELECT update_offset FROM putu_bot.state WHERE singleton');
        $updates = telegram_api('getUpdates', ['offset' => (int) $state['update_offset'],
            'timeout' => 25, 'allowed_updates' => ['message']]);
        foreach ($updates as $update) {
            try {
                telegram_handle_update($update, $send);
            } catch (TelegramApiException $e) {
                if ($e->getCode() !== 403) {
                    throw $e;
                }
                $chatId = $update['message']['chat']['id'] ?? 0;
                $account = db_one('SELECT id_user FROM putu_bot.accounts WHERE chat_id = :chat', ['chat' => $chatId]);
                if ($account !== null) {
                    telegram_disconnect((int) $account['id_user']);
                }
            }
            db_exec('UPDATE putu_bot.state SET update_offset = :offset WHERE singleton',
                ['offset' => $update['update_id'] + 1]);
        }
        telegram_deliver($send);
    } catch (PDOException $e) {
        // Koneksi yang hilang juga melepas advisory lock. Keluar agar supervisor membuka sesi baru.
        fwrite(STDERR, "[telegram] Koneksi/query database gagal. Periksa database lalu jalankan ulang worker.\n");
        exit(1);
    } catch (TelegramApiException $e) {
        fwrite(STDERR, $e->getMessage() . "\n");
        if (in_array($e->getCode(), [401, 409], true)) {
            exit(1);
        }
        sleep($e->retryAfter);
    } catch (Throwable $e) {
        fwrite(STDERR, "[telegram] Worker gagal; periksa konfigurasi dan database.\n");
        exit(1);
    }
}
