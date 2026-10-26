<?php
$page = 'register';
$title = 'Daftar';
require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
    </head>
    <body class="min-h-dvh bg-stone-950 font-sans text-stone-100 antialiased">
        <div class="grid min-h-dvh lg:grid-cols-2">
            <?php require __DIR__ . '/../components/auth-visual.php'; ?>
            <div class="relative flex min-h-dvh flex-col px-6 py-6 sm:px-10 lg:px-12">
                <div class="flex items-center justify-between gap-3">
                    <a href="<?= e(url()) ?>" class="flex min-h-11 items-center gap-2 text-xs text-stone-400 hover:text-lime-200">
                        <?= icon('back', 'size-4') ?>Kembali ke beranda
                    </a>
                    <a href="<?= e(url()) ?>" class="text-2xl font-extrabold tracking-tighter lg:hidden">
                        putu<span class="text-lime-500">.</span>
                    </a>
                </div>
                <main class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-10 sm:py-14">
                    <p class="eyebrow">
                        Putu / Academic workspace
                    </p>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tighter sm:text-4xl">
                        Mulai perjalananmu.
                    </h1>
                    <p class="mt-3 text-sm leading-7 text-stone-400">
                        Satu ruang untuk hari kuliah yang lebih teratur. Mulai dari sini.
                    </p>
                    <form action="<?= e(url('register')) ?>" method="POST" data-auth-form class="mt-8 space-y-5">
                        <div>
                            <label for="nama" class="mb-2 block text-xs font-semibold">
                                Nama lengkap
                            </label>
                            <input id="nama" name="nama" type="text" autocomplete="name" required maxlength="100" placeholder="Siapa nama kamu?" class="field">
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-xs font-semibold">
                                Email
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" placeholder="nama@email.com" class="field">
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-xs font-semibold">
                                Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" aria-describedby="password-help" required placeholder="Minimal 8 karakter" class="field pr-14!">
                                <button type="button" data-password-toggle aria-label="Tampilkan password" aria-controls="password" aria-pressed="false" class="absolute top-0 right-1 flex size-12 items-center justify-center rounded-xl text-stone-400 hover:text-lime-200">
                                    <?= icon('eye', 'size-4') ?>
                                </button>
                            </div>
                            <p id="password-help" class="mt-2 text-xs text-stone-400">
                                Gunakan minimal 8 karakter.
                            </p>
                        </div>
                        <button type="submit" class="button w-full">
                            Buat akun PUTU<?= icon('arrow', 'size-4') ?>
                        </button>
                        <p id="auth-feedback" role="status" aria-live="polite" class="text-xs leading-6 text-amber-200" <?= ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' ? 'hidden' : '' ?>>
                            Autentikasi belum terhubung. Kamu bisa menjelajahi pratinjau di bawah ini; data form tidak disimpan.
                        </p>
                    </form>
                    <p class="mt-6 text-center text-xs leading-6 text-stone-400">
                        Sudah punya akun?
                        <a href="<?= e(url('login')) ?>" class="font-semibold text-lime-300 hover:text-lime-200">
                            Masuk sekarang
                        </a>
                    </p>
                    <div class="mt-8 border-t border-stone-800 pt-6">
                        <a href="<?= e(url('dashboard')) ?>" class="flex min-h-11 items-center justify-center gap-2 rounded-2xl border border-stone-800 px-4 text-xs text-stone-300 hover:border-stone-600 hover:text-lime-200">
                            Jelajahi pratinjau mahasiswa <?= icon('arrow', 'size-4') ?>
                        </a>
                        <p class="mt-3 text-center text-[10px] leading-5 text-stone-500">
                            Pratinjau frontend · Belum membuat atau masuk ke akun.
                        </p>
                    </div>
                </main>
                <footer class="text-center text-[10px] leading-5 text-stone-500">
                    © <?= date('Y') ?> PUTU. Make room for what matters.
                </footer>
            </div>
        </div>
    </body>
</html>
