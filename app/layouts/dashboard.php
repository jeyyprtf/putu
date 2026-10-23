<?php if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=dashboard'); exit; } ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0c0a09">
    <title>PUTU | <?= e($menu[$page]['label']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require __DIR__ . '/../components/fonts.php'; ?>
    <link rel="stylesheet" href="<?= e(asset('resources/css/app.css')) ?>">
    <script id="task-data" type="application/json" data-today="<?= $today->format('Y-m-d') ?>"><?= json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script src="<?= e(asset('public/assets/js/app.js')) ?>" defer></script>
</head>
<body class="bg-stone-950 text-stone-100 font-sans antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 button">Lewati navigasi</a>
    <?php require __DIR__ . '/../components/sidebar.php'; ?>
    <div class="lg:ml-68">
        <?php require __DIR__ . '/../components/navbar.php'; ?>
        <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
            <?php require __DIR__ . '/../pages/' . $page . '.php'; ?>
            <footer class="mt-12 flex flex-col gap-2 border-t border-stone-800 pt-5 text-xs leading-5 text-stone-400 sm:flex-row sm:justify-between">
                <p>© <?= date('Y') ?> PUTU. Make room for what matters.</p>
                <p>Pratinjau · Data contoh · Perubahan sementara di tab ini</p>
            </footer>
        </main>
    </div>
    <nav aria-label="Navigasi utama mobile" class="dock fixed inset-x-3 z-40 mx-auto flex max-w-sm items-center gap-1 rounded-full border border-stone-700/60 bg-stone-900/95 p-2 shadow-2xl backdrop-blur-xl lg:hidden">
        <?php foreach ($menu as $key => $item): ?>
            <a href="<?= e(url($key)) ?>" <?= $page === $key ? 'aria-current="page"' : '' ?> class="nav-link min-w-0 flex-1 flex-col justify-center gap-1! px-1! py-2! text-[10px]!">
                <?= icon($item['icon']) ?><span><?= e($item['short']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</body>
</html>
