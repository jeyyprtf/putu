<?php
$page = 'jadwal';
$title = 'Jadwal kuliah';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../jadwal.php';

$user = require_login();

// 0 = Senin ... 6 = Minggu (sama dengan indeks yang dipakai app.js).
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$todayIndex = (int) (new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta')))->format('N') - 1;

$jadwal = [];
$galatMuat = '';
try {
    $jadwal = jadwal_daftar((int) $user['id_kelas']); // id_kelas dari sesi, bukan dari URL
} catch (Throwable $e) {
    error_log('[jadwal] ' . $e->getMessage());
    $galatMuat = 'Jadwal belum bisa dimuat. Coba muat ulang halaman.';
}
$classesByDay = jadwal_per_hari($jadwal);
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
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 lg:px-10 lg:py-10">
                <section class="mb-8 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl">
                            Jadwal kuliah<span class="text-lime-300">.</span>
                        </h1>
                    </div>
                    <span class="tag border border-stone-800 bg-stone-900 text-stone-300"><?= e($user['nama_kelas'] ?? 'Kelasmu') ?></span>
                </section>
                <?php if ($galatMuat !== ''): ?>
                    <p role="alert" class="mb-4 rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-sm text-red-300"><?= e($galatMuat) ?></p>
                <?php elseif ($jadwal === []): ?>
                    <p role="status" class="mb-4 rounded-2xl border border-stone-800 bg-stone-900 px-4 py-3 text-sm text-stone-300">
                        Belum ada jadwal kuliah untuk kelasmu. Jadwal akan muncul di sini setelah diisi.
                    </p>
                <?php endif; ?>
                <div class="mb-3 flex  items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold">
                        Agenda mingguan
                    </h2>
                    <div role="group" aria-label="Tampilan jadwal" class="flex gap-2">
                        <button type="button" data-schedule-view="daily" aria-pressed="false" class="filter-tab">
                            Harian
                        </button>
                        <button type="button" data-schedule-view="weekly" aria-pressed="true" class="filter-tab">
                            Mingguan
                        </button>
                    </div>
                </div>
                    <div id="day-picker" hidden class="mb-3 grid w-full grid-cols-7 gap-1.5 py-1 sm:gap-2" role="group" aria-label="Pilih hari">
                    <?php foreach ($days as $index => $day): ?>
                        <button type="button" data-day="<?= $index ?>" aria-label="<?= e($day) ?>" aria-pressed="<?= $index === $todayIndex ? 'true' : 'false' ?>" class="filter-tab w-full min-w-0 px-1! text-xs sm:text-sm">
                            <?= e(substr($day, 0, 3)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="grid items-start gap-5 xl:grid-cols-2" id="schedule-list" data-today="<?= $todayIndex ?>">
                    <?php foreach ($days as $index => $day): ?>
                        <section class="panel p-5 sm:p-6" data-schedule-day="<?= $index ?>" aria-labelledby="day-<?= $index ?>">
                            <div class="flex items-center justify-between gap-3 border-b border-stone-800 pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 id="day-<?= $index ?>" class="font-semibold">
                                        <?= e($day) ?>
                                    </h3>
                                    <?php if ($index === $todayIndex): ?>
                                        <span class="tag bg-lime-300/10 text-lime-200">Hari ini</span>
                                    <?php endif; ?>
                                </div>
                                <span class="shrink-0 text-xs text-stone-400"><?= count($classesByDay[$index]) ?> kelas</span>
                            </div>
                            <div class="divide-y divide-stone-800">
                                <?php foreach ($classesByDay[$index] as $class): ?>
                                    <article class="flex gap-4 py-5">
                                        <div class="w-14 shrink-0 border-r border-stone-700 pr-3">
                                            <p class="text-sm font-semibold">
                                                <?= e($class['start']) ?>
                                            </p>
                                            <p class="mt-2 text-xs text-stone-400">
                                                <?= e($class['end']) ?>
                                            </p>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-medium leading-6">
                                                <?= e($class['course']) ?>
                                            </h4>
                                            <p class="mt-1 text-xs text-lime-200/80">
                                                <?= e($class['type']) ?>
                                            </p>
                                            <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-stone-400">
                                                <?= icon('location', 'mt-0.5 size-3 shrink-0') ?><?= e($class['room']) ?>
                                            </p>
                                            <p class="mt-1 text-xs leading-5 text-stone-400">
                                                <?= e($class['lecturer']) ?>
                                            </p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                                <?php if (!$classesByDay[$index]): ?>
                                    <p class="py-7 text-sm leading-6 text-stone-400">
                                        Tidak ada kuliah. Ruang untuk istirahat atau mengejar tugas.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
                <p id="schedule-feedback" class="sr-only" role="status" aria-live="polite">
                </p>
          
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>