<?php
/**
 * Halaman instruksi dan kirim ulang verifikasi email.
 *
 * - Tidak membutuhkan login.
 * - Menggunakan Supabase Auth.
 * - Cooldown 60 detik per email dalam session PHP.
 * - Tidak mengubah database PUTU.
 */

$page = 'cek-email';
$title = 'Periksa Email';

require_once __DIR__ . '/../bootstrap.php';

session_boot();

$error = '';
$success = '';
$emailPendaftaran = $_SESSION['email_verifikasi_pendaftaran'] ?? '';
$email = $emailPendaftaran;
$cooldown = 60;

// Simpan waktu permintaan terakhir per email.
$_SESSION['email_resend_at'] ??= [];

if (!is_array($_SESSION['email_resend_at'])) {
    $_SESSION['email_resend_at'] = [];
}

// --------------------------------------------------
// Proses kirim ulang email verifikasi.
// --------------------------------------------------
if (is_post()) {
    $email = strtolower(post_str('email'));

    if (!csrf_verify()) {
        $error = 'Sesi keamanan tidak valid. '
            . 'Muat ulang halaman lalu coba lagi.';

    } elseif (
        strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'Masukkan alamat email yang valid.';

    } else {
        $key = hash('sha256', $email);
        $now = time();

        $lastSent = (int) (
            $_SESSION['email_resend_at'][$key] ?? 0
        );

        $remaining = max(
            0,
            $cooldown - ($now - $lastSent)
        );

        if ($remaining > 0) {
            $error = "Tunggu {$remaining} detik "
                . 'sebelum mengirim ulang.';

        } else {
            try {
                $callback =
                    'https://putu.juan.web.id/app/pages/verifikasi-email.php';

                $endpoint = 'resend?redirect_to='
                    . rawurlencode($callback);

                // Menggunakan helper yang sudah tersedia.
                $result = supabase_auth_request(
                    $endpoint,
                    [
                        'type' => 'signup',
                        'email' => $email,
                    ]
                );

                $status = (int) (
                    $result['status'] ?? 0
                );

                if ($status >= 200 && $status < 300) {

                    $_SESSION['email_resend_at'][$key] = time();

                    $success = 'Permintaan berhasil diproses. '
                        . 'Jika email terdaftar dan belum '
                        . 'diverifikasi, pesan konfirmasi '
                        . 'akan dikirim. Periksa Inbox dan Spam.';

                } elseif ($status === 429) {

                    $error = 'Terlalu banyak permintaan. '
                        . 'Tunggu beberapa saat sebelum '
                        . 'mencoba kembali.';

                } else {

                    error_log(
                        '[cek-email][resend] HTTP ' . $status
                    );

                    $error = 'Permintaan belum berhasil diproses. '
                        . 'Periksa alamat email atau coba lagi nanti.';
                }

            } catch (Throwable $e) {

                error_log(
                    '[cek-email][resend] '
                    . $e->getMessage()
                );

                $error = 'Layanan verifikasi sedang '
                    . 'mengalami gangguan. Coba lagi nanti.';
            }
        }
    }
}

// Waktu cooldown yang tersisa untuk email di form.
$remaining = 0;

$emailValid = strlen($email) <= 254 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
$tampilkanGmail = $emailValid && str_ends_with(strtolower($email), '@gmail.com');

