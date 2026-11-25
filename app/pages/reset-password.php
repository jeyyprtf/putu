<?php
$page = 'reset-password';
$title = 'Reset Password';
require_once __DIR__ . '/../bootstrap.php';

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
session_boot();

$resetUrl = url('reset-password');
$invalidLink = 'Tautan reset tidak valid, sudah digunakan, atau kedaluwarsa. Minta tautan baru.';
$recovery = $_SESSION['password_recovery'] ?? null;
if ($recovery !== null && $recovery['expires_at'] <= time()) {
    unset($_SESSION['password_recovery']);
    $recovery = null;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && array_key_exists('token_hash', $_GET)) {
    $tokenHash = $_GET['token_hash'];
    unset($_SESSION['password_reset_complete']);
    if (!is_string($tokenHash) || preg_match('/\A[a-zA-Z0-9_-]{32,256}\z/', $tokenHash) !== 1) {
        unset($_SESSION['password_recovery']);
    } elseif ($recovery === null || !hash_equals($recovery['token_hash'], $tokenHash)) {
        $_SESSION['password_recovery'] = [
            'id' => bin2hex(random_bytes(16)),
            'token_hash' => $tokenHash,
            'expires_at' => time() + 900,
        ];
    }

    // Hapus token dari URL sebelum halaman dirender. Verifikasi hanya saat form dikirim.
    header('Location: ' . $resetUrl, true, 303);
    exit;
}

$complete = !empty($_SESSION['password_reset_complete']);
unset($_SESSION['password_reset_complete']);
$error = '';

if (is_post() && $recovery !== null) {
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $passwordLength = text_length($password);

    if (!csrf_verify() || !hash_equals($recovery['id'], post_str('recovery_id'))) {
        $error = 'Sesi form sudah berubah atau kedaluwarsa. Muat ulang halaman dan periksa tautan email kamu.';
    } elseif ($passwordLength === null || $passwordLength < 8 || strlen($password) > 72) {
        $error = 'Password harus terdiri dari minimal 8 karakter dan maksimal 72 byte.';
    } elseif ($password !== ($_POST['password_confirmation'] ?? null)) {
        $error = 'Konfirmasi password belum sama.';
    } else {
        try {
            if (!isset($recovery['access_token'])) {
                $result = supabase_auth_verify_password_reset($recovery['token_hash']);
                $accessToken = $result['data']['access_token'] ?? null;
                if ($result['status'] === 200 && is_string($accessToken) && $accessToken !== '') {
                    $recovery['access_token'] = $accessToken;
                    $recovery['expires_at'] = min(
                        $recovery['expires_at'],
                        time() + (int) ($result['data']['expires_in'] ?? 900)
                    );
                    $_SESSION['password_recovery'] = $recovery;
                } elseif (in_array($result['status'], [400, 401, 403, 422], true)) {
                    unset($_SESSION['password_recovery']);
                    $recovery = null;
                } else {
                    $error = 'Tautan belum bisa diperiksa. Silakan coba lagi.';
                    error_log('[password-reset][verify] HTTP ' . $result['status']);
                }
            }

            if (isset($recovery['access_token'])) {
                $result = supabase_auth_update_password($recovery['access_token'], $password);
                $code = $result['data']['error_code'] ?? $result['data']['code'] ?? '';
                if ($result['status'] === 200) {
                    unset($_SESSION['password_recovery']);
                    $_SESSION['password_reset_complete'] = true;
                    header('Location: ' . $resetUrl, true, 303);
                    exit;
                }

                if ($result['status'] === 401 || in_array($code, ['bad_jwt', 'session_expired', 'session_not_found'], true)) {
                    unset($_SESSION['password_recovery']);
                    $recovery = null;
                } else {
                    $error = match ($code) {
                        'weak_password' => 'Password belum memenuhi syarat keamanan. Pilih password yang lebih panjang dan unik.',
                        'same_password' => 'Gunakan password yang berbeda dari password sebelumnya.',
                        default => 'Password belum berhasil diperbarui. Silakan coba lagi.',
                    };
                    if ($result['status'] >= 500) {
                        error_log('[password-reset][update] HTTP ' . $result['status']);
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('[password-reset] Permintaan ke Supabase gagal.');
            $error = 'Layanan reset password belum dapat dihubungi. Silakan coba lagi.';
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
    <main class="mx-auto flex min-h-dvh w-full max-w-md flex-col justify-center px-6 py-12">
        <?php if ($complete): ?>
            <h1 class="text-3xl font-semibold tracking-tight">Password berhasil diperbarui</h1>
            <p role="status" class="mt-3 text-sm leading-7 text-stone-400">Gunakan password baru saat masuk ke PUTU.</p>
            <a href="<?= e(url('login')) ?>" class="button mt-6 w-full">Masuk ke PUTU</a>
        <?php elseif ($recovery === null): ?>
            <h1 class="text-3xl font-semibold tracking-tight">Reset password</h1>
            <p class="mt-3 text-sm leading-7 text-stone-400"><?= e($invalidLink) ?></p>
            <a href="<?= e(url('lupa-password')) ?>" class="button mt-6 w-full">Minta tautan baru</a>
        <?php else: ?>
            <h1 class="text-3xl font-semibold tracking-tight">Buat password baru</h1>
            <form action="<?= e($resetUrl) ?>" method="post" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="recovery_id" value="<?= e($recovery['id']) ?>">
                <div>
                    <label for="password" class="mb-2 block text-xs font-semibold">Password baru</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required class="field">
                </div>
                <div>
                    <label for="password-confirmation" class="mb-2 block text-xs font-semibold">Ulangi password baru</label>
                    <input id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" maxlength="72" required class="field">
                </div>
                <?php if ($error !== ''): ?>
                    <p role="alert" class="text-sm text-red-300"><?= e($error) ?></p>
                <?php endif; ?>
                <button type="submit" class="button w-full">Simpan password baru</button>
            </form>
            <a href="<?= e(url('lupa-password')) ?>" class="mt-5 text-center text-sm text-lime-300 hover:text-lime-200">Minta tautan baru</a>
        <?php endif; ?>
    </main>
</body>
</html>
