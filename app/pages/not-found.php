<?php
$page = 'not-found';
$title = 'Halaman tidak ditemukan';
require_once __DIR__ . '/../bootstrap.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
    </head>
    <body class="flex min-h-dvh items-center justify-center bg-stone-950 px-6 font-sans text-stone-100 antialiased">
        <main class="max-w-md text-center">
            <p class="eyebrow">
                404 / Salah belok sedikit
            </p>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight">
                Halaman ini belum ada.
            </h1>
            <p class="mt-4 text-sm leading-7 text-stone-400">
                Kembali ke beranda dan temukan ruang yang kamu cari.
            </p>
            <a href="<?= e(url()) ?>" class="button mt-7">
                Kembali ke beranda
            </a>
        </main>
    </body>
</html>
