<?php
$page = 'pengumuman';
$title = 'Pengumuman';
require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
    </head>
    <body class="bg-stone-950 text-stone-100 font-sans antialiased">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 button">
            Lewati navigasi
        </a>
        <?php require __DIR__ . '/../components/sidebar.php'; ?>
        <div class="lg:ml-68">
            <?php require __DIR__ . '/../components/navbar.php'; ?>
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10 lg:pt-10">
                <section class="mb-8">
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:mt-0 sm:text-4xl">
                        Kabar kelas<span class="text-lime-300">.</span>
                    </h1>
                </section>
                <div class="mb-3 flex flex-wrap gap-2 sm:mb-6" role="group" aria-label="Kategori pengumuman">
                    <button type="button" data-news-filter="Semua" aria-pressed="true" class="filter-tab">
                        Semua
                    </button>
                    <button type="button" data-news-filter="Penting" aria-pressed="false" class="filter-tab">
                        Penting
                    </button>
                </div>
                <p id="news-result" role="status" aria-live="polite" class="mb-3 text-xs text-stone-400 sm:mb-4">
                    <?= count($announcements) ?> pengumuman
                </p>
                <div class="max-w-4xl space-y-5 sm:space-y-4">
                    <?php foreach ($announcements as $news): ?>
                        <article class="panel p-5 sm:p-7" data-news-card data-important="<?= $news['important'] ? 'true' : 'false' ?>" id="news-<?= e($news['id']) ?>">
                            <div class="flex flex-wrap items-center gap-2">
                                <?php if ($news['important']): ?>
                                    <span class="tag bg-amber-300/10 text-amber-200"><?= icon('bell', 'size-3') ?>Penting</span>
                                <?php endif; ?>
                                <span class="ml-auto text-xs text-stone-400"><?= e(date_id($news['date'])) ?></span>
                            </div>
                            <h2 class="mt-5 text-xl font-semibold leading-8 tracking-tight">
                                <?= e($news['title']) ?>
                            </h2>
                            <p class="mt-2 text-xs text-stone-400">
                                Oleh <?= e($news['author']) ?>
                            </p>
                            <p class="mt-4 text-sm leading-7 text-stone-400">
                                <?= e($news['summary']) ?>
                            </p>
                            <details class="mt-4" <?= ($_GET['id'] ?? '') === $news['id'] ? 'open' : '' ?>>
                                <summary class="flex min-h-11 w-fit items-center gap-2 text-xs font-semibold text-lime-300">
                                    Baca selengkapnya<span class="details-arrow transition-transform"><?= icon('chevron', 'size-3') ?></span>
                                </summary>
                                <div class="mt-3 border-t border-stone-800 pt-4 text-sm leading-8 text-stone-300">
                                    <?= e($news['body']) ?>
                                </div>
                            </details>
                        </article>
                    <?php endforeach; ?>
                    <div id="news-empty" hidden class="panel px-6 py-14 text-center">
                        <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('bell') ?></span>
                        <h2 class="mt-4 text-lg font-semibold">
                            Belum ada pengumuman untuk filter ini.
                        </h2>
                        <p class="mt-2 text-sm text-stone-400">
                            Coba lihat semua pengumuman kelas.
                        </p>
                        <button type="button" data-news-reset class="button button-secondary mt-5">
                            Lihat semua
                        </button>
                    </div>
                </div>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
