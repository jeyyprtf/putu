<?php
if (isset($_GET['page'])) {
    require __DIR__ . '/app/layouts/dashboard.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require __DIR__ . '/app/components/fonts.php'; ?>
    <link rel="stylesheet" href="assets/css/app.css">
    <title>KUE PUTU</title>
</head>
<body class="font-sans antialiased bg-stone-950">
    <nav class="border-b border-white/40 backdrop-blur-sm fixed top-0 left-0 w-full z-50 px-10">
        <div class="flex items-center justify-between py-4 max-w-7xl mx-auto w-full">
            <div class="flex flex-row gap-2 items-center">
                <a href="index.php" class="text-stone-100 text-3xl font-extrabold tracking-tighter">
                putu<span class="text-lime-500">.</span>
                </a>
            </div>
            <div class="gap-2 flex items-center">
                <a href="app/pages/login.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Masuk</a>
                <div class="bg-lime-500 py-2 px-5 rounded-2xl gap-2 flex items-center group hover:bg-lime-300 transition duration-300 cursor-pointer">
                    <a href="app/pages/register.php" class="text-lime-950 font-bold tracking-tighter hover:text-black transition duration-300">Daftar</a>
                    <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-lime-950"><?php require __DIR__ . '/resources/icons/arrowUpRight.svg'; ?></span>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-20 pt-32 pb-20 flex flex-col gap-20">
        <section class="mx-auto grid lg:max-w-7xl w-full grid-cols-2 items-center gap-16 ">
            <div class="flex flex-col gap-6">
                <h1 class="text-6xl font-inter leading-tight tracking-tighter text-stone-100">Kuliah lebih teratur.
                    <br>
                    <span class="text-lime-300">Pikiran lebih lega.</span>
                </h1>
                <p class="text-base text-stone-100/80 max-w-lg">Tugas, jadwal, dan kabar kelas. Satu ruang 
                    sederhana untuk merapikan hari, biar kamu punya ruang untuk hal yang berarti.</p>
                <div class="mt-2 gap-2 flex items-center">
                    <div class="bg-lime-500 py-2 px-5 rounded-2xl gap-2 flex items-center group hover:bg-lime-300 transition duration-300 cursor-pointer">
                        <a href="app/pages/register.php" class="text-lime-950 font-semibold tracking-tighter hover:text-black transition duration-300">Mulai Langkahmu</a>
                        <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-lime-950"><?php require __DIR__ . '/resources/icons/arrowRight.svg'; ?></span>
                    </div>
                    <div class="py-2 px-5 rounded-2xl gap-2 flex items-center group transition duration-300 cursor-pointer">
                        <a href="app/pages/register.php" class="text-stone-200 tracking-tighter hover:text-white transition duration-300">Kenali PUTU</a>
                        <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-white"><?php require __DIR__ . '/resources/icons/arrowUpRight.svg'; ?></span>
                    </div>
                </div>
            </div>
            <div class="border border-white/40 w-full h-full flex item-center justify-center rounded-3xl bg-lime-900/40">
                <img src="resources/img/macosWallpaper.png" alt="MacOS wallpaper" class="w-full h-full object-cover rounded-3xl">
            </div>
        </section>

        <div class="h-[1px] w-full bg-white/30 max-w-7xl mx-auto"></div>
        
        <section class="mx-auto w-full lg:max-w-7xl gap-10 flex flex-col">
            <div class="flex justify-between">
                <div>
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-lime-200">Lebih sedikit chaos, lebih banyak fokus</p>
                    <h2 class="text-3xl text-stone-100">Semua punya tempatnya.</h2>
                </div>
                
                <p class="mt-5 max-w-sm text-sm leading-6 text-stone-100/60">Dari deadline sampai pergantian jadwal, tak perlu mengingat semuanya sendiri.</p>
            </div>
            
            <div class="group/cards grid grid-cols-3 gap-4">
                <article class="rounded-4xl border border-white/10 p-6 gap-4 flex flex-col bg-lime-300/20 group-hover/cards:[&:not(:hover)]:brightness-50 hover:brightness-110 hover:bg-lime-500/40 transition duration-300">
                    <span aria-hidden="true" class="inline-block w-6 h-6 text-neutral-300 mb-2"><?php require __DIR__ . '/resources/icons/checklistCircle.svg'; ?></span>
                    <p class="text-neutral-300 text-xs font-bold">01 / TUGAS & PROGRES</p>
                    <h3 class="font-semibold text-xl text-neutral-100 tracking-thighter">Tahu mana yang didahulukan</h3>
                    <p class="text-sm leading-6 text-neutral-400">Jadwal kuliah, ruang, dan dosen dalam satu tampilan. Lebih siap menjalani hari.</p>
                </article>
                <article class="rounded-4xl border border-white/10 p-6 gap-4 flex flex-col bg-lime-300/20 group-hover/cards:[&:not(:hover)]:brightness-50 hover:brightness-110 hover:bg-lime-500/40 transition duration-300">
                    <span aria-hidden="true" class="inline-block w-6 h-6 text-neutral-300 mb-2"><?php require __DIR__ . '/resources/icons/calendar.svg'; ?></span>
                    <p class="text-neutral-300 text-xs font-bold">02 / JADWAL KULIAH</p>
                    <h3 class="font-semibold text-xl text-neutral-100 tracking-thighter"> Ada tempat untuk setiap agenda.</h3>
                    <p class="text-sm leading-6 text-neutral-400">adwal kuliah, ruang, dan dosen dalam satu tampilan. Lebih siap menjalani hari.</p>
                </article>
                <article class="rounded-4xl border border-white/10 p-6 gap-4 flex flex-col bg-lime-300/20 group-hover/cards:[&:not(:hover)]:brightness-50 hover:brightness-110 hover:bg-lime-500/40 transition duration-300">
                    <span aria-hidden="true" class="inline-block w-6 h-6 text-neutral-300 mb-2"><?php require __DIR__ . '/resources/icons/notification.svg'; ?></span>
                    <p class="text-neutral-300 text-xs font-bold">03 / PENGUMUMAN</p>
                    <h3 class="font-semibold text-xl text-neutral-100 tracking-thighter">Kabar kelas tetap dekat.</h3>
                    <p class="text-sm leading-6 text-neutral-400">Temukan pengumuman kelas tanpa tenggelam di percakapan. Informasi penting lebih mudah dicari.</p>
                </article>
            </div>

            <div class="rounded-4xl flex flex-row justify-between bg-lime-900 p-10 mt-10">
                <div>
                    <h2 class="mb-4 text-2xl text-stone-100">Hari yang rapi dimulai dari sini.</h2>
                    <p class="text-sm leading-6 text-stone-100/80">Buat akun, gabung atau gabung kelasmu, lalu jalani satu tugas pada satu waktu.</p>
                </div>
                <div class="flex items-center">
                    <div class="bg-lime-200 py-2 px-4 rounded-2xl gap-2 flex items-center group hover:bg-lime-300 transition duration-300">
                        <a href="app/pages/register.php" class="text-emerald-950 font-semibold tracking-tighter hover:text-black transition duration-300">Mulai Langkahmu</a>
                        <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-emerald-950"><?php require __DIR__ . '/resources/icons/arrowRight.svg'; ?></span>
                    </div>
                </div>
            </div>
        </section>

    </main>
    <footer class=" text-center text-stone-100/50">
        <div class="h-[1px] w-full bg-white/30 max-w-7xl mx-auto"></div>
        <div class="flex flex-row justify-between max-w-7xl mx-auto p-10 text-xs">
            <p class="">
                © 2026 PUTU. Make room for what matters.
            </p>
            <p class="">
                Your academic sidekick.
            </p>
        </div>
    </footer>
    <?php include dirname(__DIR__) . '/app/pages/login.php'; ?>

</body>
</html>
