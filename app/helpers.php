<?php

date_default_timezone_set('Asia/Jakarta');

function e(string|int $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url(): string
{
    $directory = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
    if (str_ends_with($directory, '/app/pages')) {
        $directory = dirname($directory, 2);
    }
    return rtrim($directory, '/.');
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
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'tasks' => '<path d="M21 11v1a9 9 0 1 1-5.3-8.2"/><path d="m9 11 3 3L22 4"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'book' => '<path d="M12 5v16M12 5C9 2 5 3 2 3v16c4 0 7 0 10 2 3-2 6-2 10-2V3c-3 0-7-1-10 2Z"/>',
        'arrow' => '<path d="M7 17 17 7M7 7h10v10"/>',
        'sort' => '<path d="M12 4v16m-6-6 6 6 6-6"/>',
        'cycle' => '<path d="M20 7a8 8 0 0 0-14-2L3 8m0-5v5h5M4 17a8 8 0 0 0 14 2l3-3m0 5v-5h-5"/>',
        'back' => '<path d="M19 12H5m6-6-6 6 6 6"/>',
        'chevron' => '<path d="m9 5 7 7-7 7"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'location' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    ];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['info']) . '</svg>';
}
