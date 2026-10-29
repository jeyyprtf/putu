<header class="sticky top-0 z-30 border-b border-stone-800/80 bg-stone-950/90 backdrop-blur-xl">
    <div class="mx-auto flex min-h-18 max-w-7xl items-center justify-between gap-3 px-5 sm:px-8 lg:px-10">
        <a href="<?= e(url()) ?>" class="flex items-center gap-2 lg:hidden" aria-label="PUTU beranda">
            <span class="text-2xl font-extrabold tracking-tighter">putu<span class="text-lime-500">.</span></span>
        </a>
        <p class="hidden text-xs text-stone-400 lg:block">
            Workspace <span class="mx-3 text-stone-600">/</span> <span class="text-stone-200"><?= e($title) ?></span>
        </p>
        <div class="flex items-center gap-3 sm:gap-5">
            <p class="hidden text-xs text-stone-400 sm:block">
                <?= e($days[$todayIndex]) ?>, <?= e(date_id('today', true)) ?>
            </p>
            <a href="<?= e(url('pengumuman')) ?>" class="icon-button relative" aria-label="Lihat pengumuman">
                <?= icon('bell') ?><span class="absolute top-2 right-2 size-1.5 rounded-full bg-lime-400"></span>
            </a>
            <a href="<?= e(url('pengaturan')) ?>" class="flex min-h-11 items-center gap-3 rounded-full hover:text-lime-200" aria-label="Buka profil dan pengaturan" <?= $page === 'pengaturan' ? 'aria-current="page"' : '' ?>>
                <span class="flex size-10 items-center justify-center rounded-full bg-lime-300/15 text-sm font-semibold text-lime-200"><?= e($profile['initial']) ?></span>
                <div class="hidden sm:block">
                    <p class="max-w-32 truncate text-xs font-semibold">
                        <?= e($profile['name']) ?>
                    </p>
                    <p class="mt-1 text-[10px] text-stone-400">
                        Mahasiswa · Pratinjau
                    </p>
                </div>
            </a>
        </div>
    </div>
</header>
