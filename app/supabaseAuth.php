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
