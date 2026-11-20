<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/notification-channels.php';

try {
    echo notification_whatsapp_command($argv[1] ?? '', $argv[2] ?? '', trim((string) fgets(STDIN, 100))), "\n";
} catch (Throwable) {
    exit(1);
}
