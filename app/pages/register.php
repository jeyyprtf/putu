<?php
$page = 'register';
$title = 'Daftar';
require_once __DIR__ . '/../bootstrap.php';

require_guest();

$error = '';
$old = ['nama' => '', 'email' => ''];

if (is_post()) {
    $old['nama'] = post_str('nama');
    $old['email'] = strtolower(post_str('email'));
    // Password sengaja tidak di-trim: spasi di tepi boleh jadi bagian password.
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    $namaLen = text_length($old['nama']);

    if (!csrf_verify()) {
        $error = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($namaLen === null || $namaLen < 2 || $namaLen > 100) {
        $error = 'Nama lengkap harus 2–100 karakter.';
    } elseif (strlen($old['email']) > 254 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif (strlen($password) > 72) {
        // Batas bcrypt: lebih dari 72 byte akan terpotong diam-diam.
        $error = 'Password maksimal 72 karakter.';
    } else {
        try {
            $user = db_one(
                "INSERT INTO users (nama, email, password_hash, role)
                 VALUES (:nama, :email, :hash, 'mahasiswa') RETURNING id_user",
                [
                    'nama'  => $old['nama'],
                    'email' => $old['email'],
                    'hash'  => password_hash($password, PASSWORD_DEFAULT),
                ]
            );
            login_user((int) $user['id_user']);
            redirect(url('gabung'));
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') { // unique_violation pada email
                $error = 'Email sudah terdaftar. Silakan masuk.';
            } else {
                error_log('[register] ' . $e->getMessage());
                $error = 'Terjadi kesalahan di server. Coba lagi nanti.';
            }
        } catch (Throwable $e) {
            error_log('[register] ' . $e->getMessage());
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
                        Mulai perjalananmu.
                    </h1>
                    <p class="mt-3 text-sm leading-7 text-stone-400">
                        Satu ruang untuk hari kuliah yang lebih teratur. Mulai dari sini.
                    </p>
                    <form action="<?= e(url('register')) ?>" method="POST" class="mt-8 space-y-5">
                        <?= csrf_field() ?>
                        <div>
                            <label for="nama" class="mb-2 block text-xs font-semibold">
                                Nama lengkap
                            </label>
                            <input id="nama" name="nama" type="text" autocomplete="name" required maxlength="100" placeholder="Siapa nama kamu?" value="<?= e($old['nama']) ?>" class="field">
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-xs font-semibold">
                                Email
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" placeholder="nama@email.com" value="<?= e($old['email']) ?>" class="field">
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-xs font-semibold">
                                Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" aria-describedby="password-help" required placeholder="Minimal 8 karakter" class="field pr-14!">
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
                        <p id="auth-feedback" role="alert" aria-live="polite" class="text-xs leading-6 text-red-300" <?= $error === '' ? 'hidden' : '' ?>>
                            <?= e($error) ?>
                        </p>
                    </form>
                    <p class="mt-6 text-center text-xs leading-6 text-stone-400">
                        Sudah punya akun?
                        <a href="<?= e(url('login')) ?>" class="font-semibold text-lime-300 hover:text-lime-200">
                            Masuk sekarang
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