if ($emailValid) {
    $key = hash('sha256', $email);

    $lastSent = (int) (
        $_SESSION['email_resend_at'][$key] ?? 0
    );

    $remaining = max(
        0,
        $cooldown - (time() - $lastSent)
    );
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../components/head.php'; ?>
    <style>
        #email:is(:autofill, :-webkit-autofill) {
            box-shadow: inset 0 0 0 100px var(--color-stone-900, #1c1917);
            -webkit-text-fill-color: var(--color-stone-100, #f5f5f4);
        }
    </style>
</head>

<body class="flex min-h-dvh flex-col bg-stone-950 font-sans text-stone-100 antialiased">
    <?php $mailIcon = '<svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>'; ?>
    <main class="flex flex-1 items-center justify-center px-5 py-6 sm:px-6">
        <div class="w-full max-w-md">
            <a
                href="<?= e(url('login')) ?>"
                class="mb-6 inline-flex min-h-11 items-center gap-2 text-sm text-stone-400 transition hover:text-stone-100"
            >
                <?= icon('back', 'size-5') ?>
                Kembali ke masuk
            </a>

            <p class="text-xs font-bold uppercase tracking-[.18em] text-lime-400">
                PUTU / Academic Workspace
            </p>

            <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                Cek email kamu.
            </h1>

            <?php if ($emailValid): ?>
                <p class="mt-4 text-sm leading-6 text-stone-400 sm:text-base">
                    Periksa kotak masuk email berikut untuk menemukan tautan verifikasi akun PUTU:
                </p>

                <p class="mt-3 break-all text-base font-semibold text-stone-100">
                    <?= e($email) ?>
                </p>
            <?php else: ?>
                <p class="mt-4 text-sm leading-6 text-stone-400 sm:text-base">
                    Alamat email belum tersedia. Kembali ke halaman masuk untuk meminta ulang email verifikasi.
                </p>
            <?php endif; ?>

            <form method="POST" action="<?= e(url('cek-email')) ?>" id="resend-form" class="mt-6">
                <?= csrf_field() ?>

                <input type="hidden" id="email" name="email" value="<?= e($email) ?>">

                <div class="grid gap-3">
                    <div id="gmail-shortcut"<?= $tampilkanGmail ? '' : ' hidden' ?>>
                        <a
                            href="https://mail.google.com/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex min-h-12 items-center justify-center gap-3 rounded-full bg-[#84cc00] px-5 text-sm font-semibold text-stone-950 transition hover:bg-lime-400 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lime-400 sm:min-h-14 sm:text-base"
                        >
                            <?= icon('gmail', 'size-5 shrink-0') ?>
                            Buka Gmail
                        </a>
                    </div>

                    <button
                        type="submit"
                        id="resend-button"
                        data-remaining="<?= (int) $remaining ?>"
                        data-email-valid="<?= $emailValid ? '1' : '0' ?>"
                        class="min-h-12 rounded-full border border-stone-800 px-5 text-sm font-semibold transition hover:border-stone-600 hover:bg-stone-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lime-400 disabled:cursor-not-allowed disabled:opacity-50 sm:min-h-14 sm:text-base"
                        <?= !$emailValid ? 'disabled' : '' ?>
                    >
                        Kirim ulang email verifikasi
                    </button>
                </div>

                <p id="cooldown-message" class="sr-only" aria-live="polite">
                    Kamu dapat meminta email ulang dengan jeda minimal 60 detik.
                </p>

                <?php if ($success !== ''): ?>
                    <p role="status" class="mt-4 rounded-xl border border-lime-500/30 bg-lime-500/10 px-4 py-3 text-sm leading-6 text-lime-300">
                        <?= e($success) ?>
                    </p>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <p role="alert" class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm leading-6 text-red-300">
                        <?= e($error) ?>
                    </p>
                <?php endif; ?>
            </form>

            <p class="mt-6 flex items-start gap-3 text-sm leading-6 text-stone-400">
                <?= icon('info', 'mt-0.5 size-5 shrink-0 text-stone-300') ?>
                <span>
                    Belum menemukan emailnya? Periksa juga folder <strong class="font-semibold text-stone-300">Spam</strong> di kotak masukmu.
                </span>
            </p>
        </div>
    </main>
    <footer class="px-5 pb-3 text-center text-xs leading-5 text-stone-400 [@media(min-height:900px)]:pb-7">
        © <?= date('Y') ?> PUTU. Make room for what matters.
    </footer>

    <script>
    (() => {
        const form = document.getElementById('resend-form');
        const button = document.getElementById('resend-button');
        const message = document.getElementById('cooldown-message');
        const emailValid = button.dataset.emailValid === '1';

        let remaining = Number(button.dataset.remaining || 0);

        function updateButton() {
            if (!emailValid) {
                button.disabled = true;
                button.textContent = 'Kirim ulang email verifikasi';
                return;
            }

            if (remaining > 0) {
                button.disabled = true;
                button.textContent = `Kirim ulang dalam ${remaining} detik`;
                message.textContent = 'Silakan tunggu sebelum meminta email ulang.';
            } else {
                button.disabled = false;
                button.textContent = 'Kirim ulang email verifikasi';
                message.textContent = 'Kamu dapat meminta email ulang dengan jeda minimal 60 detik.';
            }
        }

        updateButton();

        if (remaining > 0) {
            const timer = setInterval(() => {
                remaining = Math.max(0, remaining - 1);
                updateButton();

                if (remaining === 0) {
                    clearInterval(timer);
                }
            }, 1000);
        }

        form.addEventListener('submit', (event) => {
            if (!emailValid || remaining > 0) {
                event.preventDefault();
                return;
            }

            button.disabled = true;
            button.textContent = 'Memproses permintaan...';
        });
    })();
    </script>

</body>
</html>
