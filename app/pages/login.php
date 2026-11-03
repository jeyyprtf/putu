<?php
$page = 'login';
$title = 'Masuk';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth.php';

auth_session_start();

if (auth_user_id() !== null) {
    header('Location: ' . url('dashboard'));
    exit;
}

$error = '';
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_verify($_POST['csrf'] ?? null)) {
        $error = 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif ($password === '' || strlen($password) > 72) {
        $error = 'Email atau password salah.';
    } else {
        try {
            $user = db_one(
                'SELECT id_user, password_hash FROM users WHERE email = ?',
                [$email]
            );

            if ($user !== null && password_verify($password, $user['password_hash'])) {
                auth_login((int) $user['id_user']);
                header('Location: ' . url('gabung'));
                exit;
            }

            $error = 'Email atau password salah.';
        } catch (PDOException $ex) {
            error_log('[login] ' . $ex->getMessage());
            $error = 'Terjadi kesalahan saat masuk. Coba lagi sebentar lagi.';
        } catch (RuntimeException $ex) {
            error_log('[login] ' . $ex->getMessage());
            $error = 'Layanan database belum tersedia. Hubungi pengelola aplikasi.';
        }
    }
}
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
                        Senang kamu kembali.
                    </h1>
                    <p class="mt-3 text-sm leading-7 text-stone-400">
                        Masuk dan lihat apa yang perlu kamu fokuskan hari ini.
                    </p>
                    <form action="<?= e(url('login')) ?>" method="POST" class="mt-8 space-y-5">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <div>
                            <label for="email" class="mb-2 block text-xs font-semibold">
                                Email
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= e($email) ?>" placeholder="nama@email.com" class="field">
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-xs font-semibold">
                                Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Masukkan password kamu" class="field pr-14!">
                                <button type="button" data-password-toggle aria-label="Tampilkan password" aria-controls="password" aria-pressed="false" class="absolute top-0 right-1 flex size-12 items-center justify-center rounded-xl text-stone-400 hover:text-lime-200">
                                    <?= icon('eye', 'size-4') ?>
                                </button>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="button" data-forgot-password class="min-h-11 text-xs text-lime-300 hover:text-lime-200">
                                Lupa password?
                            </button>
                        </div>
                        <button type="submit" class="button w-full">
                            Masuk ke PUTU<?= icon('arrow', 'size-4') ?>
                        </button>
                        <p id="auth-feedback" role="alert" aria-live="polite" class="text-xs leading-6 text-red-300" <?= $error === '' ? 'hidden' : '' ?>><?= e($error) ?></p>
                    </form>
                    <p class="mt-6 text-center text-xs leading-6 text-stone-400">
                        Belum punya akun?
                        <a href="<?= e(url('register')) ?>" class="font-semibold text-lime-300 hover:text-lime-200">
                            Daftar sekarang
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
