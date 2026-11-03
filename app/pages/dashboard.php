<?php
$page = 'dashboard';
$title = 'Ringkasan';
require_once __DIR__ . '/../bootstrap.php';
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
                <section class="mb-4">
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:mt-0 sm:text-4xl">
                        Halo, <span class="break-words"><?= e($profile['name']) ?></span><span class="text-lime-300">.</span>
                    </h1>
                </section>
                <section aria-label="Progres tugas" class="panel mb-3 px-4 py-3">
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <h2 class="font-medium text-stone-300">Progres tugas</h2>
                        <p class="text-stone-400">
                            <span data-count="done"><?= $taskCounts['done'] ?></span>/<?= $taskCounts['all'] ?> selesai
                            <span data-progress-percent class="ml-2 font-semibold text-lime-300"><?= $percent ?>%</span>
                        </p>
                    </div>
                    <progress data-progress value="<?= $taskCounts['done'] ?>" max="<?= max(1, $taskCounts['all']) ?>" aria-label="Progres tugas selesai" class="mt-2 block h-1.5 w-full overflow-hidden rounded-full accent-lime-400"><?= $percent ?>%</progress>
                </section>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <section aria-label="Ringkasan tugas" class="panel p-4 sm:p-5">
                        <h2 class="text-xs font-medium text-stone-300">Total tugas</h2>
                        <p class="mt-3">
                            <span data-count="active" class="text-5xl font-semibold tracking-tight"><?= $taskCounts['active'] ?></span>
                            <span class="ml-1 text-xs text-stone-400">aktif</span>
                        </p>
                        <div class="mt-3 grid grid-cols-2 gap-2 divide-x divide-stone-800 border-t border-stone-800 pt-3 text-center">
                            <div title="Deadline dalam 2 hari atau sudah lewat">
                                <p data-count="priority" class="text-2xl font-semibold text-amber-300"><?= $taskCounts['priority'] ?></p>
                                <p class="mt-1 text-[10px] text-stone-400 sm:text-xs">Mendesak</p>
                            </div>
                            <div title="Deadline lebih dari 2 hari lagi">
                                <p data-count="calm" class="text-2xl font-semibold text-lime-300"><?= $taskCounts['calm'] ?></p>
                                <p class="mt-1 text-[10px] text-stone-400 sm:text-xs">Santai</p>
                            </div>
                        </div>
                    </section>
                    <section aria-label="Jumlah jadwal hari ini" class="panel p-4 sm:p-5">
                        <h2 class="text-xs font-medium text-stone-300">Jadwal hari ini</h2>
                        <p class="mt-3 text-5xl font-semibold tracking-tight" data-today-class-count><?= count($todayClasses) ?></p>
                        <p class="mt-2 text-xs text-stone-400">kelas terjadwal</p>
                    </section>
                </div>
                <section aria-labelledby="today-agenda-title" class="panel mb-3 p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 id="today-agenda-title" class="text-base font-semibold">Agenda hari ini</h2>
                            <p class="mt-1 text-xs text-stone-400"><?= e($days[$todayIndex]) ?>, <?= e(date_id('today')) ?></p>
                        </div>
                        <a href="<?= e(url('jadwal')) ?>" class="flex min-h-11 shrink-0 items-center gap-1 text-xs font-semibold text-lime-300">
                            Jadwal <?= icon('arrow', 'size-3') ?>
                        </a>
                    </div>
                    <div class="mt-2 divide-y divide-stone-800">
                        <?php foreach ($todayClasses as $class): ?>
                            <div data-today-class class="flex gap-3 py-3">
                                <div class="shrink-0 border-r border-stone-800 pr-3">
                                    <p class="text-sm font-semibold"><?= e($class['start']) ?></p>
                                    <p class="mt-1 text-xs text-stone-400"><?= e($class['end']) ?></p>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-medium leading-5"><?= e($class['course']) ?></h3>
                                    <p class="mt-1 text-xs leading-5 text-stone-400"><?= e($class['type']) ?> · <?= e($class['room']) ?></p>
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
                        <h2 id="nearest-deadlines-title" class="text-base font-semibold">Deadline terdekat</h2>
                        <a href="<?= e(url('tugas')) ?>" class="flex min-h-11 shrink-0 items-center gap-1 text-xs font-semibold text-lime-300">
                            Semua <?= icon('arrow', 'size-3') ?>
                        </a>
                    </div>
                    <div class="divide-y divide-stone-800">
                        <?php foreach ($deadlineTasks as $task): ?>
                            <?php $deadlineLabel = deadline_label($task); ?>
                            <a href="<?= e(url('tugas', ['task' => $task['id']])) ?>" data-priority-task="<?= e($task['id']) ?>" data-deadline="<?= e($task['deadline']) ?>" <?= !in_array($task['id'], $nearestTaskIds, true) ? 'hidden' : '' ?> class="group flex items-start gap-3 py-3">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-medium leading-5 group-hover:text-lime-200"><?= e($task['title']) ?></h3>
                                    <p class="mt-1 text-xs leading-5 text-stone-400"><?= e($task['course']) ?></p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span data-deadline-badge class="tag task-deadline" data-urgent="<?= in_array($deadlineLabel, ['Hari ini', 'Besok', '2 hari lagi']) ? 'true' : 'false' ?>" data-late="<?= $deadlineLabel === 'Terlambat' ? 'true' : 'false' ?>" data-done="<?= $task['status'] === 'done' ? 'true' : 'false' ?>"><?= e($deadlineLabel) ?></span>
                                    <time datetime="<?= e($task['deadline']) ?>" class="mt-1 block text-[11px] text-stone-400"><?= e(date_id($task['deadline'])) ?></time>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <p id="priorities-empty" <?= $taskCounts['active'] !== 0 ? 'hidden' : '' ?> class="py-3 text-sm text-stone-400">Semua tugas sudah selesai.</p>
                </section>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
