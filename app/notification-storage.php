<?php

function notification_state_dir(): string
{
    return env('PUTU_NOTIFICATION_DIR') ?? getenv('HOME') . '/.config/putu/notifications';
}

function notification_private_dir(): string
{
    $directory = notification_state_dir();
    $root = realpath(dirname(__DIR__));
    // Cek sebelum membuat direktori, termasuk saat document root lama masih aktif.
    $path = realpath($directory);
    if ($path === false) {
        if (!str_starts_with($directory, '/') || in_array('..', explode('/', $directory), true)) {
            throw new RuntimeException('Gunakan path absolut untuk direktori notifikasi.');
        }
        $parent = dirname($directory);
        while (!is_dir($parent) && dirname($parent) !== $parent) {
            $parent = dirname($parent);
        }
        $parent = realpath($parent);
        if ($parent === false || $parent === $root || str_starts_with($parent, $root . '/')) {
            throw new RuntimeException('Siapkan direktori notifikasi privat di luar repositori.');
        }
        if (!@mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Direktori notifikasi tidak bisa dibuat.');
        }
        $path = realpath($directory);
    }
    if ($path === false || $path === $root || str_starts_with($path, $root . '/')
        || !is_dir($path) || (fileperms($path) & 0777) !== 0700 || !is_writable($path)) {
        throw new RuntimeException('Direktori notifikasi harus privat (0700) dan bisa ditulis oleh PHP.');
    }
    return $path;
}
