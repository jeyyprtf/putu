<?php
$page = $_GET['page'] ?? 'dashboard';

$menu = [
    'dashboard' => 'Dashboard',
    'tugas' => 'Tugas',
    'jadwal' => 'Jadwal Kuliah',
    'pengumuman' => 'Pengumuman',
];

if (!array_key_exists($page, $menu)) {
    $page = 'dashboard';
}

$halaman = __DIR__ . '/../pages/' . $page . '.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUTULAGI | <?= htmlspecialchars($menu[$page]) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require __DIR__ . '/../components/fonts.php'; ?>
</head>

<body class="min-h-screen bg-[#111814] text-[#e2e9e3]">
    <div class="min-h-screen md:flex">

        <aside class="w-full border-b border-white/10 bg-[#1c2922] p-5 md:min-h-screen md:w-64 md:border-r">
            <a href="../../index.php" class="text-2xl font-bold">
                putu<span class="text-lime-400">.</span>
            </a>

            <p class="mb-5 mt-2 text-xs text-[#a3b0a7]">
                DEMO MAHASISWA
            </p>

            <nav class="flex flex-wrap gap-2 md:flex-col">
                <?php foreach ($menu as $key => $label): ?>
                    <a
                        href="../../index.php?page=<?= urlencode($key) ?>"
                        class="rounded-xl px-4 py-3 text-sm <?= $page === $key
                            ? 'bg-[#214333] text-white'
                            : 'text-[#a3b0a7] hover:bg-white/5 hover:text-white' ?>"
                    >
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 p-5 md:p-10">
            <?php
            if (is_file($halaman)) {
                require $halaman;
            } else {
                echo '<p>Halaman belum tersedia.</p>';
            }
            ?>
        </main>

    </div>
</body>
</html>
