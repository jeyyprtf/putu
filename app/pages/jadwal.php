<?php
/** Halaman jadwal kuliah: cek kelas, muat agenda, lalu tampilkan per hari. */
$page = 'jadwal';
$title = 'Jadwal kuliah';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../jadwal.php';

$user = require_login();
$kelasId = (int) ($user['id_kelas'] ?? 0);
$isAdmin = ($user['role'] ?? '') === 'admin';
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$hariIni = new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));
$todayIndex = (int) $hariIni->format('N') - 1;

// ID kelas selalu berasal dari sesi pengguna.
$jadwal = [];
$galatMuat = '';
if ($kelasId < 1) {
    $galatMuat = 'Kamu belum tergabung dalam kelas.';
} else {
    try {
        $jadwal = jadwal_daftar($kelasId);
    } catch (Throwable $e) {
        error_log('[jadwal] muat daftar: ' . $e->getMessage());
        $galatMuat = 'Jadwal belum bisa dimuat. Coba muat ulang halaman.';
    }
}
$classesByDay = jadwal_per_hari($jadwal);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../components/head.php'; ?>
</head>
<body class="bg-stone-950 text-stone-100 font-sans antialiased">
    <?php require __DIR__ . '/../components/sidebar.php'; ?>
    <div class="lg:ml-68">
        <?php require __DIR__ . '/../components/navbar.php'; ?>
        <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Jadwal kuliah<span class="text-lime-300">.</span></h1>
                <?php if ($isAdmin && $kelasId > 0): ?>
                    <a href="<?= e(url('admin/jadwal')) ?>" class="button page-add" aria-label="Tambah jadwal">
                        <?= icon('plus', 'page-add-icon') ?>
                        <span class="page-add-label">Tambah jadwal</span>
                    </a>
                <?php endif; ?>
            </header>

            <?php if ($galatMuat !== ''): ?>
                <p role="alert" class="rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-xs md:text-sm text-red-300"><?= e($galatMuat) ?></p>
            <?php elseif ($jadwal === []): ?>
                <div class="panel px-6 py-12 text-center">
                    <h2 class="text-lg font-semibold">Belum ada jadwal kuliah.</h2>
                    <p class="mt-2 text-xs md:text-sm text-stone-400">Jadwal kelasmu akan muncul di sini setelah diisi.</p>
                </div>
            <?php else: ?>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">Agenda kuliah</h2>
                    <div role="group" aria-label="Tampilan jadwal" class="flex gap-2">
                        <button type="button" data-schedule-view="daily" aria-pressed="false" class="filter-tab">Harian</button>
                        <button type="button" data-schedule-view="weekly" aria-pressed="true" class="filter-tab">Mingguan</button>
                    </div>
                </div>
                <div id="day-picker" hidden role="group" aria-label="Pilih hari" class="mb-3 grid w-full grid-cols-7 gap-1.5 py-1 sm:gap-2">
                    <?php foreach ($days as $index => $day): ?>
                        <button type="button" data-day="<?= $index ?>" aria-label="<?= e($day) ?>"
                                aria-pressed="<?= $index === $todayIndex ? 'true' : 'false' ?>" class="filter-tab w-full min-w-0 px-1! text-xs md:text-sm sm:text-xs md:text-sm">
                            <?= e(substr($day, 0, 3)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div id="schedule-list" data-today="<?= $todayIndex ?>" class="grid items-start gap-5 xl:grid-cols-1">
                    <?php foreach ($days as $index => $day): ?>
                        <?php $kelasHariIni = $classesByDay[$index]; ?>
                        <section data-schedule-day="<?= $index ?>" aria-labelledby="day-<?= $index ?>" class="panel p-5 sm:p-6">
                            <header class="flex items-center justify-between gap-3 border-b border-stone-800 pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 id="day-<?= $index ?>" class="font-semibold"><?= e($day) ?></h3>
                                    <?php if ($index === $todayIndex): ?>
                                        <span class="tag bg-lime-300/10 text-lime-200">Hari ini</span>
                                    <?php endif; ?>
                                </div>
                                <span class="shrink-0 text-xs md:text-sm text-stone-400"><?= count($kelasHariIni) ?> kelas</span>
                            </header>
                            <?php if ($kelasHariIni === []): ?>
                                <p class="py-6 text-xs md:text-sm text-stone-400">Tidak ada jadwal kuliah pada hari ini.</p>
                            <?php else: ?>
                                <div class="divide-y divide-stone-800">
                                    <?php foreach ($kelasHariIni as $class): ?>
                                        <article class="flex min-w-0 flex-col items-stretch gap-4 py-5 lg:flex-row lg:items-center lg:justify-between">

                                            <!-- Waktu dan detail kuliah -->
                                            <div class="flex min-w-0 items-start gap-4">
                                                <div class="w-14 shrink-0 border-r border-stone-700 pr-3">
                                                    <p class="text-xs font-semibold md:text-sm">
                                                        <?= e($class['start']) ?>
                                                    </p>
                                                    <p class="mt-2 text-xs text-stone-400 md:text-sm">
                                                        <?= e($class['end']) ?>
                                                    </p>
                                                </div>

                                                <div class="min-w-0">
                                                    <h4 class="break-words text-xs font-medium leading-6 md:text-sm">
                                                        <?= e($class['course']) ?>
                                                    </h4>

                                                    <p class="mt-1 text-xs text-lime-200/80 md:text-sm">
                                                        <?= e($class['type']) ?>
                                                    </p>

                                                    <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-stone-400 md:text-sm">
                                                        <?= icon('location', 'mt-0.5 size-3 shrink-0') ?>
                                                        <span class="min-w-0 break-words"><?= e($class['room']) ?></span>
                                                    </p>

                                                    <p class="mt-1 break-words text-xs leading-5 text-stone-400 md:text-sm">
                                                        <?= e($class['lecturer']) ?>
                                                    </p>
                                                </div>
                                            </div>

                                            <?php if ($isAdmin): ?>
                                                <!-- Dua kolom di HP, sejajar di kanan pada desktop -->
                                                <div class="grid w-full grid-cols-2 gap-2 lg:flex lg:w-auto lg:shrink-0 lg:items-center">
                                                    <a
                                                        href="<?= e(url('admin/jadwal', ['id' => $class['id']])) ?>"
                                                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-stone-700 px-3 py-2 text-xs font-semibold text-stone-300 transition hover:bg-stone-800 lg:w-auto"
                                                    >
                                                        <?= icon('edit', 'size-4') ?>
                                                        Edit
                                                    </a>

                                                    <form
                                                        action="<?= e(url('admin/jadwal', ['id' => $class['id']])) ?>"
                                                        method="POST"
                                                        class="min-w-0"
                                                        onsubmit="return confirm('Hapus jadwal kuliah ini secara permanen dari agenda kelas?');"
                                                    >
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="aksi" value="hapus">

                                                        <button
                                                            type="submit"
                                                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-red-400/40 px-3 py-2 text-xs font-semibold text-red-400 transition hover:bg-red-400/10 lg:w-auto"
                                                        >
                                                            <?= icon('trash', 'size-4') ?>
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>

                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
                <p id="schedule-feedback" class="sr-only" role="status" aria-live="polite"></p>
            <?php endif; ?>
        </main>
    </div>
    <?php require __DIR__ . '/../components/dock.php'; ?>
</body>
</html>