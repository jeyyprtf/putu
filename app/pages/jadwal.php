<?php if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=jadwal'); exit; } ?>
<section class="mb-8 flex flex-wrap items-end justify-between gap-5"><div><p class="eyebrow">Make room for learning</p><h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Jadwal kuliah<span class="text-lime-300">.</span></h1><p class="mt-3 text-sm leading-6 text-stone-400">Tahu kapan, di mana, dan bersama siapa kamu belajar.</p></div><span class="tag border border-stone-800 bg-stone-900 text-stone-300">Semester 3 · SIB 2B</span></section>
<section aria-label="Ringkasan jadwal" class="mb-8 grid grid-cols-3 gap-3 sm:gap-5">
    <?php foreach ([[count(array_unique(array_column($schedules, 'course'))), 'Mata kuliah'], [count($schedules), 'Kelas per minggu'], [count(array_unique(array_column($schedules, 'day'))), 'Hari kuliah']] as [$value, $label]): ?>
        <div class="panel p-4 sm:p-6"><p class="text-3xl font-semibold tracking-tight"><?= $value ?></p><p class="mt-2 text-xs leading-5 text-stone-400"><?= e($label) ?></p></div>
    <?php endforeach; ?>
</section>
<div class="mb-5 flex flex-wrap items-center justify-between gap-4"><h2 class="text-lg font-semibold">Agenda mingguan</h2><div role="group" aria-label="Tampilan jadwal" class="flex gap-2"><button type="button" data-schedule-view="daily" aria-pressed="false" class="filter-tab">Harian</button><button type="button" data-schedule-view="weekly" aria-pressed="true" class="filter-tab">Mingguan</button></div></div>
<div id="day-picker" hidden class="mb-5 flex gap-2 overflow-x-auto py-1" role="group" aria-label="Pilih hari">
    <?php foreach ($days as $index => $day): ?><button type="button" data-day="<?= $index ?>" aria-label="<?= e($day) ?>" aria-pressed="<?= $index === $todayIndex ? 'true' : 'false' ?>" class="filter-tab min-w-11 flex-1 shrink-0 px-2!"><?= e(substr($day, 0, 3)) ?></button><?php endforeach; ?>
</div>
<div class="grid items-start gap-5 xl:grid-cols-2" id="schedule-list" data-today="<?= $todayIndex ?>">
    <?php foreach ($days as $index => $day): $classes = array_filter($schedules, fn($class) => $class['day'] === $index); ?>
        <section class="panel p-5 sm:p-6" data-schedule-day="<?= $index ?>" aria-labelledby="day-<?= $index ?>">
            <div class="flex items-center justify-between gap-3 border-b border-stone-800 pb-4"><div class="flex flex-wrap items-center gap-2"><h3 id="day-<?= $index ?>" class="font-semibold"><?= e($day) ?></h3><?php if ($index === $todayIndex): ?><span class="tag bg-lime-300/10 text-lime-200">Hari ini</span><?php endif; ?></div><span class="shrink-0 text-xs text-stone-400"><?= count($classes) ?> kelas</span></div>
            <div class="divide-y divide-stone-800">
                <?php foreach ($classes as $class): ?>
                    <article class="flex gap-4 py-5"><div class="w-14 shrink-0 border-r border-stone-700 pr-3"><p class="text-sm font-semibold"><?= e($class['start']) ?></p><p class="mt-2 text-xs text-stone-400"><?= e($class['end']) ?></p></div><div class="min-w-0"><h4 class="text-sm font-medium leading-6"><?= e($class['course']) ?></h4><p class="mt-1 text-xs text-lime-200/80"><?= e($class['type']) ?></p><p class="mt-4 flex items-start gap-2 text-xs leading-5 text-stone-400"><?= icon('location', 'mt-0.5 size-3 shrink-0') ?><?= e($class['room']) ?></p><p class="mt-1 text-xs leading-5 text-stone-400"><?= e($class['lecturer']) ?></p></div></article>
                <?php endforeach; ?>
                <?php if (!$classes): ?><p class="py-7 text-sm leading-6 text-stone-400">Tidak ada kuliah. Ruang untuk istirahat atau mengejar tugas.</p><?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
<p id="schedule-feedback" class="sr-only" role="status" aria-live="polite"></p>
