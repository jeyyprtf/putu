<?php if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=pengumuman'); exit; } ?>
<section class="mb-8"><p class="eyebrow">Stay in the loop</p><h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Kabar kelas<span class="text-lime-300">.</span></h1><p class="mt-3 text-sm leading-6 text-stone-400">Informasi penting punya tempatnya sendiri. Untuk SIB 2B.</p></section>
<div class="mb-6 flex flex-wrap gap-2" role="group" aria-label="Kategori pengumuman">
    <?php foreach (['Semua', 'Penting', 'Akademik', 'Kesempatan', 'Agenda'] as $category): ?><button type="button" data-news-filter="<?= e($category) ?>" aria-pressed="<?= $category === 'Semua' ? 'true' : 'false' ?>" class="filter-tab"><?= e($category) ?></button><?php endforeach; ?>
</div>
<p id="news-result" role="status" aria-live="polite" class="mb-4 text-xs text-stone-400"><?= count($announcements) ?> pengumuman</p>
<div class="max-w-4xl space-y-4">
    <?php foreach ($announcements as $news): ?>
        <article class="panel p-5 sm:p-7" data-news-card data-category="<?= e($news['category']) ?>" data-important="<?= $news['important'] ? 'true' : 'false' ?>" id="news-<?= e($news['id']) ?>">
            <div class="flex flex-wrap items-center gap-2"><span class="tag bg-stone-800 text-stone-300"><?= e($news['category']) ?></span><?php if ($news['important']): ?><span class="tag bg-amber-300/10 text-amber-200"><?= icon('bell', 'size-3') ?>Penting</span><?php endif; ?><span class="ml-auto text-xs text-stone-400"><?= e(date_id($news['date'])) ?></span></div>
            <h2 class="mt-5 text-xl font-semibold leading-8 tracking-tight"><?= e($news['title']) ?></h2>
            <p class="mt-2 text-xs text-stone-400">Oleh <?= e($news['author']) ?></p>
            <p class="mt-4 text-sm leading-7 text-stone-400"><?= e($news['summary']) ?></p>
            <details class="mt-4" <?= ($_GET['id'] ?? '') === $news['id'] ? 'open' : '' ?>>
                <summary class="flex min-h-11 w-fit items-center gap-2 text-xs font-semibold text-lime-300">Baca selengkapnya<span class="details-arrow transition-transform"><?= icon('chevron', 'size-3') ?></span></summary>
                <div class="mt-3 border-t border-stone-800 pt-4 text-sm leading-8 text-stone-300"><?= e($news['body']) ?></div>
            </details>
        </article>
    <?php endforeach; ?>
    <div id="news-empty" hidden class="panel px-6 py-14 text-center"><span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('bell') ?></span><h2 class="mt-4 text-lg font-semibold">Belum ada kabar di kategori ini.</h2><p class="mt-2 text-sm text-stone-400">Coba lihat semua pengumuman kelas.</p><button type="button" data-news-reset class="button button-secondary mt-5">Lihat semua</button></div>
</div>
