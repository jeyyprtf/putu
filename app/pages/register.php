<?php
$page = 'register';
$title = 'Daftar';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../auth.php';

auth_session_start();

// Sudah login: tidak perlu mendaftar lagi.
if (auth_user_id() !== null) {
    header('Location: ' . url('dashboard'));
    exit;
}

$error = '';
$old = ['nama' => '', 'email' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // Normalisasi input. Password sengaja tidak di-trim.
    $nama = preg_replace('/\s+/u', ' ', trim((string) ($_POST['nama'] ?? ''))) ?? '';
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $old = ['nama' => $nama, 'email' => $email];

    if (!csrf_verify($_POST['csrf'] ?? null)) {
        $error = 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($nama === '' || mb_strlen($nama) > 100) {
        $error = 'Nama lengkap wajib diisi (maksimal 100 karakter).';
    } elseif ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (mb_strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif (strlen($password) > 72) {
        // bcrypt hanya memakai 72 byte pertama, jadi lebih dari itu ditolak.
        $error = 'Password terlalu panjang (maksimal 72 karakter).';
    } else {
        try {
            $row = db_one(
                'INSERT INTO users (nama, email, password_hash) VALUES (?, ?, ?) RETURNING id_user',
                [$nama, $email, password_hash($password, PASSWORD_DEFAULT)]
            );
            auth_login((int) $row['id_user']);
            header('Location: ' . url('gabung'));
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23505') {
                // unique_violation pada uq_users_email
                $error = 'Email ini sudah terdaftar. Coba masuk atau pakai email lain.';
            } else {
                error_log('[register] ' . $ex->getMessage());
                $error = 'Terjadi kesalahan saat membuat akun. Coba lagi sebentar lagi.';
            }
        } catch (RuntimeException $ex) {
            error_log('[register] ' . $ex->getMessage());
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
                        Mulai perjalananmu.
                    </h1>
                    <p class="mt-3 text-sm leading-7 text-stone-400">
                        Satu ruang untuk hari kuliah yang lebih teratur. Mulai dari sini.
                    </p>
                    <form action="<?= e(url('register')) ?>" method="POST" class="mt-8 space-y-5">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <div>
                            <label for="nama" class="mb-2 block text-xs font-semibold">
                                Nama lengkap
                            </label>
                            <input id="nama" name="nama" type="text" autocomplete="name" required maxlength="100" value="<?= e($old['nama']) ?>" placeholder="Siapa nama kamu?" class="field">
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-xs font-semibold">
                                Email
                            </label>
                            <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= e($old['email']) ?>" placeholder="nama@email.com" class="field">
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
                        <p id="auth-feedback" role="alert" aria-live="polite" class="text-xs leading-6 text-red-300" <?= $error === '' ? 'hidden' : '' ?>><?= e($error) ?></p>
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