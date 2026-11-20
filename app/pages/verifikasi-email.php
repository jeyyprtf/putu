
<?php
/**
 * Halaman callback verifikasi email Supabase Auth.
 *
 * Alur:
 * 1. Pengguna mengklik link konfirmasi bawaan Supabase.
 * 2. Supabase memverifikasi email.
 * 3. Supabase mengarahkan pengguna ke halaman ini.
 * 4. JavaScript memvalidasi sesi ke Supabase Auth.
 *
 * Tidak membuat session PHP dan tidak mengubah public.users.
 */

$page = 'verifikasi-email';
$title = 'Verifikasi Email';

require_once __DIR__ . '/../bootstrap.php';

$supabaseUrl = rtrim(
    (string) env('SUPABASE_URL', ''),
    '/'
);

$supabaseKey = (string) env(
    'SUPABASE_PUBLISHABLE_KEY',
    ''
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../components/head.php'; ?>
    <meta name="referrer" content="no-referrer">
</head>

<body class="min-h-dvh bg-stone-950 font-sans text-stone-100 antialiased">

    <main class="flex min-h-dvh items-center justify-center px-6 py-12">

        <div class="w-full max-w-md text-center">

            <div
                id="verify-icon"
                class="mx-auto mb-6 flex size-16 items-center justify-center rounded-2xl bg-lime-400/10 text-3xl text-lime-300"
            >
                ✉
            </div>

            <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-lime-400">
                PUTU / Verifikasi Email
            </p>

            <h1
                id="verify-title"
                class="text-3xl font-semibold tracking-tight"
            >
                Memeriksa email<span class="text-lime-400">.</span>
            </h1>

            <p
                id="verify-message"
                role="status"
                aria-live="polite"
                class="mt-5 text-sm leading-7 text-stone-400"
            >
                Sedang memeriksa hasil konfirmasi email melalui Supabase Auth.
            </p>

            <a
                href="<?= e(url('login')) ?>"
                class="mt-8 inline-flex min-h-11 items-center justify-center rounded-xl bg-lime-500 px-6 text-sm font-semibold text-stone-950 transition-colors hover:bg-lime-400"
            >
                Kembali ke Login
            </a>

            <p class="mt-6 text-xs leading-6 text-stone-500">
                PUTU / Academic Workspace
            </p>

        </div>

    </main>

    <script>
    (async function () {
        const title = document.getElementById('verify-title');
        const message = document.getElementById('verify-message');
        const icon = document.getElementById('verify-icon');

        const supabaseUrl = <?= json_encode(
            $supabaseUrl,
            JSON_HEX_TAG | JSON_HEX_AMP |
            JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?>;

        const supabaseKey = <?= json_encode(
            $supabaseKey,
            JSON_HEX_TAG | JSON_HEX_AMP |
            JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?>;

        const params = new URLSearchParams(
            window.location.hash.substring(1)
        );

        const accessToken = params.get('access_token');

        const error = params.get('error')
            || params.get('error_description');

        // Hapus token dari URL browser secepat mungkin.
        history.replaceState(
            null,
            '',
            window.location.pathname + window.location.search
        );

        function updateStatus(heading, description, symbol) {
            title.textContent = heading;
            message.textContent = description;
            icon.textContent = symbol;
        }

        if (error) {
            updateStatus(
                'Verifikasi gagal',
                'Link konfirmasi tidak valid atau sudah kedaluwarsa. Silakan meminta link verifikasi baru.',
                '!'
            );
            return;
        }

        if (!accessToken) {
            updateStatus(
                'Link verifikasi tidak ditemukan',
                'Silakan buka halaman ini melalui link konfirmasi yang dikirim ke email kamu.',
                '✉'
            );
            return;
        }

        if (!supabaseUrl || !supabaseKey) {
            updateStatus(
                'Konfigurasi belum lengkap',
                'Layanan verifikasi sedang tidak tersedia. Silakan coba lagi nanti.',
                '!'
            );
            return;
        }

        try {
            const response = await fetch(
                supabaseUrl + '/auth/v1/user',
                {
                    method: 'GET',
                    headers: {
                        'apikey': supabaseKey,
                        'Authorization': 'Bearer ' + accessToken
                    },
                    cache: 'no-store',
                    referrerPolicy: 'no-referrer'
                }
            );

            if (!response.ok) {
                throw new Error('Validasi sesi gagal');
            }

            const user = await response.json();

            if (!user.id || !user.email_confirmed_at) {
                throw new Error('Email belum terkonfirmasi');
            }

            updateStatus(
                'Email berhasil diverifikasi!',
                'Alamat email kamu telah berhasil dikonfirmasi oleh Supabase Auth.',
                '✓'
            );

            title.classList.add('text-lime-300');

        } catch (err) {
            updateStatus(
                'Verifikasi belum dapat dipastikan',
                'Kami tidak dapat memeriksa hasil verifikasi. Silakan periksa status akun atau coba lagi nanti.',
                '!'
            );
        }
    })();
    </script>

</body>
</html>
