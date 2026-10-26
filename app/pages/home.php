<?php
$page = 'home';
$title = 'Ruang belajar yang lebih teratur';
require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
    </head>
    <body class="font-sans antialiased bg-stone-950">
        <nav aria-label="Navigasi beranda" class="border-b border-white/40 backdrop-blur-sm fixed top-0 left-0 w-full z-50 px-5 sm:px-10">
            <div class="flex items-center justify-between py-4 max-w-7xl mx-auto w-full">
                <div class="flex flex-row gap-2 items-center">
                    <a href="<?= e(url()) ?>" class="text-stone-100 text-3xl font-extrabold tracking-tighter">
                        putu<span class="text-lime-500">.</span>
                    </a>
                </div>
                <div class="gap-2 flex items-center">
                    <a href="<?= e(url('login')) ?>" class="min-h-11 inline-flex items-center text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">
                        Masuk
                    </a>
                    <a href="<?= e(url('register')) ?>" class="min-h-11 bg-lime-500 py-2 px-5 rounded-2xl gap-2 flex items-center group hover:bg-lime-300 transition duration-300 cursor-pointer">
                        <span class="text-lime-950 font-bold tracking-tighter hover:text-black transition duration-300">Daftar</span> <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-lime-950">
                        <?php require __DIR__ . '/../../resources/icons/arrowUpRight.svg'; ?>
                        </span>
                    </a>
                </div>
            </div>
        </nav>
        <main class="min-h-screen px-5 sm:px-10 lg:px-20 pt-28 sm:pt-32 pb-20 flex flex-col gap-20">
            <section class="mx-auto grid lg:max-w-7xl w-full grid-cols-1 lg:grid-cols-2 items-center gap-8 lg:gap-16 ">
                <div class="flex flex-col gap-6">
                    <h1 class="text-4xl sm:text-5xl xl:text-6xl font-inter leading-tight tracking-tighter text-stone-100">
                        Kuliah lebih teratur. <br> <span class="text-lime-300">Pikiran lebih lega.</span>
                    </h1>
                    <p class="text-base text-stone-100/80 max-w-lg">
                        Tugas, jadwal, dan kabar kelas. Satu ruang sederhana untuk merapikan hari, biar kamu punya ruang untuk hal yang berarti.
                    </p>
                    <div class="mt-2 gap-2 flex items-center">
                        <a href="<?= e(url('register')) ?>" class="min-h-11 bg-lime-500 py-2 px-5 rounded-2xl gap-2 flex items-center group hover:bg-lime-300 transition duration-300 cursor-pointer">
                            <span class="text-lime-950 font-semibold tracking-tighter hover:text-black transition duration-300">Mulai Langkahmu</span> <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-lime-950">
                            <?php require __DIR__ . '/../../resources/icons/arrowRight.svg'; ?>
                            </span>
                        </a>
                        <a href="#fitur" class="min-h-11 py-2 px-5 rounded-2xl gap-2 flex items-center group transition duration-300 cursor-pointer">
                            <span class="text-stone-200 tracking-tighter hover:text-white transition duration-300">Kenali PUTU</span> <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-white">
                            <?php require __DIR__ . '/../../resources/icons/arrowUpRight.svg'; ?>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="border border-white/40 w-full h-full flex items-center justify-center rounded-3xl bg-lime-900/40">
                    <img src="<?= e(asset('resources/img/macosWallpaper.png')) ?>" alt="MacOS wallpaper" class="w-full h-full object-cover rounded-3xl">
                </div>
            </section>
            <div class="h-[1px] w-full bg-white/30 max-w-7xl mx-auto">
            </div>
            <section id="fitur" class="mx-auto w-full lg:max-w-7xl gap-10 flex flex-col scroll-mt-28">
                <div class="flex flex-col md:flex-row justify-between gap-4">
                    <div>
                        <p class="mb-3 text-xs font-bold uppercase tracking-[0.2em] text-lime-200">
                            Lebih sedikit chaos, lebih banyak fokus
                        </p>
                        <h2 class="text-3xl text-stone-100">
                            Semua punya tempatnya.
                        </h2>
                    </div>
                    <p class="mt-5 max-w-sm text-sm leading-6 text-stone-100/60">
                        Dari deadline sampai pergantian jadwal, tak perlu mengingat semuanya sendiri.
                    </p>
                </div>
                <div class="group/cards grid grid-cols-1 md:grid-cols-3 gap-4">
                    <article class="rounded-4xl border border-white/10 p-6 gap-4 flex flex-col bg-lime-300/20 group-hover/cards:[&:not(:hover)]:brightness-50 hover:brightness-110 hover:bg-lime-500/40 transition duration-300">
                        <span aria-hidden="true" class="inline-block w-6 h-6 text-neutral-300 mb-2">
                        <?php require __DIR__ . '/../../resources/icons/checklistCircle.svg'; ?>
                        </span>
                        <p class="text-neutral-300 text-xs font-bold">
                            01 / TUGAS & PROGRES
                        </p>
                        <h3 class="font-semibold text-xl text-neutral-100 tracking-tight">
                            Tahu mana yang didahulukan
                        </h3>
                        <p class="text-sm leading-6 text-neutral-400">
                            Semua tugas dan deadline dalam satu tampilan. Tahu mana yang perlu kamu kerjakan lebih dulu.
                        </p>
                    </article>
                    <article class="rounded-4xl border border-white/10 p-6 gap-4 flex flex-col bg-lime-300/20 group-hover/cards:[&:not(:hover)]:brightness-50 hover:brightness-110 hover:bg-lime-500/40 transition duration-300">
                        <span aria-hidden="true" class="inline-block w-6 h-6 text-neutral-300 mb-2">
                        <?php require __DIR__ . '/../../resources/icons/calendar.svg'; ?>
                        </span>
                        <p class="text-neutral-300 text-xs font-bold">
                            02 / JADWAL KULIAH
                        </p>
                        <h3 class="font-semibold text-xl text-neutral-100 tracking-tight">
                            Ada tempat untuk setiap agenda.
                        </h3>
                        <p class="text-sm leading-6 text-neutral-400">
                            Jadwal kuliah, ruang, dan dosen dalam satu tampilan. Lebih siap menjalani hari.
                        </p>
                    </article>
                    <article class="rounded-4xl border border-white/10 p-6 gap-4 flex flex-col bg-lime-300/20 group-hover/cards:[&:not(:hover)]:brightness-50 hover:brightness-110 hover:bg-lime-500/40 transition duration-300">
                        <span aria-hidden="true" class="inline-block w-6 h-6 text-neutral-300 mb-2">
                        <?php require __DIR__ . '/../../resources/icons/notification.svg'; ?>
                        </span>
                        <p class="text-neutral-300 text-xs font-bold">
                            03 / PENGUMUMAN
                        </p>
                        <h3 class="font-semibold text-xl text-neutral-100 tracking-tight">
                            Kabar kelas tetap dekat.
                        </h3>
                        <p class="text-sm leading-6 text-neutral-400">
                            Temukan pengumuman kelas tanpa tenggelam di percakapan. Informasi penting lebih mudah dicari.
                        </p>
                    </article>
                </div>
                <div class="rounded-4xl flex flex-col sm:flex-row gap-6 justify-between bg-lime-900 p-6 sm:p-10 mt-10">
                    <div>
                        <h2 class="mb-4 text-2xl text-stone-100">
                            Hari yang rapi dimulai dari sini.
                        </h2>
                        <p class="text-sm leading-6 text-stone-100/80">
                            Buat akun, buat atau gabung kelasmu, lalu jalani satu tugas pada satu waktu.
                        </p>
                    </div>
                    <div class="flex items-center">
                        <a href="<?= e(url('register')) ?>" class="min-h-11 bg-lime-200 py-2 px-4 rounded-2xl gap-2 flex items-center group hover:bg-lime-300 transition duration-300">
                            <span class="text-emerald-950 font-semibold tracking-tighter hover:text-black transition duration-300">Mulai Langkahmu</span> <span aria-hidden="true" class="w-4 h-4 inline-block ml-1 transition-transform duration-300 group-hover:translate-x-1 text-emerald-950">
                            <?php require __DIR__ . '/../../resources/icons/arrowRight.svg'; ?>
                            </span>
                        </a>
                    </div>
                </div>
            </section>
        </main>
        <footer class=" text-center text-stone-100/50">
            <div class="h-[1px] w-full bg-white/30 max-w-7xl mx-auto">
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-between max-w-7xl mx-auto p-6 sm:p-10 text-xs">
                <p class="">
                    © 2026 PUTU. Make room for what matters.
                </p>
                <p class="">
                    Your academic sidekick.
                </p>
            </div>
        </footer>
    </body>
</html>
