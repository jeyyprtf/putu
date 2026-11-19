<?php

date_default_timezone_set('Asia/Jakarta');

function e(string|int $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Ambil input POST sebagai string yang sudah di-trim.
 * Input bukan string (mis. name[]=x) dianggap kosong.
 */
function post_str(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/**
 * Jumlah karakter UTF-8 (tanpa mbstring), atau null kalau bukan UTF-8 valid
 * atau mengandung karakter kontrol.
 *
 * $allowNewline dipakai untuk teks panjang (mis. textarea) yang boleh berisi
 * baris baru. Tanpa ini, newline dihitung sebagai karakter kontrol dan input
 * dianggap tidak valid.
 */
function text_length(string $value, bool $allowNewline = false): ?int
{
    if (preg_match('//u', $value) !== 1) {
        return null;
    }

    // \p{Cc} = semua karakter kontrol. Kalau newline diizinkan, keluarkan
    // \x0A (LF) dan \x0D (CR) dari larangan.
    $pola = $allowNewline ? '/[\x00-\x09\x0B-\x1F\x7F]/' : '/\p{Cc}/u';
    if (preg_match($pola, $value) !== 0) {
        return null;
    }

    $count = preg_match_all('/./su', $value);
    return $count === false ? null : $count;
}

/** Huruf pertama nama dalam huruf besar (aman untuk UTF-8 tanpa mbstring). */
function first_letter(string $text): string
{
    return preg_match('/^./u', $text, $m) === 1 ? strtoupper($m[0]) : '?';
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

/**
 * URL dasar aplikasi, tidak tergantung kedalaman folder halaman.
 * Mendukung /index.php, /app/pages/jadwal.php, dan /app/pages/admin/jadwal.php,
 * termasuk aplikasi yang dipasang di subfolder (mis. /putu).
 */
function base_url(): string
{
    $script = str_replace(
        '\\',
        '/',
        $_SERVER['SCRIPT_NAME'] ?? '/index.php'
    );

    // Halaman langsung dari app/pages/
    if (preg_match(
        '~^(.*)/app/pages/(?:admin/)?[^/]+\.php$~',
        $script,
        $match
    )) {
        return rtrim($match[1], '/');
    }

    // Halaman dari index.php
    $directory = str_replace('\\', '/', dirname($script));

    if ($directory === '/' || $directory === '.' || $directory === '') {
        return '';
    }

    return rtrim($directory, '/');
}

function url(string $page = '', array $query = []): string
{
    $path = $page === '' ? '/index.php' : '/app/pages/' . $page . '.php';
    return base_url() . $path . ($query === [] ? '' : '?' . http_build_query($query));
}

function asset(string $path): string
{
    return base_url() . '/' . $path;
}

function date_id(string $date, bool $year = false): string
{
    $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $day = new DateTimeImmutable($date);
    return $day->format('j') . ' ' . $months[(int) $day->format('n')] . ($year ? ' ' . $day->format('Y') : '');
}

function deadline_label(array $task): string
{
    if ($task['status'] === 'done') {
        return 'Selesai';
    }
    $days = (int) (new DateTimeImmutable('today'))->diff(new DateTimeImmutable($task['deadline']))->format('%r%a');
    return match (true) {
        $days < 0 => 'Terlambat',
        $days === 0 => 'Hari ini',
        $days === 1 => 'Besok',
        default => $days . ' hari lagi',
    };
}

function icon(string $name, string $class = 'size-5'): string
{
    $paths = [
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'users' => '<circle cx="9" cy="8" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2"/><path d="M16.4 4.3a4 4 0 0 1 0 7.4"/><path d="M22 21v-1.5a6 6 0 0 0-4.5-5.8"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'tasks' => '<rect x="8" y="2" width="8" height="4" rx="1.4"/><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><path d="M8 10h.01M11.5 10H16M8 15h.01M11.5 15H16"/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="17" rx="2"/><path d="M7.5 2v5M16.5 2v5M3 10h18"/><path d="M7.5 14h.01M12 14h.01M16.5 14h.01M7.5 18h.01M12 18h.01M16.5 18h.01"/>',
        'bell' => '<path d="M8 7h3c3.4 0 6.3-1.4 10-4v15c-3.7-2.6-6.6-4-10-4H8Z"/><path d="M8 7H5a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3M5.5 14l2.3 6a1.5 1.5 0 0 0 1.4 1h1a1 1 0 0 0 .9-1.4L8.5 14"/>',
        'book' => '<path d="M12 5v16M12 5C9 2 5 3 2 3v16c4 0 7 0 10 2 3-2 6-2 10-2V3c-3 0-7-1-10 2Z"/>',
        'arrow' => '<path d="M7 17 17 7M7 7h10v10"/>',
        'sort' => '<path d="M12 4v16m-6-6 6 6 6-6"/>',
        'cycle' => '<path d="M20 7a8 8 0 0 0-14-2L3 8m0-5v5h5M4 17a8 8 0 0 0 14 2l3-3m0 5v-5h-5"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'back' => '<path d="M19 12H5m6-6-6 6 6 6"/>',
        'chevron' => '<path d="m9 5 7 7-7 7"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'location' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4 10.5-10.5Z"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M5 6l1 15h12l1-15M10 10v7M14 10v7"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    ];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['info']) . '</svg>';
}
