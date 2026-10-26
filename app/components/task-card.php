<?php $deadlineLabel = deadline_label($task); ?>
<article class="panel p-4 sm:p-6" data-task-card data-task-id="<?= e($task['id']) ?>" data-course="<?= e($task['course']) ?>" data-title="<?= e($task['title']) ?>" data-deadline="<?= e($task['deadline']) ?>" data-status="<?= e($task['status']) ?>">
    <div class="flex items-start justify-between gap-3">
        <p class="min-w-0 text-xs leading-5 text-stone-400">
            <span class="font-medium text-stone-200"><?= e($task['course']) ?></span>
            <span aria-hidden="true"> · </span><?= e($task['type']) ?>
        </p>
        <span class="tag task-deadline shrink-0" data-deadline-badge data-urgent="<?= in_array($deadlineLabel, ['Hari ini', 'Besok', '2 hari lagi']) ? 'true' : 'false' ?>" data-late="<?= $deadlineLabel === 'Terlambat' ? 'true' : 'false' ?>" data-done="<?= $task['status'] === 'done' ? 'true' : 'false' ?>"><?= e($deadlineLabel) ?></span>
    </div>
    <details class="mt-2" <?= ($_GET['task'] ?? '') === $task['id'] ? 'open' : '' ?>>
        <summary class="flex min-h-11 items-start justify-between gap-4">
            <h2 class="text-lg font-semibold leading-7 tracking-tight">
                <?= e($task['title']) ?>
            </h2>
            <span class="details-arrow mt-1 shrink-0 text-stone-400 transition-transform"><?= icon('chevron', 'size-4') ?></span>
        </summary>
        <p class="mt-2 text-sm leading-6 text-stone-400">
            <?= e($task['description']) ?>
        </p>
        <p class="mt-2 text-xs leading-5 text-stone-400">
            Menandai selesai mencatat progres pribadi. Pengumpulan tetap melalui LMS.
        </p>
    </details>
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-stone-800 pt-3">
        <p class="flex items-center gap-2 text-xs text-stone-400">
            <?= icon('calendar', 'size-4') ?><?= e(date_id($task['deadline'])) ?>
        </p>
        <div class="flex items-center gap-2">
            <label for="status-<?= e($task['id']) ?>" class="sr-only">
                Status tugas <?= e($task['title']) ?>
            </label>
            <select id="status-<?= e($task['id']) ?>" data-task-status class="min-h-11 max-w-full rounded-xl border border-stone-700 bg-stone-950 px-3 py-2 text-xs text-stone-200">
                <option value="todo" <?= $task['status'] === 'todo' ? 'selected' : '' ?>>
                    Belum dikerjakan
                </option>
                <option value="doing" <?= $task['status'] === 'doing' ? 'selected' : '' ?>>
                    Sedang dikerjakan
                </option>
                <option value="done" <?= $task['status'] === 'done' ? 'selected' : '' ?>>
                    Selesai
                </option>
            </select>
        </div>
    </div>
</article>
