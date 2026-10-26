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
</nav>
