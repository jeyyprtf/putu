<?php if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=login'); exit; } ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0c0a09">
    <title>PUTU | <?= $page === 'register' ? 'Daftar' : 'Masuk' ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require __DIR__ . '/../components/fonts.php'; ?>
    <link rel="stylesheet" href="<?= e(asset('resources/css/app.css')) ?>">
    <script src="<?= e(asset('public/assets/js/app.js')) ?>" defer></script>
</head>
<body class="min-h-dvh bg-stone-950 font-sans text-stone-100 antialiased">
    <div class="grid min-h-dvh lg:grid-cols-2">
        <aside aria-label="Ruang belajar PUTU" class="relative m-3 hidden min-h-[700px] overflow-hidden rounded-3xl bg-stone-900 lg:block">
            <img src="<?= e(asset('public/assets/images/library-study.jpg')) ?>" alt="Mahasiswa berheadphone mencari buku di perpustakaan" class="absolute inset-0 h-full w-full object-cover" width="1400" height="883" fetchpriority="high">
            <div class="auth-photo absolute inset-0"></div>
            <div class="relative flex h-full flex-col justify-between gap-16 p-10 xl:p-12">
                <a href="<?= e(url()) ?>" class="w-fit text-4xl font-extrabold tracking-tighter drop-shadow-lg" aria-label="PUTU beranda">putu<span class="text-lime-400">.</span></a>
                <div><p class="eyebrow">Your academic sidekick</p><h2 class="mt-5 max-w-lg text-4xl font-medium leading-tight tracking-tighter xl:text-5xl">Kuliah lebih teratur.<br><span class="text-lime-200">Pikiran lebih lega.</span></h2><p class="mt-5 max-w-sm text-sm leading-7 text-stone-200">Beri tempat untuk tugas, jadwal, dan kabar kelas. Sisakan ruang untuk hal yang berarti.</p><a href="https://unsplash.com/photos/person-browsing-books-on-a-library-shelf-pPIalGzMv-k" target="_blank" rel="noopener noreferrer" class="mt-8 inline-block text-[10px] text-stone-300 underline underline-offset-4">Foto: Austin / Unsplash</a></div>
            </div>
        </aside>
        <div class="relative flex min-h-dvh flex-col px-6 py-6 sm:px-10 lg:px-12">
            <div class="flex items-center justify-between gap-3"><a href="<?= e(url()) ?>" class="flex min-h-11 items-center gap-2 text-xs text-stone-400 hover:text-lime-200"><?= icon('back', 'size-4') ?>Kembali ke beranda</a><a href="<?= e(url()) ?>" class="text-2xl font-extrabold tracking-tighter lg:hidden">putu<span class="text-lime-500">.</span></a></div>
            <main class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-10 sm:py-14">
                <?php require __DIR__ . '/../pages/' . $page . '.php'; ?>
            </main>
            <footer class="text-center text-[10px] leading-5 text-stone-500">© <?= date('Y') ?> PUTU. Make room for what matters.</footer>
        </div>
    </div>
</body>
</html>
