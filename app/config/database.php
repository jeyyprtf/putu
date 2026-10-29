<?php
function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }

    $lokal = __DIR__ . '/database.local.php';
    $c = is_file($lokal) ? require $lokal : [];
    $ambil = fn(string $k) => getenv($k) ?: ($c[$k] ?? null);

    $pdo = new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
            $ambil('DB_HOST'), $ambil('DB_PORT') ?: '5432', $ambil('DB_NAME') ?: 'postgres'),
        $ambil('DB_USER'),
        $ambil('DB_PASS'),
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    return $pdo;
}