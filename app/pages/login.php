<?php
$page = 'login';
$title = 'Masuk';
require_once __DIR__ . '/../bootstrap.php';

require_guest();

$error = '';
$oldEmail = '';

if (is_post()) {
    $oldEmail = strtolower(post_str('email'));
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!csrf_verify()) {
        $error = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($oldEmail === '' || $password === '' || strlen($oldEmail) > 254 || strlen($password) > 72) {
        $error = 'Email atau password salah.';
    } else {
        try {
            $user = db_one(
                'SELECT id_user, password_hash FROM users WHERE email = :email',
                ['email' => $oldEmail]
            );

            // Hash tiruan: waktu respons tetap sama walau email tidak terdaftar,
            // jadi penyerang tidak bisa menebak email mana yang ada.
            $hash = $user['password_hash'] ?? '$2y$12$Gcju7U4ksTCdsqiYvEbEs./XcyNGxtZ.9bv8mdamFfCdp1F9VMnsO';
            $valid = password_verify($password, $hash) && $user !== null;

            if ($valid) {
                // Naikkan cost hash lama kalau standar PHP sudah berubah.
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    db_exec(
                        'UPDATE users SET password_hash = :hash WHERE id_user = :id',
                        ['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id_user']]
                    );
                }
                login_user((int) $user['id_user']);
                redirect(home_url_for(current_user()));
            }
            $error = 'Email atau password salah.';
        } catch (Throwable $e) {
            error_log('[login] ' . $e->getMessage());
            $error = 'Terjadi kesalahan di server. Coba lagi nanti.';
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
                        <?= csrf_field() ?>
                        <div>
                            <label for="email" class="mb-2 block text-xs font-semibold">
                                Email
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" placeholder="nama@email.com" value="<?= e($oldEmail) ?>" class="field">
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-xs font-semibold">
                                Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" autocomplete="current-password"  required maxlength="72" placeholder="Masukkan password kamu" class="field pr-14!">
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
                        <p id="auth-feedback" role="alert" aria-live="polite" class="text-xs leading-6 text-red-300" <?= $error === '' ? 'hidden' : '' ?>>
                            <?= e($error) ?>
                        </p>
                    </form>
                    <p class="mt-6 text-center text-xs leading-6 text-stone-400">
                        Belum punya akun?
                        <a href="<?= e(url('register')) ?>" class="font-semibold text-lime-300 hover:text-lime-200">
                            Daftar sekarang
                        </a>
                    </p>
                </main>
                <footer class="text-center text-[10px] leading-5 text-stone-500">
                    © <?= date('Y') ?> PUTU. Make room for what matters.
                </footer>
            </div>
        </div>
    </body>
</html>
