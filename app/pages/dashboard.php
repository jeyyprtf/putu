<?php if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=dashboard'); exit; }
$active = count(array_filter($tasks, fn($task) => $task['status'] !== 'done'));
$done = count($tasks) - $active;
$doing = count(array_filter($tasks, fn($task) => $task['status'] === 'doing'));
$priority = count(array_filter($tasks, fn($task) => $task['status'] !== 'done' && $task['deadline'] <= $today->modify('+2 days')->format('Y-m-d')));
$percent = (int) round($done / count($tasks) * 100);
?>
<section class="mb-8 flex flex-wrap items-end justify-between gap-5">
    <div><p class="eyebrow">Your day, at a glance</p><h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Halo, Juan<span class="text-lime-300">.</span></h1><p class="mt-3 text-sm leading-6 text-stone-400">Yuk, buat hari ini sedikit lebih teratur.</p></div>
    <span class="tag border border-stone-800 bg-stone-900 text-stone-300"><?= icon('check', 'size-3') ?>Satu langkah setiap hari</span>
</section>
<section aria-label="Ringkasan tugas" class="mb-8 grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
    <?php foreach ([['active', 'Tugas aktif', $active, 'tasks', 'Sedikit demi sedikit, pasti bisa.'], ['priority', 'Perlu diprioritaskan', $priority, 'clock', 'Deadline dalam 2 hari atau lewat.'], ['doing', 'Sedang dikerjakan', $doing, 'arrow', 'Lanjutkan langkah baikmu.'], ['done', 'Sudah selesai', $done, 'check', 'Satu langkah lebih dekat.']] as [$key, $label, $value, $glyph, $caption]): ?>
        <div class="panel p-4 sm:p-5"><div class="flex items-start justify-between gap-2"><p class="text-xs leading-5 text-stone-400"><?= e($label) ?></p><span class="shrink-0 text-lime-300/70"><?= icon($glyph, 'size-4') ?></span></div><p class="mt-5 text-4xl font-semibold tracking-tight" data-count="<?= e($key) ?>"><?= sprintf('%02d', $value) ?></p><p class="mt-3 text-[11px] leading-5 text-stone-400"><?= e($caption) ?></p></div>
    <?php endforeach; ?>
