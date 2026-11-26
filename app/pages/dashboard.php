<?php
$page = 'dashboard';
$title = 'Ringkasan';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../jadwal.php';
require_once __DIR__ . '/../tugas.php';

// 1. Wajib login & sudah tergabung di kelas
$user = require_login();
$idUser = (int) $user['id_user'];
$kelasId = (int) $user['id_kelas'];

// 2. Indeks hari ini (0 = Senin ... 6 = Minggu)
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$todayIndex = (int) (new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta')))->format('N') - 1;

// 3. Muat jadwal hari ini dari database
$todayClasses = [];
try {
    $jadwalSemua = jadwal_daftar($kelasId);
    $classesByDay = jadwal_per_hari($jadwalSemua);
    $todayClasses = $classesByDay[$todayIndex] ?? [];
} catch (Throwable $e) {
    error_log('[dashboard:jadwal] ' . $e->getMessage());
}

// 4. Muat daftar tugas & hitung progres dari database
$tasks = [];
try {
    $tasks = tugas_daftar($idUser, $kelasId);
} catch (Throwable $e) {
    error_log('[dashboard:tugas] ' . $e->getMessage());
}

$taskCounts = tugas_hitung($tasks);
$percent = $taskCounts['all'] > 0
    ? (int) round(($taskCounts['done'] / $taskCounts['all']) * 100)
    : 0;

// 5. Urutkan tugas berdasarkan deadline terdekat & ambil 5 tugas aktif teratas
$deadlineTasks = $tasks;
usort($deadlineTasks, static fn(array $a, array $b): int => strcmp((string) ($a['deadline'] ?? ''), (string) ($b['deadline'] ?? '')));

$activeTasks = array_values(array_filter(
    $deadlineTasks,
    static fn(array $t): bool => ($t['status'] ?? '') !== 'done'
));
$nearestTaskIds = array_column(array_slice($activeTasks, 0, 5), 'id');

// 6. Pastikan data nama profil mengikuti user yang sedang login
$profile['name'] = $user['nama'] ?? ($profile['name'] ?? 'Mahasiswa');
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
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 pb-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10">
            <!-- Sapaan -->
            <section class="mb-5">
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    Halo, <span class="break-words"><?= e($profile['name']) ?></span><span class="text-lime-300">.</span>
                </h1>
            </section>

            <!-- Progres tugas -->
            <section aria-label="Progres tugas" class="panel mb-4 px-4 py-4 sm:px-5">
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-xs sm:text-sm">
                    <h2 class="font-medium text-stone-300">Progres tugas</h2>

                    <p class="text-stone-400">
                        <span data-count="done"><?= $taskCounts['done'] ?></span>/<?= $taskCounts['all'] ?> selesai
                        <span data-progress-percent class="ml-2 font-semibold text-lime-300">
                            <?= $percent ?>%
                        </span>
                    </p>
                </div>

                <progress
                    data-progress
                    value="<?= $taskCounts['done'] ?>"
                    max="<?= max(1, $taskCounts['all']) ?>"
                    aria-label="Progres tugas selesai"
                    class="mt-3 block h-1.5 w-full overflow-hidden rounded-full accent-lime-400"
                ><?= $percent ?>%</progress>
            </section>

            <!-- Ringkasan -->
            <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4">
                <section aria-label="Ringkasan tugas" class="panel flex flex-col p-4 sm:p-5">
                    <h2 class="text-xs font-medium text-stone-300 sm:text-sm">Total tugas</h2>

                    <p class="mt-3 flex items-baseline gap-1.5">
                        <span data-count="active" class="text-4xl font-semibold tracking-tight sm:text-5xl">
                            <?= $taskCounts['active'] ?>
                        </span>
                        <span class="text-xs text-stone-400 sm:text-sm">aktif</span>
                    </p>

                    <div class="mt-4 grid grid-cols-2 divide-x divide-stone-800 border-t border-stone-800 pt-3 text-center">
                        <div title="Deadline dalam 2 hari atau sudah lewat" class="px-1">
                            <p data-count="priority" class="text-xl font-semibold text-amber-300 sm:text-2xl">
                                <?= $taskCounts['priority'] ?>
                            </p>
                            <p class="mt-1 text-[10px] text-stone-400 sm:text-xs">Mendesak</p>
                        </div>

                        <div title="Deadline lebih dari 2 hari lagi" class="px-1">
                            <p data-count="calm" class="text-xl font-semibold text-lime-300 sm:text-2xl">
                                <?= $taskCounts['calm'] ?>
                            </p>
                            <p class="mt-1 text-[10px] text-stone-400 sm:text-xs">Santai</p>
                        </div>
                    </div>
                </section>

                <section aria-label="Jumlah jadwal hari ini" class="panel flex flex-col p-4 sm:p-5">
                    <h2 class="text-xs font-medium text-stone-300 sm:text-sm">Jadwal hari ini</h2>

                    <p data-today-class-count class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                        <?= count($todayClasses) ?>
                    </p>

                    <p class="mt-2 text-xs text-stone-400 sm:text-sm">kelas terjadwal</p>

                    <div class="mt-auto border-t border-stone-800 pt-3">
                        <a href="<?= e(url('jadwal')) ?>" class="inline-flex min-h-9 items-center gap-1.5 text-xs font-semibold text-lime-300 hover:text-lime-200 sm:text-sm">
                            Lihat jadwal
                            <?= icon('arrow', 'size-3') ?>
                        </a>
                    </div>
                </section>
            </div>

            <!-- Agenda dan deadline -->
            <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-2">
                <section aria-labelledby="today-agenda-title" class="panel p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 id="today-agenda-title" class="text-base font-semibold">
                                Agenda hari ini
                            </h2>
                            <p class="mt-1 text-xs text-stone-400 sm:text-sm">
                                <?= e($days[$todayIndex]) ?>, <?= e(date_id('today')) ?>
                            </p>
                        </div>

                        <a href="<?= e(url('jadwal')) ?>" class="inline-flex min-h-11 shrink-0 items-center gap-1 text-xs font-semibold text-lime-300 hover:text-lime-200">
                            Jadwal <?= icon('arrow', 'size-3') ?>
                        </a>
                    </div>

                    <div class="mt-3 divide-y divide-stone-800">
                        <?php foreach ($todayClasses as $class): ?>
                            <div data-today-class class="flex items-start gap-3 py-3">
                                <div class="w-15 shrink-0 border-r border-stone-800 pr-3">
                                    <p class="text-sm font-semibold"><?= e($class['start']) ?></p>
                                    <p class="mt-1 text-xs text-stone-400"><?= e($class['end']) ?></p>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="break-words text-sm font-medium leading-5">
                                        <?= e($class['course']) ?>
                                    </h3>
                                    <p class="mt-1 break-words text-xs leading-5 text-stone-400">
                                        <?= e($class['type']) ?> · <?= e($class['room']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!$todayClasses): ?>
                            <p class="py-3 text-sm text-stone-400">Tidak ada kuliah hari ini.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section aria-labelledby="nearest-deadlines-title" class="panel p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="nearest-deadlines-title" class="text-base font-semibold">
                            Deadline terdekat
                        </h2>

                        <a href="<?= e(url('tugas')) ?>" class="inline-flex min-h-11 shrink-0 items-center gap-1 text-xs font-semibold text-lime-300 hover:text-lime-200">
                            Semua <?= icon('arrow', 'size-3') ?>
                        </a>
                    </div>

                    <div class="divide-y divide-stone-800">
                        <?php foreach ($deadlineTasks as $task): ?>
                            <?php $deadlineLabel = deadline_label($task); ?>

                            <a
                                href="<?= e(url('tugas', ['task' => $task['id']])) ?>"
                                data-priority-task="<?= e($task['id']) ?>"
                                data-deadline="<?= e($task['deadline']) ?>"
                                <?= !in_array($task['id'], $nearestTaskIds, true) ? 'hidden' : '' ?>
                                class="group flex items-start gap-3 py-3"
                            >
                                <div class="min-w-0 flex-1">
                                    <h3 class="break-words text-sm font-medium leading-5 group-hover:text-lime-200">
                                        <?= e($task['title']) ?>
                                    </h3>
                                    <p class="mt-1 break-words text-xs leading-5 text-stone-400">
                                        <?= e($task['course']) ?>
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <span
                                        data-deadline-badge
                                        class="tag task-deadline"
                                        data-urgent="<?= in_array($deadlineLabel, ['Hari ini', 'Besok', '2 hari lagi']) ? 'true' : 'false' ?>"
                                        data-late="<?= $deadlineLabel === 'Terlambat' ? 'true' : 'false' ?>"
                                        data-done="<?= $task['status'] === 'done' ? 'true' : 'false' ?>"
                                    ><?= e($deadlineLabel) ?></span>

                                    <time datetime="<?= e($task['deadline']) ?>" class="mt-1 block text-[11px] text-stone-400">
                                        <?= e(date_id($task['deadline'])) ?>
                                    </time>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <p
                        id="priorities-empty"
                        <?= $taskCounts['active'] !== 0 ? 'hidden' : '' ?>
                        class="py-3 text-sm text-stone-400"
                    >
                        Semua tugas sudah selesai.
                    </p>
                </section>
            </div>
        </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>