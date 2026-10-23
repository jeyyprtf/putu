<?php if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=tugas'); exit; } ?>
<section class="mb-8"><p class="eyebrow">One thing at a time</p><h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Tugas saya<span class="text-lime-300">.</span></h1><p class="mt-3 text-sm leading-6 text-stone-400">Lihat prioritasmu, mulai kerjakan, dan rayakan yang selesai.</p></section>
<section aria-label="Filter tugas" class="space-y-5">
    <div class="flex flex-wrap gap-2" role="group" aria-label="Status tugas">
        <?php foreach (['all' => 'Semua tugas', 'todo' => 'Belum dikerjakan', 'doing' => 'Sedang dikerjakan', 'done' => 'Selesai'] as $status => $label): ?>
            <button type="button" class="filter-tab" data-status-filter="<?= e($status) ?>" aria-pressed="<?= $status === 'all' ? 'true' : 'false' ?>"><?= e($label) ?><span data-count="<?= e($status) ?>" class="text-xs opacity-70"><?= $status === 'all' ? count($tasks) : count(array_filter($tasks, fn($task) => $task['status'] === $status)) ?></span></button>
        <?php endforeach; ?>
    </div>
    <div class="grid gap-3 sm:grid-cols-2 lg:max-w-2xl">
        <div><label for="course-filter" class="mb-2 block text-xs text-stone-400">Mata kuliah</label><select id="course-filter" class="field"><option value="">Semua mata kuliah</option><?php foreach (array_unique(array_column($tasks, 'course')) as $course): ?><option value="<?= e($course) ?>"><?= e($course) ?></option><?php endforeach; ?></select></div>
        <div><label for="task-sort" class="mb-2 block text-xs text-stone-400">Urutkan</label><select id="task-sort" class="field"><option value="deadline">Deadline terdekat</option><option value="latest">Deadline terjauh</option><option value="title">Judul A–Z</option></select></div>
    </div>
</section>
<div class="mt-7 mb-4 flex flex-wrap items-center justify-between gap-3 text-xs text-stone-400"><p id="task-result" role="status" aria-live="polite"><?= count($tasks) ?> tugas ditampilkan</p><p>Tugas dibagikan oleh admin kelas</p></div>
<section id="task-list" aria-label="Daftar tugas" class="grid items-start gap-4 xl:grid-cols-2">
    <?php foreach ($tasks as $task): require __DIR__ . '/../components/task-card.php'; endforeach; ?>
</section>
<div id="tasks-empty" hidden class="panel px-6 py-14 text-center"><span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('tasks') ?></span><h2 class="mt-4 text-lg font-semibold">Belum ada tugas di sini.</h2><p class="mt-2 text-sm text-stone-400">Coba status atau mata kuliah lainnya.</p><button type="button" data-reset-filters class="button button-secondary mt-5">Reset filter</button></div>
<p id="task-feedback" role="status" aria-live="polite" class="mt-4 text-xs leading-6 text-lime-200"></p>
<noscript><p class="mt-5 text-sm text-amber-200">Aktifkan JavaScript untuk mencoba filter dan perubahan status. Detail tugas tetap dapat dibuka.</p></noscript>
