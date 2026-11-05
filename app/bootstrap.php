<?php

// Persiapan bersama sebelum halaman menampilkan HTML.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/demo.php';

// Database dimuat kalau sudah dikonfigurasi. Kalau belum, halaman tetap jalan
// (memakai data demo), jadi tidak error saat kredensial belum diisi.
if (is_file(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/kelas.php';
require_once __DIR__ . '/pengumuman.php';
require_once __DIR__ . '/tugas.php';

// Profil di navbar/sidebar/pengaturan memakai user yang login (kalau ada).
// Tanpa cookie session, tidak ada query DB sama sekali.
try {
    $currentUser = current_user();
} catch (Throwable $e) {
    // DB bermasalah: jangan matikan halaman publik, anggap belum login.
    error_log('[auth] ' . $e->getMessage());
    $currentUser = null;
}
if ($currentUser !== null) {
    $profile = [
        'name'    => $currentUser['nama'],
        'email'   => $currentUser['email'],
        'initial' => first_letter($currentUser['nama']),
        'role'    => role_label($currentUser),
        'kelas'   => $currentUser['nama_kelas'] ?? 'Belum bergabung kelas',
    ];
}

