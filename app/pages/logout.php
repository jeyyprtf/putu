<?php
// Logout hanya lewat POST + token CSRF agar tidak bisa dipicu dari link/gambar situs lain.
require_once __DIR__ . '/../bootstrap.php';

if (is_post() && current_user() !== null && csrf_verify()) {
    if (isset($_SESSION['notification_web'])) {
        require_once __DIR__ . '/../notification-channels.php';
        notification_disconnect((int) current_user()['id_user'], 'web', $_SESSION['notification_web']);
    }
    logout_user();
}

redirect(url('login'));
