<?php
$dockTabs = [
    ['route' => 'dashboard',  'icon' => 'grid',     'label' => 'Ringkasan'],
    ['route' => 'tugas',      'icon' => 'tasks',    'label' => 'Tugas saya'],
    ['route' => 'jadwal',     'icon' => 'calendar', 'label' => 'Jadwal'],
    ['route' => 'pengumuman', 'icon' => 'bell',     'label' => 'Pengumuman'],
];

$dockMenu = [
    ['route' => 'anggota',    'icon' => 'users', 'label' => 'Anggota kelas'],
    ['route' => 'pengaturan', 'icon' => 'user',  'label' => 'Profil & pengaturan'],
];

$dockIsAdmin = ($user['role'] ?? '') === 'admin';

// dock.php: bedakan tujuan URL dan penanda halaman aktif.
if ($dockIsAdmin) {
    $dockMenu[] = ['route' => 'admin/admin', 'page' => 'admin', 'icon' => 'grid', 'label' => 'Halaman admin'];
}

$dockLainnyaAktif = in_array($page, ['anggota', 'pengaturan'], true)
    || ($dockIsAdmin && ($page === 'admin' || str_starts_with($page, 'admin/')));
?>

<nav
    aria-label="Navigasi utama mobile"
    class="group/dock fixed inset-x-3 bottom-[calc(0.75rem_+_env(safe-area-inset-bottom,0px))]
       z-40 mx-auto max-w-sm rounded-4xl border border-stone-700/60
       bg-stone-900/95 shadow-2xl backdrop-blur-xl lg:hidden"
>
    <div class="mx-auto grid h-16 max-w-lg grid-cols-5 items-center px-1">

        <?php foreach ($dockTabs as $tab): ?>
            <a
                href="<?= e(url($tab['route'])) ?>"
                aria-label="<?= e($tab['label']) ?>"
                <?= $page === $tab['route'] ? 'aria-current="page"' : '' ?>
                class="group/tab flex min-h-11 min-w-0 flex-col items-center
                       justify-center gap-1 rounded-xl text-stone-400
                       aria-[current=page]:font-semibold aria-[current=page]:text-lime-400"
            >
                <span
                    aria-hidden="true"
                    class="grid h-12 w-15 place-items-center rounded-full
                           group-aria-[current=page]/tab:bg-lime-500/15"
                >
                    <?= icon($tab['icon'], 'size-5') ?>
                </span>
            </a>
        <?php endforeach; ?>

        <button
            type="button"
            aria-label="Lainnya"
            popovertarget="dock-lainnya"
            data-active="<?= $dockLainnyaAktif ? 'true' : 'false' ?>"
            class="group/more flex min-h-11 min-w-0 flex-col items-center
                   justify-center gap-1 rounded-xl text-stone-400
                   data-[active=true]:font-semibold data-[active=true]:text-lime-400
                   group-has-[:popover-open]/dock:text-lime-400"
        >
            <span
                aria-hidden="true"
                class="flex h-12 w-15 items-center justify-center gap-1 rounded-full
                       group-data-[active=true]/more:bg-lime-500/15
                       group-has-[:popover-open]/dock:bg-lime-500/15"
            >
                <span class="size-1 rounded-full bg-current"></span>
                <span class="size-1 rounded-full bg-current"></span>
                <span class="size-1 rounded-full bg-current"></span>
            </span>
        </button>
    </div>

    <div
        id="dock-lainnya"
        popover="auto"
        class="fixed inset-auto right-[max(0.75rem,calc((100%_-_32rem)/2_+_0.5rem))]
               bottom-[calc(4.5rem_+_env(safe-area-inset-bottom,0px))]
               mb-2 w-56 max-w-[calc(100%_-_1.5rem)] rounded-4xl
               border border-stone-700 bg-stone-950 p-1.5 text-stone-100 shadow-2xl"
    >
        <?php foreach ($dockMenu as $item): ?>
            <a
                href="<?= e(url($item['route'])) ?>"
                <?= $page === ($item['page'] ?? $item['route']) ? 'aria-current="page"' : '' ?>
                class="flex min-h-11 items-center gap-3 rounded-4xl px-3 text-sm
                       text-stone-300 hover:bg-stone-800
                       aria-[current=page]:bg-lime-500/15 aria-[current=page]:text-lime-300"
            >
                <?= icon($item['icon'], 'size-5') ?>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
