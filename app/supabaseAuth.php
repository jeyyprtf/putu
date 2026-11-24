 <?php
/**
 * Integrasi PUTU dengan Supabase Auth.
 *
 * File ini hanya menangani komunikasi dengan API Supabase Auth.
 * Tidak membuat session PHP dan tidak mengubah public.users.
 */

/**
 * Mengirim permintaan HTTP ke Supabase Auth.
 */
function supabase_auth_request(
    string $endpoint,
    array $payload
): array {
    $baseUrl = rtrim(
        (string) env('SUPABASE_URL', ''),
        '/'
    );

    $apiKey = (string) env(
        'SUPABASE_PUBLISHABLE_KEY',
        ''
    );

    if ($baseUrl === '' || $apiKey === '') {
        throw new RuntimeException(
            'Konfigurasi Supabase Auth belum lengkap.'
        );
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException(
            'Ekstensi PHP cURL belum aktif.'
        );
    }

    $url = $baseUrl . '/auth/v1/' . ltrim($endpoint, '/');

    $curl = curl_init($url);

    if ($curl === false) {
        throw new RuntimeException(
            'Gagal membuat koneksi ke Supabase.'
        );
    }

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(
            $payload,
            JSON_THROW_ON_ERROR
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);

    try {
        $response = curl_exec($curl);

        if ($response === false) {
            throw new RuntimeException(
                'Koneksi Supabase gagal: ' . curl_error($curl)
            );
        }

        $status = (int) curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );
    } finally {
        curl_close($curl);
    }

    $data = json_decode($response, true);

    return [
        'status' => $status,
        'data' => is_array($data) ? $data : [],
    ];
}

/**
 * Mendaftarkan akun ke Supabase Auth.
 * Email konfirmasi dikirim otomatis oleh Supabase.
 */
function supabase_auth_register(
    string $nama,
    string $email,
    string $password
): array {
    $callback = 'https://putu.juan.web.id/app/pages/verifikasi-email.php';

    $endpoint = 'signup?redirect_to='
        . rawurlencode($callback);

    $result = supabase_auth_request($endpoint, [
        'email' => $email,
        'password' => $password,
        'data' => [
            'nama' => $nama,
        ],
    ]);

    $success = $result['status'] >= 200
        && $result['status'] < 300
        && !empty($result['data']['id']);

    return [
        'success' => $success,
        'status' => $result['status'],
    ];
}

/**
 * Login menggunakan email dan password Supabase Auth.
 * Tidak membuat session PHP.
 */
function supabase_auth_login(
    string $email,
    string $password
): array {
    $result = supabase_auth_request(
        'token?grant_type=password',
        [
            'email' => $email,
            'password' => $password,
        ]
    );

    $status = (int) $result['status'];
    $data = $result['data'];

    $errorCode = (string) (
        $data['error_code']
        ?? $data['code']
        ?? ''
    );

    $user = $data['user'] ?? null;

    if (
        $status >= 200 &&
        $status < 300 &&
        is_array($user) &&
        !empty($data['access_token'])
    ) {
        $authId = (string) ($user['id'] ?? '');
        $authEmail = strtolower((string) ($user['email'] ?? ''));

        if (
            !preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                $authId
            ) ||
            $authEmail !== strtolower($email)
        ) {
            throw new RuntimeException(
                'Identitas autentikasi tidak valid.'
            );
        }

        if (empty($user['email_confirmed_at'])) {
            return [
                'success' => false,
                'status' => $status,
                'error_code' => 'email_not_confirmed',
                'user' => null,
            ];
        }

        return [
            'success' => true,
            'status' => $status,
            'error_code' => '',
            'user' => $user,
        ];
    }

    return [
        'success' => false,
        'status' => $status,
        'error_code' => $errorCode,
        'user' => null,
    ];
}

/**
 * Cari atau buat profil PUTU untuk akun Supabase
 * yang telah berhasil login dan terverifikasi.
 *
 * Akun lama dengan email sama tidak otomatis
 * diambil alih atau dihubungkan.
 */
function supabase_auth_sync_profile(array $authUser): int
{
    $authId = (string) ($authUser['id'] ?? '');
    $email = strtolower(trim(
        (string) ($authUser['email'] ?? '')
    ));

    if (
        !preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $authId
        ) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        empty($authUser['email_confirmed_at'])
    ) {
        throw new RuntimeException(
            'Akun Supabase belum valid atau belum terverifikasi.'
        );
    }

    // Identitas Supabase adalah penghubung utama.
    $existing = db_one(
        'SELECT id_user
         FROM public.users
         WHERE auth_user_id = :auth_id',
        ['auth_id' => $authId]
    );

    if ($existing !== null) {
        return (int) $existing['id_user'];
    }

    // Jangan otomatis menautkan akun legacy
    // berdasarkan kecocokan email saja.
    $emailOwner = db_one(
        'SELECT id_user
         FROM public.users
         WHERE LOWER(email) = :email',
        ['email' => $email]
    );

    if ($emailOwner !== null) {
        throw new RuntimeException(
            'EMAIL_ALREADY_IN_USE'
        );
    }

    $metadata = $authUser['user_metadata'] ?? [];
    $nama = is_array($metadata)
        ? trim((string) ($metadata['nama'] ?? ''))
        : '';

    // Nama hanya dipakai sebagai profil, bukan role.
    if ($nama === '') {
        $nama = explode('@', $email, 2)[0];
    }

    $nama = mb_substr($nama, 0, 100);

    // ON CONFLICT mencegah pembuatan duplikat
    // jika ada dua login bersamaan.
    $inserted = db_one(
        "INSERT INTO public.users
            (
                nama,
                email,
                password_hash,
                role,
                id_kelas,
                auth_user_id
            )
         VALUES
            (
                :nama,
                :email,
                NULL,
                'mahasiswa',
                NULL,
                :auth_id
            )
         ON CONFLICT DO NOTHING
         RETURNING id_user",
        [
            'nama' => $nama,
            'email' => $email,
            'auth_id' => $authId,
        ]
    );

    if ($inserted !== null) {
        return (int) $inserted['id_user'];
    }

    // Kemungkinan profil dibuat request paralel.
    $existing = db_one(
        'SELECT id_user
         FROM public.users
         WHERE auth_user_id = :auth_id',
        ['auth_id' => $authId]
    );

    if ($existing !== null) {
        return (int) $existing['id_user'];
    }

    throw new RuntimeException(
        'EMAIL_ALREADY_IN_USE'
    );
}
