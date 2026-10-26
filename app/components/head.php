<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0c0a09">
<title>PUTU | <?= e($title) ?></title>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4">
</script>
<?php require __DIR__ . '/fonts.php'; ?>
<link rel="stylesheet" href="<?= e(asset('resources/css/app.css')) ?>">
<?php if (in_array($page, ['dashboard', 'tugas', 'jadwal', 'pengumuman'], true)): ?>
    <script id="task-data" type="application/json" data-today="<?= $today->format('Y-m-d') ?>" data-storage-path="<?= e(asset('index.php')) ?>">
        <?= json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
<?php endif; ?>
<script src="<?= e(asset('public/assets/js/app.js')) ?>" defer>
</script>
