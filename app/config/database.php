<?php
/**
 * Koneksi database PostgreSQL (Supabase) via PDO.
 *
 * Cara pakai:
 *   require_once __DIR__ . '/../config/database.php';
 *   $rows = db()->query('SELECT * FROM tugas')->fetchAll();
 *
 * Kredensial dibaca dari:
 *   1. Environment variable (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS)
 *   2. File app/config/database.local.php (tidak di-commit, lihat .env.example)
 *   3. File .env di root project (kalau ada)
 */

/**
 * Ambil satu nilai konfigurasi dari env var / file lokal.
 */
function env(string $key, ?string $default = null): ?string
{
    static $lokal = null;

    if ($lokal === null) {
        $lokal = [];

        // Prioritas 1: file database.local.php (plain PHP, aman dari web server)
        $fileLokal = __DIR__ . '/database.local.php';
        if (is_file($fileLokal)) {
            $isi = require $fileLokal;
            if (is_array($isi)) {
                $lokal = $isi;
            }
        }

        // Prioritas 2: file .env sederhana di root project (fallback)
        $fileEnv = dirname(__DIR__, 2) . '/.env';
        if ($lokal === [] && is_file($fileEnv)) {
            foreach (file($fileEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $baris) {
                $baris = trim($baris);
                if ($baris === '' || str_starts_with($baris, '#') || !str_contains($baris, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $baris, 2);
                $lokal[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
            }
        }
    }

    $nilai = getenv($key);
    if ($nilai === false || $nilai === '') {
        $nilai = $lokal[$key] ?? null;
    }

    return ($nilai === null || $nilai === '') ? $default : (string) $nilai;
}

/**
 * Koneksi PDO tunggal (singleton) ke PostgreSQL.
 * Satu request hanya membuka satu koneksi.
 *
 * PENTING: kode ini memakai prepared statement asli (ATTR_EMULATE_PREPARES = false)
 * dan SET statement_timeout. Keduanya butuh state sesi yang utuh,
 * jadi gunakan SESSION POOLER (port 5432), bukan transaction pooler (6543).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST');
    $user = env('DB_USER');
    $pass = env('DB_PASS');

    if ($host === null || $user === null) {
        throw new RuntimeException(
            'Konfigurasi database belum lengkap. Buat app/config/database.local.php '
            . '(contoh ada di .env.example).'
        );
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
        $host,
        env('DB_PORT', '5432'),
        env('DB_NAME', 'postgres')
    );

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ]);
    } catch (PDOException $e) {
        error_log('[db] Gagal konek: ' . $e->getMessage());
        throw new RuntimeException('Tidak bisa terhubung ke database.', 0, $e);
    }

    // Batasi query yang menggantung. Ini state sesi: hanya efektif pada
    // Session pooler (5432) / direct connection, tidak pada Transaction pooler.
    $pdo->exec("SET statement_timeout = '10s'");

    return $pdo;
}

/**
 * Shortcut query SELECT: kembalikan semua baris.
 */
function db_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Shortcut query SELECT: kembalikan satu baris atau null.
 */
function db_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/**
 * Shortcut INSERT/UPDATE/DELETE: kembalikan jumlah baris terpengaruh.
 */
function db_exec(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}
