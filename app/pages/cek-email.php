
<?php
/**
 * Halaman instruksi verifikasi email setelah registrasi.
 */
$page = 'cek-email';
$title = 'Periksa Email';

require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../components/head.php'; ?>
</head>
<body class="min-h-dvh bg-stone-950 font-sans text-stone-100 antialiased">

    <main class="flex min-h-dvh items-center justify-center px-6 py-12">
        <div class="w-full max-w-md text-center">

            <div class="mb-6 text-5xl">✉️</div>

            <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-lime-400">
                PUTU / Verifikasi Email
            </p>

            <h1 class="text-3xl font-semibold tracking-tight">
                Periksa email kamu<span class="text-lime-400">.</span>
            </h1>

            <p class="mt-5 text-sm leading-7 text-stone-400">
                Jika pendaftaran berhasil, kami telah meminta Supabase
                mengirimkan email konfirmasi ke alamat yang kamu daftarkan.
            </p>

            <p class="mt-3 text-sm leading-7 text-stone-400">
                Buka Inbox atau folder Spam, lalu klik tautan
                verifikasi yang dikirimkan.
            </p>

            <a
                href="<?= e(url('login')) ?>"
                class="mt-8 inline-flex min-h-11 items-center justify-center rounded-xl bg-lime-500 px-6 text-sm font-semibold text-stone-950 hover:bg-lime-400"
            >
                Kembali ke Login
            </a>

        </div>
    </main>

</body>
</html>
