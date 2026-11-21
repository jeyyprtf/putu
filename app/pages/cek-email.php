
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

// Session hanya digunakan untuk CSRF dan cooldown.
session_boot();

$error = '';
$success = '';
$email = '';
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
    $email = strtolower(trim(
        (string) ($_POST['email'] ?? '')
    ));

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

if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
</head>

<body class="min-h-dvh bg-stone-950 font-sans text-stone-100 antialiased">

    <main class="flex min-h-dvh items-center justify-center px-6 py-12">

        <div class="w-full max-w-md text-center">

            <!-- Header -->
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

            <!-- Form resend -->
            <section class="mt-8 rounded-2xl border border-stone-800 bg-stone-900/70 p-5 text-left sm:p-6">

                <h2 class="text-base font-semibold text-stone-100">
                    Belum menerima email?
                </h2>

                <p class="mt-2 text-xs leading-6 text-stone-400">
                    Masukkan kembali alamat email yang digunakan
                    saat mendaftar untuk meminta link verifikasi baru.
                </p>

                <!-- Notifikasi berhasil -->
                <?php if ($success !== ''): ?>
                    <div
                        role="status"
                        class="mt-5 rounded-xl border border-lime-500/30 bg-lime-500/10 p-4 text-sm leading-6 text-lime-300"
                    >
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <!-- Notifikasi error -->
                <?php if ($error !== ''): ?>
                    <div
                        role="alert"
                        class="mt-5 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm leading-6 text-red-300"
                    >
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form
                    method="POST"
                    action="<?= e(url('cek-email')) ?>"
                    class="mt-5 space-y-4"
                    id="resend-form"
                >
                    <?= csrf_field() ?>

                    <div>
                        <label
                            for="email"
                            class="mb-2 block text-xs font-semibold text-stone-200"
                        >
                            Alamat email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            maxlength="254"
                            autocomplete="email"
                            placeholder="nama@email.com"
                            value="<?= e($email) ?>"
                            class="w-full min-h-12 rounded-xl border border-stone-700 bg-stone-950 px-4 py-3 text-sm text-stone-100 outline-none transition focus:border-lime-400"
                        >
                    </div>

                    <button
                        type="submit"
                        id="resend-button"
                        data-remaining="<?= (int) $remaining ?>"
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-lime-500 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-lime-400 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Kirim ulang email verifikasi
                    </button>

                    <p
                        id="cooldown-message"
                        class="text-center text-xs leading-5 text-stone-500"
                        aria-live="polite"
                    >
                        Kamu dapat meminta email ulang dengan jeda minimal 60 detik.
                    </p>

                </form>

            </section>

            <!-- Kembali login -->
            <a
                href="<?= e(url('login')) ?>"
                class="mt-7 inline-flex min-h-11 items-center justify-center rounded-xl border border-stone-700 px-6 text-sm font-semibold text-stone-200 transition hover:border-stone-500 hover:bg-stone-900"
            >
                Kembali ke Login
            </a>

            <p class="mt-6 text-xs leading-6 text-stone-500">
                Pastikan alamat email benar sebelum meminta verifikasi ulang.
            </p>

        </div>

    </main>

    <script>
    (() => {
        const form = document.getElementById('resend-form');
        const button = document.getElementById('resend-button');
        const message = document.getElementById('cooldown-message');
        const emailInput = document.getElementById('email');

        let remaining = Number(button.dataset.remaining || 0);

        function updateButton() {
            if (remaining > 0) {
                button.disabled = true;
                button.textContent = `Kirim ulang dalam ${remaining} detik`;

                message.textContent =
                    'Silakan tunggu sebelum meminta email ulang.';

            } else {
                button.disabled = false;
                button.textContent = 'Kirim ulang email verifikasi';

                message.textContent =
                    'Kamu dapat meminta email ulang dengan jeda minimal 60 detik.';
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

        // Email diubah: server tetap memvalidasi cooldown
        // berdasarkan alamat email yang dikirim.
        emailInput.addEventListener('input', () => {
            if (remaining > 0) {
                remaining = 0;
                updateButton();
            }
        });

        form.addEventListener('submit', () => {
            if (!form.checkValidity()) {
                return;
            }

            button.disabled = true;
            button.textContent = 'Memproses permintaan...';
        });
    })();
    </script>

</body>
</html>
