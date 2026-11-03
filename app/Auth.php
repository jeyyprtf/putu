<?php

/**
 * Helper sesi dan CSRF untuk autentikasi PUTU.
 * Dipakai oleh register.php (dan nanti login.php, logout, dan halaman yang butuh login).
 */

/**
 * Mulai session dengan cookie yang aman. Aman dipanggil berulang kali.
 */
function auth_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * id_user yang sedang login, atau null kalau belum login.
 */
function auth_user_id(): ?int
{
    auth_session_start();
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/**
 * Tandai user sebagai login. Id session diganti untuk mencegah session fixation.
 */
function auth_login(int $userId): void
{
    auth_session_start();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    unset($_SESSION['csrf']);
}

/**
 * Akhiri session (untuk logout).
 */
function auth_logout(): void
{
    auth_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?: 'Lax',
        ]);
    }
    session_destroy();
}

/**
 * Token CSRF untuk satu session. Taruh di form sebagai input hidden "csrf".
 */
function csrf_token(): string
{
    auth_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Cocokkan token dari form dengan token di session.
 */
function csrf_verify(mixed $token): bool
{
    auth_session_start();
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}