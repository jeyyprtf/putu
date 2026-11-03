<?php

// Persiapan bersama sebelum halaman menampilkan HTML.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
auth_session_start();
require_once __DIR__ . '/demo.php';

// Database dimuat kalau sudah dikonfigurasi. Kalau belum, halaman tetap jalan
// (memakai data demo), jadi tidak error saat kredensial belum diisi.
if (is_file(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
}
