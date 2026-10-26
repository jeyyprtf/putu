<aside class="fixed inset-y-3 left-3 z-40 hidden w-60 flex-col rounded-3xl border border-stone-800 bg-stone-900/60 p-5 lg:flex">
    <a href="<?= e(url()) ?>" class="flex items-center gap-3 px-5 py-2" aria-label="PUTU beranda">
        <span class="flex size-9 items-center justify-center rounded-xl bg-lime-500 text-lime-950"><?= icon('book') ?></span> <span class="text-3xl font-extrabold tracking-tighter">putu<span class="text-lime-500">.</span></span>
    </a>
    <p class="mt-3 px-2 text-[11px] leading-5 text-stone-400">
        A little less chaos. A lot more focus.
    </p>
    <div class="mt-6 flex items-center gap-3 rounded-2xl border border-stone-800 bg-stone-950/50 p-3">
        <span class="text-lime-300"><?= icon('book') ?></span>
        <div>
            <p class="text-xs font-semibold leading-5">
                Sistem Informasi Bisnis
            </p>
            <p class="text-xs text-stone-400">
                SIB · 2B
            </p>
        </div>
    </div>
    <p class="mt-7 mb-3 px-3 text-[10px] font-semibold uppercase tracking-widest text-stone-500">
        Workspace
    </p>
    <nav aria-label="Navigasi utama" class="space-y-2">
        <a href="<?= e(url('dashboard')) ?>" class="nav-link" <?= $page === 'dashboard' ? 'aria-current="page"' : '' ?>>
            <?= icon('grid') ?><span>Ringkasan</span>
        </a>
        <a href="<?= e(url('tugas')) ?>" class="nav-link" <?= $page === 'tugas' ? 'aria-current="page"' : '' ?>>
            <?= icon('tasks') ?><span>Tugas saya</span> <span data-count="active" class="ml-auto text-xs"><?= $taskCounts['active'] ?></span>
        </a>
        <a href="<?= e(url('jadwal')) ?>" class="nav-link" <?= $page === 'jadwal' ? 'aria-current="page"' : '' ?>>
            <?= icon('calendar') ?><span>Jadwal kuliah</span>
        </a>
        <a href="<?= e(url('pengumuman')) ?>" class="nav-link" <?= $page === 'pengumuman' ? 'aria-current="page"' : '' ?>>
            <?= icon('bell') ?><span>Pengumuman</span>
        </a>
    </nav>
    <div class="mt-auto space-y-3 pt-8">
        <details class="rounded-2xl border border-stone-800 p-3 text-xs text-stone-400">
            <summary class="flex min-h-6 items-center gap-2 text-stone-300">
                <?= icon('info', 'size-4') ?>Panduan singkat
            </summary>
            <p class="mt-3 leading-6">
                Pilih tugas untuk melihat detail. Ubah status untuk mencoba progres pribadi. Ini pratinjau; perubahan hanya berlaku di tab ini, tanpa akun atau database.
            </p>
        </details>
        <a href="<?= e(url()) ?>" class="nav-link">
            <?= icon('logout') ?>Kembali ke beranda
        </a>
    </div>
</aside>
