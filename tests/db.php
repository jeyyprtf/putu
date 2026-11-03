<?php
/**
 * Script cek koneksi database.
 * Jalankan dari root project:  php tests/db.php
 */

require __DIR__ . '/../app/config/database.php';

try {
    $versi = db()->query('SELECT version()')->fetchColumn();

    $jumlahTabel = db()->query(
        "SELECT count(*) FROM information_schema.tables
         WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
    )->fetchColumn();

    echo "OK - Terhubung ke database\n";
    echo "Server : " . substr((string) $versi, 0, 60) . "...\n";
    echo "Tabel  : {$jumlahTabel} tabel di schema public\n";
} catch (Throwable $e) {
    fwrite(STDERR, "GAGAL - " . $e->getMessage() . "\n");
    exit(1);
}
