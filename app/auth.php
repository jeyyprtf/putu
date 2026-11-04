<?php
/**
 * Autentikasi berbasis session PHP.
 *
 * Alur singkat:
 *   - login_user($id)  : simpan id user ke session (ID session di-regenerate).
 *   - current_user()   : ambil data user terbaru dari DB (di-cache per request).
 *   - require_login()  : pagar halaman yang butuh login.
 *   - csrf_field()     : wajib ada di setiap <form method="POST">.
 *
 * Session baru dibuka kalau perlu: pengunjung anonim yang tidak membuka form
 * login/register tidak diberi cookie.
 */

const SESSION_NAME = 'putu_session';

/**
 * Buka session dengan cookie yang aman. Aman dipanggil berulang kali.
 * Harus dipanggil SEBELUM ada output HTML (cookie dikirim lewat header).
 */
function session_boot(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    // Tolak session ID buatan penyerang yang belum pernah dibuat server.
    ini_set('session.use_strict_mode', '1');
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0, // hilang saat browser ditutup
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * User yang sedang login (baris dari DB + nama kelas), atau null.
 * Data diambil dari DB supaya perubahan role/kelas langsung terbaca.
 */
function current_user(bool $refresh = false): ?array
{
    static $user = false; // false = belum dimuat, null = tidak login

    if ($refresh) {
        $user = false;
    }
    if ($user !== false) {
        return $user;
    }

    // Tanpa cookie session tidak mungkin login: hindari membuka session & query DB.
    if (session_status() === PHP_SESSION_NONE) {
        if (!isset($_COOKIE[SESSION_NAME])) {
            return $user = null;
        }
        session_boot();
    }

    $id = $_SESSION['uid'] ?? null;
    if (!is_int($id)) {
        return $user = null;
    }

    $user = db_one(
        'SELECT u.id_user, u.nama, u.email, u.role, u.id_kelas, k.nama_kelas, k.kode_gabung
         FROM users AS u
         LEFT JOIN kelas AS k ON k.id_kelas = u.id_kelas
         WHERE u.id_user = :id',
        ['id' => $id]
    );

    // Akun sudah dihapus tapi session masih ada.
    if ($user === null) {
        logout_user();
    }

    return $user;
}

/**
 * Tandai user sebagai login. Dipanggil setelah password terverifikasi / akun baru dibuat.
 */
function login_user(int $idUser): void
{
    session_boot();
    session_regenerate_id(true); // cegah session fixation
    $_SESSION['uid'] = $idUser;
    unset($_SESSION['csrf']);    // token CSRF baru untuk session baru
    current_user(true);
}

/**
 * Hapus session dan cookie-nya.
 */
function logout_user(): void
{
    session_boot();
    $_SESSION = [];

    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $p['path'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'],
    ]);

    session_destroy();
    unset($_COOKIE[SESSION_NAME]);
    current_user(true);
}

/**
 * Pagar halaman. Belum login -> /login. Login tapi belum punya kelas -> /gabung.
 * Halaman yang boleh diakses tanpa kelas (mis. gabung) memanggil require_login(false).
 */
function require_login(bool $needsClass = true): array
{
    $user = current_user();

    if ($user === null) {
        redirect(url('login'));
    }
    if ($needsClass && $user['id_kelas'] === null) {
        redirect(url('gabung'));
    }

    return $user;
}

/**
 * Halaman khusus tamu (login/register). Yang sudah login diarahkan ke halaman utamanya.
 * Sekalian membuka session supaya csrf_field() aman dipanggil di tengah HTML.
 */
function require_guest(): void
{
    $user = current_user();
    if ($user !== null) {
        redirect(home_url_for($user));
    }
    csrf_token();
}

/**
 * Tujuan setelah login: belum punya kelas -> gabung, selain itu -> dashboard.
 */
function home_url_for(array $user): string
{
    return url($user['id_kelas'] === null ? 'gabung' : 'dashboard');
}

function role_label(array $user): string
{
    return $user['role'] === 'admin' ? 'Admin kelas' : 'Mahasiswa';
}

// ---------------------------------------------------------------- CSRF

function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Input hidden yang wajib ada di setiap form POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Cek token dari form POST. */
function csrf_verify(): bool
{
    $sent = $_POST['_csrf'] ?? null;
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}