</section>
<div class="grid items-start gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="panel p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-semibold tracking-tight">Mulai dari yang terdekat</h2><p class="mt-1 text-xs leading-5 text-stone-400">Prioritas kecil, kemajuan besar.</p></div><a href="<?= e(url('tugas')) ?>" class="flex min-h-11 shrink-0 items-center gap-1 text-xs font-semibold text-lime-300">Lihat semua <?= icon('arrow', 'size-3') ?></a></div>
            <div class="mt-4 divide-y divide-stone-800">
                <?php foreach ($tasks as $task): ?>
                    <a href="<?= e(url('tugas', ['task' => $task['id']])) ?>" data-priority-task="<?= e($task['id']) ?>" <?= $task['status'] === 'done' ? 'hidden' : '' ?> class="group flex items-center gap-3 py-5">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-lime-300/10 text-xs font-semibold text-lime-200"><?= e($task['code']) ?></span>
                        <div class="min-w-0 flex-1"><h3 class="text-sm font-medium leading-6 group-hover:text-lime-200"><?= e($task['title']) ?></h3><p class="mt-1 text-xs text-stone-400"><?= e($task['course']) ?></p></div>
                        <div class="hidden shrink-0 text-right sm:block"><span class="tag bg-stone-800 text-stone-300"><?= e(deadline_label($task)) ?></span><p class="mt-2 text-[10px] text-stone-400"><?= e(date_id($task['deadline'])) ?></p></div>
                        <span class="shrink-0 text-stone-500"><?= icon('arrow', 'size-4') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <p id="priorities-empty" hidden class="py-8 text-center text-sm text-stone-400">Semua tugas sudah selesai. Waktunya mengambil jeda.</p>
        </section>
        <section class="panel p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-semibold tracking-tight">Kabar dari kelas</h2><p class="mt-1 text-xs leading-5 text-stone-400">Tetap tahu, tanpa tenggelam di percakapan.</p></div><a href="<?= e(url('pengumuman')) ?>" class="flex min-h-11 shrink-0 items-center gap-1 text-xs font-semibold text-lime-300">Lihat semua <?= icon('arrow', 'size-3') ?></a></div>
            <div class="mt-4 divide-y divide-stone-800">
                <?php foreach (array_slice($announcements, 0, 2) as $news): ?>
                    <a href="<?= e(url('pengumuman', ['id' => $news['id']])) ?>" class="group block py-4"><div class="flex items-center gap-3 text-xs"><span class="tag <?= $news['important'] ? 'bg-amber-300/10 text-amber-200' : 'bg-lime-300/10 text-lime-200' ?>"><?= $news['important'] ? 'Penting' : e($news['category']) ?></span><span class="text-stone-400"><?= e(date_id($news['date'])) ?></span></div><h3 class="mt-3 text-sm font-medium group-hover:text-lime-200"><?= e($news['title']) ?></h3><p class="mt-2 text-xs leading-6 text-stone-400"><?= e($news['summary']) ?></p></a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <div class="space-y-6">
        <section class="panel p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-semibold tracking-tight">Agenda hari ini</h2><span class="text-lime-300"><?= icon('calendar', 'size-4') ?></span></div>
            <p class="mt-2 text-xs text-stone-400"><?= e($days[$todayIndex]) ?>, <?= e(date_id('today')) ?></p>
            <div class="mt-5 divide-y divide-stone-800">
                <?php $todayClasses = array_filter($schedules, fn($class) => $class['day'] === $todayIndex); ?>
                <?php foreach ($todayClasses as $class): ?>
                    <div class="flex gap-4 py-4"><div class="shrink-0 border-r border-stone-700 pr-4"><p class="text-sm font-semibold"><?= e($class['start']) ?></p><p class="mt-2 text-xs text-stone-400"><?= e($class['end']) ?></p></div><div><h3 class="text-sm font-medium leading-6"><?= e($class['course']) ?></h3><p class="mt-1 text-xs text-stone-400"><?= e($class['type']) ?></p><p class="mt-3 text-xs text-stone-400"><?= e($class['room']) ?></p></div></div>
                <?php endforeach; ?>
                <?php if (!$todayClasses): ?><p class="py-5 text-sm leading-6 text-stone-400">Tidak ada kuliah hari ini. Nikmati waktu luangmu.</p><?php endif; ?>
            </div>
            <a href="<?= e(url('jadwal')) ?>" class="mt-4 flex min-h-11 items-center gap-2 text-xs font-semibold text-lime-300">Jadwal lengkap <?= icon('arrow', 'size-3') ?></a>
        </section>
        <section class="rounded-3xl border border-lime-300/15 bg-lime-300/10 p-6">
            <div class="flex items-center justify-between"><h2 class="text-sm font-semibold">Setiap progres berarti.</h2><span class="text-lime-300"><?= icon('check', 'size-4') ?></span></div>
            <p class="mt-6"><span data-progress-percent class="text-5xl font-semibold tracking-tighter"><?= $percent ?>%</span><span class="ml-2 text-xs text-stone-400">tugas selesai</span></p>
            <progress data-progress value="<?= $done ?>" max="<?= count($tasks) ?>" aria-label="Progres tugas selesai" class="mt-5 h-1.5 w-full overflow-hidden rounded-full accent-lime-400"><?= $percent ?>%</progress>
            <p class="mt-4 text-xs leading-6 text-stone-300"><span data-count="done"><?= $done ?></span> dari <?= count($tasks) ?> tugas sudah kamu tuntaskan.</p><p class="mt-1 text-xs leading-6 text-stone-400">Konsisten lebih berarti daripada sempurna.</p>
            <a href="<?= e(url('tugas', ['status' => 'done'])) ?>" class="mt-5 flex min-h-11 items-center gap-2 text-xs font-semibold text-lime-200">Lihat progresmu <?= icon('arrow', 'size-3') ?></a>
        </section>
    </div>
</div>
