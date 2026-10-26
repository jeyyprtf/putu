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
                <div class="mb-3 flex items-center justify-between gap-2">
                    <p id="task-result" role="status" aria-live="polite" class="shrink-0 text-xs text-stone-400">
                        <?= $taskCounts['all'] ?> tugas
                    </p>
                    <div class="flex min-w-0 gap-2">
                        <button id="course-picker" type="button" class="filter-tab min-w-0 max-w-36 px-3!" aria-haspopup="dialog" aria-controls="course-drawer" aria-expanded="false" aria-label="Mata kuliah: Semua. Pilih mata kuliah.">
                            <span data-course-label class="truncate">Matkul</span>
                        </button>
                        <button id="status-filter" type="button" class="filter-tab shrink-0 px-3!" aria-label="Status: Semua. Ganti ke belum selesai.">
                            <?= icon('cycle', 'size-3.5') ?><span data-status-label>Semua</span>
                        </button>
                        <button id="task-sort" type="button" class="filter-tab min-w-11 shrink-0 px-3!" aria-label="Urutan: Deadline terdekat. Ganti ke deadline terlama.">
                            <?= icon('sort', 'size-3.5') ?><span data-sort-label class="hidden sm:inline">Terdekat</span>
                        </button>
                    </div>
                </div>
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
        <dialog id="course-drawer" class="course-drawer" aria-labelledby="course-drawer-title">
            <div class="mb-5 flex items-center justify-between gap-3">
                <h2 id="course-drawer-title" class="text-lg font-semibold">Mata kuliah<span class="text-lime-300">.</span></h2>
                <form method="dialog">
                    <button type="submit" class="icon-button" aria-label="Tutup pilihan mata kuliah" autofocus><?= icon('close') ?></button>
                </form>
            </div>
            <div role="group" aria-label="Filter mata kuliah" class="flex flex-col gap-2">
                <button type="button" data-course-filter="" aria-pressed="true" class="filter-tab justify-start! rounded-2xl! whitespace-normal! text-left">Semua mata kuliah</button>
                <?php foreach ($courseNames as $course): ?>
                    <button type="button" data-course-filter="<?= e($course) ?>" aria-pressed="false" class="filter-tab justify-start! rounded-2xl! whitespace-normal! text-left">
                        <?= e($course) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </dialog>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
