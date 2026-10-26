<?php
// URL lama tetap didukung. File di app/pages/ juga bisa dibuka langsung.
$pages = ['', 'home', 'dashboard', 'tugas', 'jadwal', 'pengumuman', 'login', 'register'];
$page = $_GET['page'] ?? '';

if (!is_string($page) || !in_array($page, $pages, true)) {
    http_response_code(404);
    require __DIR__ . '/app/pages/not-found.php';
    exit;
}

if ($page === '') {
    $page = 'home';
}
require __DIR__ . '/app/pages/' . $page . '.php';
