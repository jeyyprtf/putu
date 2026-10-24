<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <title>PUTU | Tugas saya</title>
</head>
<body class="font-sans antialiased bg-stone-950">
    <nav class="border-b border-white/40 backdrop-blur-sm fixed top-0 left-0 w-full z-50 px-10">
        <div class="flex items-center justify-between py-4 max-w-7xl mx-auto w-full">
            <a href="dashboard.php" class="text-stone-100 text-3xl font-extrabold tracking-tighter">
                putu<span class="text-lime-500">.</span>
            </a>
            <div class="gap-2 flex items-center">
                <a href="dashboard.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Beranda</a>
                <a href="tugas.php" class="bg-lime-500 py-2 px-5 rounded-2xl text-lime-950 font-bold tracking-tighter">Tugas</a>
                <a href="jadwal.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Jadwal</a>
                <a href="pengumuman.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Pengumuman</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-20 pt-32 pb-20">
        <div class="mx-auto w-full max-w-3xl flex flex-col gap-8">

            <section class="flex flex-col gap-3">
                <h1 class="text-6xl font-inter leading-tight tracking-tighter text-stone-100">Tugas saya</h1>
                <p class="text-base text-stone-100/80">Lihat prioritasmu, mulai kerjakan, dan rayakan yang selesai.</p>
            </section>

            <section class="flex flex-wrap gap-2">
                <button type="button" class="rounded-full bg-lime-500 px-4 py-1.5 text-sm font-semibold text-lime-950">Semua tugas</button>
                <button type="button" class="rounded-full border border-white/20 px-4 py-1.5 text-sm font-semibold text-stone-200 hover:text-lime-200 transition duration-300">Belum dikerjakan</button>
                <button type="button" class="rounded-full border border-white/20 px-4 py-1.5 text-sm font-semibold text-stone-200 hover:text-lime-200 transition duration-300">Sedang dikerjakan</button>
                <button type="button" class="rounded-full border border-white/20 px-4 py-1.5 text-sm font-semibold text-stone-200 hover:text-lime-200 transition duration-300">Selesai</button>
            </section>

            <section class="flex flex-col gap-4">
                <div>
                    <label for="cari" class="block mb-2 text-sm font-semibold text-stone-100">Pencarian</label>
                    <input id="cari" type="search" placeholder="Cari tugas atau mata kuliah..."
                           class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3.5 text-sm text-stone-100 placeholder:text-stone-500 outline-none focus:border-lime-300">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="matkul" class="block mb-2 text-sm font-semibold text-stone-100">Mata kuliah</label>
                        <select id="matkul" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3.5 text-sm text-stone-100 outline-none focus:border-lime-300">
                            <option>Semua mata kuliah</option>
                            <option>Basis Data</option>
                            <option>Algoritma &amp; Struktur Data</option>
                            <option>Analisis Proses Bisnis</option>
                        </select>
                    </div>
                    <div>
                        <label for="urut" class="block mb-2 text-sm font-semibold text-stone-100">Urutkan</label>
                        <select id="urut" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3.5 text-sm text-stone-100 outline-none focus:border-lime-300">
                            <option>Deadline terdekat</option>
                            <option>Judul A-Z</option>
                        </select>
                    </div>
                </div>
            </section>

            <p class="text-sm text-stone-100/60">4 tugas &middot; 3 aktif, 1 selesai</p>

            <!-- Daftar tugas -->
            <section class="flex flex-col gap-4">

                <article class="rounded-4xl border border-white/10 p-6 gap-3 flex flex-col bg-lime-300/20 hover:bg-lime-500/40 transition duration-300">
                    <p class="text-neutral-300 text-xs font-bold">BD &middot; TUGAS</p>
                    <div class="flex items-start gap-4">
                        <input type="checkbox" aria-label="Tandai selesai" class="mt-1 w-5 h-5 accent-lime-500">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-neutral-100">Normalisasi database minimarket</h2>
                            <p class="text-sm text-neutral-400">Basis Data</p>
                        </div>
                        <span class="text-sm font-semibold text-red-300">Besok</span>
                    </div>
                    <p class="text-sm leading-6 text-neutral-300">Normalisasikan tabel transaksi hingga 3NF. Sertakan ERD dan query SQL.</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-stone-300">Belum dikerjakan</span>
                        <a href="#" class="font-semibold text-lime-500 hover:text-lime-300 transition duration-300">Detail &rarr;</a>
                    </div>
                    <p class="text-sm text-red-300">Prioritas &middot; tenggat terdekat</p>
                </article>

                <article class="rounded-4xl border border-white/10 p-6 gap-3 flex flex-col bg-lime-300/20 hover:bg-lime-500/40 transition duration-300">
                    <p class="text-neutral-300 text-xs font-bold">ASD &middot; PRAKTIKUM</p>
                    <div class="flex items-start gap-4">
                        <input type="checkbox" aria-label="Tandai selesai" class="mt-1 w-5 h-5 accent-lime-500">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-neutral-100">Implementasi stack &amp; queue</h2>
                            <p class="text-sm text-neutral-400">Algoritma &amp; Struktur Data</p>
                        </div>
                        <span class="text-sm text-neutral-300">2 hari lagi</span>
                    </div>
                    <p class="text-sm leading-6 text-neutral-300">Implementasikan stack dan queue, lalu uji operasi dasar keduanya.</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-amber-300">Sedang dikerjakan</span>
                        <a href="#" class="font-semibold text-lime-500 hover:text-lime-300 transition duration-300">Detail &rarr;</a>
                    </div>
                </article>

                <article class="rounded-4xl border border-white/10 p-6 gap-3 flex flex-col bg-lime-300/20 hover:bg-lime-500/40 transition duration-300">
                    <p class="text-neutral-300 text-xs font-bold">APB &middot; PROYEK</p>
                    <div class="flex items-start gap-4">
                        <input type="checkbox" aria-label="Tandai selesai" class="mt-1 w-5 h-5 accent-lime-500">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-neutral-100">Analisis alur checkout e-commerce</h2>
                            <p class="text-sm text-neutral-400">Analisis Proses Bisnis</p>
                        </div>
                        <span class="text-sm text-neutral-300">4 hari lagi</span>
                    </div>
                    <p class="text-sm leading-6 text-neutral-300">Petakan proses checkout dan identifikasi peluang perbaikan alurnya.</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-stone-300">Belum dikerjakan</span>
                        <a href="#" class="font-semibold text-lime-500 hover:text-lime-300 transition duration-300">Detail &rarr;</a>
                    </div>
                </article>

                <article class="rounded-4xl border border-white/10 p-6 gap-3 flex flex-col bg-lime-300/20 hover:bg-lime-500/40 transition duration-300">
                    <p class="text-neutral-300 text-xs font-bold">BD &middot; TUGAS</p>
                    <div class="flex items-start gap-4">
                        <input type="checkbox" checked aria-label="Tandai selesai" class="mt-1 w-5 h-5 accent-lime-500">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-neutral-100">Rancangan ERD perpustakaan</h2>
                            <p class="text-sm text-neutral-400">Basis Data</p>
                        </div>
                        <span class="text-sm text-neutral-300">Kemarin</span>
                    </div>
                    <p class="text-sm leading-6 text-neutral-300">Rancang entitas, relasi, dan kardinalitas sistem perpustakaan.</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-lime-300">Selesai</span>
                        <a href="#" class="font-semibold text-lime-500 hover:text-lime-300 transition duration-300">Detail &rarr;</a>
                    </div>
                </article>

            </section>

            <p class="text-xs leading-5 text-stone-100/50">Progres ini hanya untuk catatanmu, bukan pengumpulan tugas. Kumpulkan melalui LMS.</p>
        </div>
    </main>
</body>
</html>