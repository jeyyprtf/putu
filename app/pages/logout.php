<?php
// Logout hanya lewat POST + token CSRF agar tidak bisa dipicu dari link/gambar situs lain.
require_once __DIR__ . '/../bootstrap.php';

if (is_post() && current_user() !== null && csrf_verify()) {
    logout_user();
}

redirect(url('login'));
