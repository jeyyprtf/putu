<?php
$page = 'lupa-password';
$title = 'Lupa Password';
require_once __DIR__ . '/../bootstrap.php';

header('Cache-Control: no-store');
session_boot();
$email = '';
$error = '';
$sent = !empty($_SESSION['password_reset_requested']);
unset($_SESSION['password_reset_requested']);

if (is_post()) {
    $email = strtolower(post_str('email'));
    if (!csrf_verify()) {
        $error = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan alamat email yang valid.';
    } else {
        try {
            $result = supabase_auth_send_password_reset($email);
            if ($result['status'] >= 500) {
                error_log('[password-reset][recover] HTTP ' . $result['status']);
            }
        } catch (Throwable $e) {
            error_log('[password-reset][recover] Permintaan ke Supabase gagal.');
        }

        // Hasil yang sama untuk semua alamat agar status akun tidak dapat ditebak.
        $_SESSION['password_reset_requested'] = true;
        header('Location: ' . url('lupa-password'), true, 303);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../components/head.php'; ?>
</head>
<body class="min-h-dvh bg-stone-950 font-sans text-stone-100 antialiased">
    <main class="mx-auto flex min-h-dvh w-full max-w-md flex-col justify-center px-6 py-12">
        <a href="<?= e(url('login')) ?>" class="mb-4 inline-flex min-h-11 items-center gap-2 text-sm text-stone-400 hover:text-lime-200">
            <?= icon('back', 'size-4') ?> Kembali ke login
        </a>
        <h1 class="text-3xl font-semibold tracking-tight">Lupa password?</h1>
        <?php if ($sent): ?>
            <p role="status" class="mt-4 text-sm leading-7 text-lime-300">
                Jika email itu terdaftar, kamu akan menerima tautan untuk membuat password baru.
                Periksa kotak masuk dan folder spam.
            </p>
        <?php else: ?>
            <p class="mt-3 text-sm leading-7 text-stone-400">Masukkan email yang kamu gunakan untuk masuk ke PUTU.</p>
            <form action="<?= e(url('lupa-password')) ?>" method="post" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label for="email" class="mb-2 block text-xs font-semibold">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" value="<?= e($email) ?>" maxlength="254" required class="field">
                </div>
                <?php if ($error !== ''): ?>
                    <p role="alert" class="text-sm text-red-300"><?= e($error) ?></p>
                <?php endif; ?>
                <button type="submit" class="button w-full">Kirim tautan reset</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
