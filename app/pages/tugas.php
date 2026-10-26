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
                <section class="mb-8">
                    <p class="eyebrow">
                        One thing at a time
                    </p>
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:mt-3 sm:text-4xl">
                        Tugas saya<span class="text-lime-300">.</span>
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-stone-400 sm:mt-3">
                        Lihat prioritasmu, mulai kerjakan, dan rayakan yang selesai.
                    </p>
                </section>
                <section aria-label="Filter tugas" class="space-y-3 sm:space-y-5">
                    <div class="flex flex-wrap gap-2" role="group" aria-label="Status tugas">
                        <button type="button" class="filter-tab" data-status-filter="all" aria-pressed="true">
                            Semua tugas<span data-count="all" class="text-xs opacity-70"><?= $taskCounts['all'] ?></span>
                        </button>
                        <button type="button" class="filter-tab" data-status-filter="todo" aria-pressed="false">
                            Belum dikerjakan<span data-count="todo" class="text-xs opacity-70"><?= $taskCounts['todo'] ?></span>
                        </button>
                        <button type="button" class="filter-tab" data-status-filter="doing" aria-pressed="false">
                            Sedang dikerjakan<span data-count="doing" class="text-xs opacity-70"><?= $taskCounts['doing'] ?></span>
                        </button>
                        <button type="button" class="filter-tab" data-status-filter="done" aria-pressed="false">
                            Selesai<span data-count="done" class="text-xs opacity-70"><?= $taskCounts['done'] ?></span>
                        </button>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:max-w-2xl">
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
                </section>
                <div class="mt-3 mb-3 flex flex-wrap items-center justify-between gap-3 text-xs text-stone-400 sm:mt-7 sm:mb-4">
                    <p id="task-result" role="status" aria-live="polite">
                        <?= $taskCounts['all'] ?> tugas ditampilkan
                    </p>
                    <p>
                        Tugas dibagikan oleh admin kelas
                    </p>
                </div>
                <section id="task-list" aria-label="Daftar tugas" class="grid items-start gap-5 sm:gap-4 xl:grid-cols-2">
                    <?php foreach ($tasks as $task): ?>
                        <?php require __DIR__ . '/../components/task-card.php'; ?>
                    <?php endforeach; ?>
                </section>
                <div id="tasks-empty" hidden class="panel px-6 py-14 text-center">
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
                <p id="task-feedback" role="status" aria-live="polite" class="mt-4 text-xs leading-6 text-lime-200">
                </p>
                <noscript>
                    <p class="mt-5 text-sm text-amber-200">
                        Aktifkan JavaScript untuk mencoba filter dan perubahan status. Detail tugas tetap dapat dibuka.
                    </p>
                </noscript>
                <?php require __DIR__ . '/../components/footer.php'; ?>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
