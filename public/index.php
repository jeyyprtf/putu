<?php

$root = dirname(__DIR__);
$path = explode('?', $_SERVER['REQUEST_URI'] ?? '/', 2)[0];

// Semua URL yang dibuat halaman berangkat dari root domain.
$_SERVER['SCRIPT_NAME'] = '/index.php';

if ($path === '/' || $path === '/index.php') {
    require $root . '/index.php';
    return;
}

$pages = [
    '/app/pages/home.php',
    '/app/pages/login.php',
    '/app/pages/register.php',
    '/app/pages/cek-email.php',
    '/app/pages/verifikasi-email.php',
    '/app/pages/lupa-password.php',
    '/app/pages/reset-password.php',
    '/app/pages/logout.php',
    '/app/pages/gabung.php',
    '/app/pages/dashboard.php',
    '/app/pages/tugas.php',
    '/app/pages/jadwal.php',
    '/app/pages/pengumuman.php',
    '/app/pages/anggota.php',
    '/app/pages/pengaturan.php',
    '/app/pages/developer.php',
    '/app/pages/admin/admin.php',
    '/app/pages/admin/jadwal.php',
    '/app/pages/admin/tugas.php',
    '/app/pages/admin/pengumuman.php',
];

if (in_array($path, $pages, true)) {
    require $root . $path;
    return;
}

// Pertahankan URL aset yang sudah dirujuk halaman dan browser.
$assets = [
    '/resources/css/app.css' => 'text/css; charset=utf-8',
    '/resources/img/macosWallpaper.png' => 'image/png',
    '/public/assets/images/library-study.jpg' => 'image/jpeg',
    '/public/assets/js/app.js' => 'text/javascript; charset=utf-8',
    '/public/assets/js/web-push.js' => 'text/javascript; charset=utf-8',
    '/notification-sw.js' => 'text/javascript; charset=utf-8',
];

$file = $root . $path;
if (isset($assets[$path]) && is_file($file) && is_readable($file)) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'GET' && $method !== 'HEAD') {
        header('Allow: GET, HEAD');
        http_response_code(405);
        return;
    }

    header('Content-Type: ' . $assets[$path]);
    header('Content-Length: ' . filesize($file));
    if ($method === 'GET') {
        readfile($file);
    }
    return;
}

require $root . '/app/pages/not-found.php';
