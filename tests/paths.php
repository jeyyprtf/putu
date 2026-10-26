<?php
// Jalankan: php tests/paths.php
require __DIR__ . '/../app/helpers.php';

foreach (['/index.php', '/app/pages/dashboard.php', '/putu/index.php', '/putu/app/pages/dashboard.php'] as $script) {
    $_SERVER['SCRIPT_NAME'] = $script;
    $prefix = str_starts_with($script, '/putu/') ? '/putu' : '';
    if (url('tugas', ['status' => 'done']) !== $prefix . '/app/pages/tugas.php?status=done') {
        throw new RuntimeException('URL halaman salah: ' . $script);
    }
    if (asset('resources/css/app.css') !== $prefix . '/resources/css/app.css') {
        throw new RuntimeException('URL aset salah: ' . $script);
    }
    if (url() !== $prefix . '/index.php') {
        throw new RuntimeException('URL beranda salah: ' . $script);
    }
}
echo "PASS: URL halaman/aset dari root, pages, dan subfolder.\n";
