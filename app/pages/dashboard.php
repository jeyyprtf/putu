<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <title>PUTU | Beranda</title>
</head>
<body class="font-sans antialiased bg-stone-950 text-stone-100">

    <nav class="border-b border-white/40 backdrop-blur-sm fixed top-0 left-0 w-full z-50 px-10">
        <div class="flex items-center justify-between py-4 max-w-7xl mx-auto w-full">
            <a href="dashboard.php" class="text-stone-100 text-3xl font-extrabold tracking-tighter">
                putu<span class="text-lime-500">.</span>
            </a>
            <div class="gap-2 flex items-center">
                <a href="dashboard.php" class="bg-lime-500 py-2 px-5 rounded-2xl text-lime-950 font-bold tracking-tighter">Beranda</a>
                <a href="tugas.php" class="text-white font-bold tracking-tighter hover:text-lime-200 px-4">Tugas</a>
                <a href="jadwal.php" class="text-white font-bold tracking-tighter hover:text-lime-200 px-4">Jadwal</a>
                <a href="pengumuman.php" class="text-white font-bold tracking-tighter hover:text-lime-200 px-4">Pengumuman</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-10 pt-32 pb-20">
        <div class="mx-auto max-w-3xl flex flex-col gap-10">

            <section>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-lime-200">Mahasiswa &middot; SIB 2B</p>
                <h1 class="mt-3 text-5xl font-inter font-extrabold tracking-tighter">Halo, Juan.</h1>
                <p class="mt-2 text-stone-400">Yuk, buat hari ini sedikit lebih teratur.</p>
            </section>

            <section class="grid grid-cols-2 gap-4">
                <div class="rounded-4xl border border-white/10 bg-lime-300/20 p-6">
                    <p class="text-5xl font-semibold tracking-tighter">05</p>
                    <p class="mt-2 text-sm text-neutral-300">Tugas aktif</p>
                </div>
                <div class="rounded-4xl border border-white/10 bg-lime-300/20 p-6">
                    <p class="text-5xl font-semibold tracking-tighter">02</p>
                    <p class="mt-2 text-sm text-neutral-300">Perlu diprioritaskan</p>
                </div>
                <div class="rounded-4xl border border-white/10 bg-lime-300/20 p-6">
                    <p class="text-5xl font-semibold tracking-tighter">01</p>
                    <p class="mt-2 text-sm text-neutral-300">Sedang dikerjakan</p>
                </div>
                <div class="rounded-4xl border border-white/10 bg-lime-300/20 p-6">
                    <p class="text-5xl font-semibold tracking-tighter">01</p>
                    <p class="mt-2 text-sm text-neutral-300">Sudah selesai</p>
                </div>
            </section>

            <section class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Prioritas tugas</h2>
                    <a href="tugas.php" class="text-sm font-semibold text-lime-500 hover:text-lime-300">Lihat semua &rarr;</a>
                </div>
                <label class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <input type="checkbox" class="w-5 h-5 accent-lime-500">
                    <span class="flex-1">
                        <span class="block font-semibold">Normalisasi database minimarket</span>
                        <span class="block text-sm text-stone-400">Basis Data</span>
                    </span>
                    <span class="text-sm text-red-300">Besok</span>
                </label>
                <label class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <input type="checkbox" class="w-5 h-5 accent-lime-500">
                    <span class="flex-1">
                        <span class="block font-semibold">Implementasi stack &amp; queue</span>
                        <span class="block text-sm text-stone-400">Algoritma &amp; Struktur Data</span>
                    </span>
                    <span class="text-sm text-stone-300">2 hari lagi</span>
                </label>
            </section>

            <section class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Jadwal hari ini</h2>
                    <a href="jadwal.php" class="text-sm font-semibold text-lime-500 hover:text-lime-300">Lihat jadwal &rarr;</a>
                </div>
                <div class="flex items-center gap-5 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="border-r-2 border-lime-500 pr-5">
                        <p class="text-xl font-semibold">10:30</p>
                        <p class="text-sm text-stone-400">14:30</p>
                    </div>
                    <div>
                        <p class="font-semibold">Basis Data</p>
                        <p class="text-sm text-stone-400">LPR7 &middot; Gedung B</p>
                    </div>
                </div>
            </section>

            <section class="rounded-4xl bg-lime-900 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Progres belajarmu</h2>
                    <p class="font-semibold text-lime-200">17%</p>
                </div>
                <div class="mt-4 h-2 rounded-full bg-white/15">
                    <div class="h-2 w-[17%] rounded-full bg-lime-500"></div>
                </div>
                <p class="mt-3 text-sm text-stone-100/80">1 dari 6 tugas sudah kamu tuntaskan.</p>
            </section>

        </div>
    </main>
</body>
</html>