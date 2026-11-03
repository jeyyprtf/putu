<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Metode tidak diizinkan.');
}

if (!csrf_verify($_POST['csrf'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Permintaan logout tidak valid. Muat ulang halaman dan coba lagi.');
}

auth_logout();
header('Location: ' . url('login'));
exit;
