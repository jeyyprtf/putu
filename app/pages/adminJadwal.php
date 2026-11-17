<?php
// Rute lama: arahkan pengguna ke halaman admin yang baru.
// POST lama tetap diproses jika ada form dari halaman yang tersimpan di browser.
require_once __DIR__ . '/../helpers.php';
if (!is_post()) {
    redirect(url('admin/jadwal'));
}
require __DIR__ . '/admin/jadwal.php';
