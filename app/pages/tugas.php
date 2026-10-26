<?php
$page = 'tugas';
$title = 'Tugas saya';
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
                        Tugas saya<span class="text-lime-300">.</span>
                    </h1>
                </section>
                <div class="mb-1 flex items-center justify-between gap-3">
                    <p id="task-result" role="status" aria-live="polite" class="min-w-0 text-xs text-stone-400">
                        <?= $taskCounts['all'] ?> tugas
                    </p>
                    <div>
                        <label for="status-filter" class="sr-only">Filter status tugas</label>
                        <select id="status-filter" class="min-h-11 max-w-full rounded-full border border-stone-800 bg-stone-900 px-4 text-xs text-stone-200">
                            <option value="all">Semua status</option>
                            <option value="todo">Belum dikerjakan</option>
                            <option value="doing">Sedang dikerjakan</option>
                            <option value="done">Selesai</option>
                        </select>
                    </div>
                </div>
                <details data-task-filters class="mb-3">
                    <summary class="ml-auto flex min-h-11 w-fit items-center gap-2 text-xs text-stone-400 hover:text-lime-200">
                        Mata kuliah & urutan<span class="details-arrow transition-transform"><?= icon('chevron', 'size-3') ?></span>
                    </summary>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:max-w-2xl">
                        <div>
                            <label for="course-filter" class="mb-2 block text-xs text-stone-400">
                                Mata kuliah
                            </label>
                            <select id="course-filter" class="field">
                                <option value="">
                                    Semua mata kuliah
                                </option>
                                <?php foreach ($courseNames as $course): ?>
                                    <option value="<?= e($course) ?>">
                                        <?= e($course) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="task-sort" class="mb-2 block text-xs text-stone-400">
                                Urutkan
                            </label>
                            <select id="task-sort" class="field">
                                <option value="deadline">
                                    Deadline terdekat
                                </option>
                                <option value="latest">
                                    Deadline terjauh
                                </option>
                                <option value="title">
                                    Judul A–Z
                                </option>
                            </select>
                        </div>
                    </div>
                </details>
                <section id="task-list" aria-label="Daftar tugas" class="grid items-start gap-3 sm:gap-4 xl:grid-cols-2">
                    <?php foreach ($tasks as $task): ?>
                        <?php require __DIR__ . '/../components/task-card.php'; ?>
                    <?php endforeach; ?>
                </section>
                <div id="tasks-empty" hidden class="panel px-4 py-8 text-center sm:px-6 sm:py-14">
                    <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('tasks') ?></span>
                    <h2 class="mt-4 text-lg font-semibold">
                        Belum ada tugas di sini.
                    </h2>
                    <p class="mt-2 text-sm text-stone-400">
                        Coba status atau mata kuliah lainnya.
                    </p>
                    <button type="button" data-reset-filters class="button button-secondary mt-5">
                        Reset filter
                    </button>
                </div>
                <p id="task-feedback" role="status" aria-live="polite" class="mt-3 text-xs leading-6 text-lime-200">
                </p>
                <noscript>
                    <p class="mt-5 text-sm text-amber-200">
                        Aktifkan JavaScript untuk mencoba filter dan perubahan status. Detail tugas tetap dapat dibuka.
                    </p>
                </noscript>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
