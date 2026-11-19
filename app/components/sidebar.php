<aside class="fixed inset-y-3 left-3 z-40 hidden w-60 flex-col overflow-y-auto rounded-3xl border border-stone-800 bg-stone-900/60 p-5 lg:flex">
    <a href="<?= e(url()) ?>" class="flex items-center px-5 py-2" aria-label="PUTU beranda">
        <span class="text-3xl font-extrabold tracking-tighter">putu<span class="text-lime-500">.</span></span>
    </a>
    <p class="mt-3 px-2 text-[11px] leading-5 text-stone-400">
        A little less chaos. A lot more focus.
    </p>
    <div class="mt-6 flex items-center gap-3 rounded-3xl border border-stone-800 bg-stone-950/50 p-3">
        <span class="text-lime-300"><?= icon('book') ?></span>
        <div>
            <p class="text-xs font-semibold leading-5">
                <?= e($profile['kelas']) ?>
            </p>
            <p class="text-xs text-stone-400">
                <?= e($profile['role']) ?>
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
            <?= icon('tasks') ?><span>Daftar Tugas</span> <span data-count="active" class="ml-auto text-xs"><?= $taskCounts['active'] ?></span>
        </a>
        <a href="<?= e(url('jadwal')) ?>" class="nav-link" <?= $page === 'jadwal' ? 'aria-current="page"' : '' ?>>
            <?= icon('calendar') ?><span>Jadwal</span>
        </a>
        <a href="<?= e(url('pengumuman')) ?>" class="nav-link" <?= $page === 'pengumuman' ? 'aria-current="page"' : '' ?>>
            <?= icon('bell') ?><span>Pengumuman</span>
        </a>
        <?php
        $sidebarIsAdmin = ($user['role'] ?? '') === 'admin';
        $sidebarAdminAktif = $sidebarIsAdmin
            && ($page === 'admin' || str_starts_with($page, 'admin/'));

        $sidebarLainnyaAktif = in_array($page, ['anggota', 'pengaturan'], true)
            || $sidebarAdminAktif;
        ?>

        <details <?= $sidebarLainnyaAktif ? 'open' : '' ?>>
            <summary class="nav-link cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                <span aria-hidden="true" class="inline-flex size-5 items-center justify-center text-xl leading-none">⋮</span>
                <span>Lainnya</span>
            </summary>

            <div class="mt-2 space-y-1 rounded-2xl bg-stone-950/80 p-2">
                <a
                    href="<?= e(url('anggota')) ?>"
                    class="nav-link"
                    <?= $page === 'anggota' ? 'aria-current="page"' : '' ?>
                >
                    <?= icon('users') ?>
                    <span>Anggota kelas</span>
                </a>

                <a
                    href="<?= e(url('pengaturan')) ?>"
                    class="nav-link"
                    <?= $page === 'pengaturan' ? 'aria-current="page"' : '' ?>
                >
                    <?= icon('user') ?>
                    <span>Profil &amp; pengaturan</span>
                </a>

                <?php if ($sidebarIsAdmin): ?>
                    <a
                        href="<?= e(url('admin')) ?>"
                        class="nav-link"
                        <?= $page === 'admin' ? 'aria-current="page"' : '' ?>
                    >
                        <?= icon('grid') ?>
                        <span>Halaman admin</span>
                    </a>
                <?php endif; ?>
            </div>
        </details>
    </nav>
    <div class="mt-auto space-y-3 pt-8">
        <details class="rounded-3xl border border-stone-800 p-3 text-xs text-stone-400">
            <summary class="flex min-h-6 items-center gap-2 text-stone-300">
                <?= icon('info', 'size-4') ?>Panduan singkat
            </summary>
            <p class="mt-3 leading-6">
                Pilih tugas untuk melihat detail dan ubah statusnya. Sebagian data masih contoh sementara fitur database dikerjakan bertahap.
            </p>
        </details>
        <form
            action="<?= e(url('logout')) ?>"
            method="POST"
            data-logout-confirm
        >
            <?= csrf_field() ?>

            <button type="submit" class="nav-link w-full text-red-400!">
                <?= icon('logout') ?>Keluar
            </button>
        </form>

    </div>
</aside>

<?php require_once __DIR__ . '/logout-confirm.php'; ?>