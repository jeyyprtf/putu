<?php
/**
 * Halaman anggota kelas.
 *
 *   1. guard     : wajib login + punya kelas
 *   2. muat data : modul app/anggota.php
 *   3. render    : daftar kartu + panel "Tentang kelas"
 *
 * Pencarian & filter jabatan dikerjakan di sisi klien.
 */
$page = 'anggota';
$title = 'Anggota Kelas';
require_once __DIR__ . '/../bootstrap.php';

$user = require_login();
$idUser = (int) $user['id_user'];
$kelasId = (int) $user['id_kelas'];

$anggota = [];
$kelas = null;
$semester = '';
$galatMuat = '';

try {
    $kelas = anggota_kelas($kelasId);
    $semester = anggota_semester($kelasId);
    $anggota = anggota_daftar($kelasId, $idUser);
} catch (Throwable $e) {
    error_log('[anggota] ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    $galatMuat = 'DEBUG: ' . $e->getMessage();
}


$totalAnggota = count($anggota);
$totalAdmin = 0;
foreach ($anggota as $m) {
    if (($m['role'] ?? '') === 'admin') {
        $totalAdmin++;
    }
}

$labelKelas = trim((string) ($kelas['nama_kelas'] ?? ''));
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
                <section class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:mt-0 sm:text-4xl">
                        Anggota kelas<span class="text-lime-300">.</span>
                    </h1>
                    <p class="shrink-0 text-sm text-stone-400">
                        <?php if ($labelKelas !== ''): ?><?= e($labelKelas) ?> · <?php endif; ?><?= $totalAnggota ?> anggota
                    </p>
                </section>

                <?php if ($galatMuat !== ''): ?>
                    <p role="alert" class="mb-4 rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-sm text-red-300"><?= e($galatMuat) ?></p>
                <?php endif; ?>

                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <label for="member-search" class="sr-only">Cari nama anggota</label>
                        <input id="member-search" type="search" autocomplete="off" placeholder="Cari nama anggota" class="field">
                    </div>
                    <div class="flex shrink-0 gap-2 overflow-x-auto" role="group" aria-label="Filter jabatan">
                        <button type="button" data-member-filter="Semua" aria-pressed="true" class="filter-tab">Semua</button>
                        <button type="button" data-member-filter="Admin kelas" aria-pressed="false" class="filter-tab">Admin kelas</button>
                        <button type="button" data-member-filter="Mahasiswa" aria-pressed="false" class="filter-tab">Mahasiswa</button>
                    </div>
                </div>

                <p id="member-result" role="status" aria-live="polite" class="sr-only"><?= $totalAnggota ?> anggota</p>

                <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_21rem]">
                    <section id="member-list" aria-label="Daftar anggota" class="flex flex-col gap-3">
                        <?php if ($anggota === [] && $galatMuat === ''): ?>
                            <div class="panel px-6 py-14 text-center">
                                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('users') ?></span>
                                <h2 class="mt-4 text-lg font-semibold">Belum ada anggota lain.</h2>
                                <p class="mt-2 text-sm text-stone-400">
                                    Anggota kelas akan muncul di sini setelah bergabung.
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($anggota as $m): ?>
                            <?php require __DIR__ . '/../components/member-card.php'; ?>
                        <?php endforeach; ?>

                        <div id="members-empty" class="panel hidden px-6 py-14 text-center">
                            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('users') ?></span>
                            <h2 class="mt-4 text-lg font-semibold">Tidak ada anggota yang cocok.</h2>
                            <p class="mt-2 text-sm text-stone-400">Coba kata kunci atau filter lainnya.</p>
                            <button type="button" data-member-reset class="button button-secondary mt-5">Reset filter</button>
                        </div>
                    </section>

                    <aside class="panel p-5 xl:sticky xl:top-24">
                        <h2 class="text-lg font-semibold">Tentang kelas</h2>
                        <dl class="mt-2 divide-y divide-stone-800 text-sm">
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Kelas</dt>
                                <dd class="text-right font-medium"><?= e($labelKelas !== '' ? $labelKelas : '—') ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Semester</dt>
                                <dd class="text-right font-medium"><?= e($semester !== '' ? $semester : '—') ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Anggota</dt>
                                <dd class="text-right font-medium"><?= $totalAnggota ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Admin kelas</dt>
                                <dd class="text-right font-medium"><?= $totalAdmin ?></dd>
                            </div>
                        </dl>
                    </aside>
                </div>

                <noscript>
                    <p class="mt-5 text-sm text-amber-200">
                        Aktifkan JavaScript untuk mencari dan memfilter anggota.
                    </p>
                </noscript>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
        <script>
            (() => {
                const search = document.getElementById('member-search');
                const result = document.getElementById('member-result');
                const tabs = [...document.querySelectorAll('[data-member-filter]')];
                const cards = [...document.querySelectorAll('[data-member]')];
                const empty = document.getElementById('members-empty');
                const reset = document.querySelector('[data-member-reset]');
                if (!search || !cards.length) return;

                let jabatan = 'Semua';

                const apply = () => {
                    const q = search.value.trim().toLowerCase();
                    let shown = 0;

                    cards.forEach((card) => {
                        const cocokJabatan = jabatan === 'Semua' || card.dataset.memberRole === jabatan;
                        const cocokKata = q === '' || card.dataset.memberSearch.includes(q);
                        const tampil = cocokJabatan && cocokKata;
                        card.classList.toggle('hidden', !tampil);
                        if (tampil) shown++;
                    });

                    empty.classList.toggle('hidden', shown !== 0);
                    result.textContent = shown + ' anggota';
                };

                search.addEventListener('input', apply);

                tabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        jabatan = tab.dataset.memberFilter;
                        tabs.forEach((t) => t.setAttribute('aria-pressed', String(t === tab)));
                        apply();
                    });
                });

                reset.addEventListener('click', () => {
                    search.value = '';
                    jabatan = 'Semua';
                    tabs.forEach((t) => t.setAttribute('aria-pressed', String(t.dataset.memberFilter === 'Semua')));
                    apply();
                });
            })();
        </script>
    </body>
</html>
