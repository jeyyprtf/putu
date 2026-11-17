<nav aria-label="Navigasi utama mobile" class="dock fixed inset-x-3 z-40 mx-auto flex max-w-sm items-center gap-1 rounded-full border border-stone-700/60 bg-stone-900/95 p-2 shadow-2xl backdrop-blur-xl lg:hidden">
    <a href="<?= e(url('dashboard')) ?>" <?= $page === 'dashboard' ? 'aria-current="page"' : '' ?> class="nav-link min-w-0 flex-1 flex-col justify-center gap-1! px-1! py-2! text-[10px]!">
        <?= icon('grid') ?><span>Ringkasan</span>
    </a>
    <a href="<?= e(url('tugas')) ?>" <?= $page === 'tugas' ? 'aria-current="page"' : '' ?> class="nav-link min-w-0 flex-1 flex-col justify-center gap-1! px-1! py-2! text-[10px]!">
        <?= icon('tasks') ?><span>Tugas saya</span>
    </a>
    <a href="<?= e(url('jadwal')) ?>" <?= $page === 'jadwal' ? 'aria-current="page"' : '' ?> class="nav-link min-w-0 flex-1 flex-col justify-center gap-1! px-1! py-2! text-[10px]!">
        <?= icon('calendar') ?><span>Jadwal</span>
    </a>
    <a href="<?= e(url('pengumuman')) ?>" <?= $page === 'pengumuman' ? 'aria-current="page"' : '' ?> class="nav-link min-w-0 flex-1 flex-col justify-center gap-1! px-1! py-2! text-[10px]!">
        <?= icon('bell') ?><span>Pengumuman</span>
    </a>
    <details class="relative min-w-0 flex-1">
        <summary class="nav-link min-h-11 cursor-pointer list-none flex-col justify-center gap-1 px-1 py-2 text-[10px]">
            <?= icon('user') ?><span>Lainnya</span>
        </summary>
        <div class="absolute right-0 bottom-full mb-3 w-48 rounded-2xl border border-stone-700 bg-stone-900 p-2 shadow-2xl">
            <a href="<?= e(url('pengaturan')) ?>" class="nav-link">
                <?= icon('user') ?><span>Profil &amp; pengaturan</span>
            </a>
            <form action="<?= e(url('logout')) ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="nav-link w-full">
                    <?= icon('logout') ?><span>Keluar</span>
                </button>
            </form>
        </div>
    </details>
</nav>
