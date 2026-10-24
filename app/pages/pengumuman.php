<?php
// Data pengumuman: [kategori, penting, judul, penulis, isi]
$pengumuman = [
    ['Akademik', true, 'Perubahan ruang kelas Basis Data', 'Admin Akademik', 'Perkuliahan Basis Data hari Kamis dipindahkan ke LPR7, Gedung B. Jam perkuliahan tetap mengikuti jadwal. Pastikan kamu mengecek ruang sebelum kelas dimulai.'],
    ['Kesempatan', false, 'Saatnya upgrade skill: sertifikasi database', 'Jurusan Teknologi Informasi', 'Pendaftaran sertifikasi database dibuka untuk mahasiswa Sistem Informasi Bisnis. Cek informasi dan ketentuan pendaftaran melalui jurusan.'],
    ['Akademik', false, 'Persiapan pekan UTS', 'Admin Akademik', 'Cek kembali tugas, materi perkuliahan, dan kartu ujianmu. Pantau jadwal UTS yang dibagikan oleh admin akademik.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <title>PUTU | Pengumuman</title>
</head>
<body class="font-sans antialiased bg-stone-950">
    <nav class="border-b border-white/40 backdrop-blur-sm fixed top-0 left-0 w-full z-50 px-10">
        <div class="flex items-center justify-between py-4 max-w-7xl mx-auto w-full">
            <a href="dashboard.php" class="text-stone-100 text-3xl font-extrabold tracking-tighter">
                putu<span class="text-lime-500">.</span>
            </a>
            <div class="gap-2 flex items-center">
                <a href="dashboard.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Beranda</a>
                <a href="tugas.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Tugas</a>
                <a href="jadwal.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Jadwal</a>
                <a href="pengumuman.php" class="bg-lime-500 py-2 px-5 rounded-2xl text-lime-950 font-bold tracking-tighter">Pengumuman</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-20 pt-32 pb-20">
        <div class="mx-auto w-full max-w-3xl flex flex-col gap-8">

            <section class="flex flex-col gap-3">
                <h1 class="text-6xl font-inter leading-tight tracking-tighter text-stone-100">Pengumuman</h1>
                <p class="text-base text-stone-100/80">Kabar yang perlu kamu tahu, dalam satu tempat.</p>
            </section>

            <div>
                <label for="cari" class="block mb-2 text-sm font-semibold text-stone-100">Pencarian</label>
                <input id="cari" type="search" placeholder="Cari pengumuman..."
                       class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3.5 text-sm text-stone-100 placeholder:text-stone-500 outline-none focus:border-lime-300">
            </div>

            <section class="flex flex-wrap gap-2">
                <button type="button" class="rounded-full bg-lime-500 px-4 py-1.5 text-sm font-semibold text-lime-950">Semua</button>
                <?php foreach (['Penting', 'Akademik', 'Kesempatan', 'Umum'] as $chip): ?>
                    <button type="button" class="rounded-full border border-white/20 px-4 py-1.5 text-sm font-semibold text-stone-200 hover:text-lime-200 transition duration-300"><?= $chip ?></button>
                <?php endforeach; ?>
            </section>

            <p class="text-sm text-stone-100/60"><?= count($pengumuman) ?> pengumuman &middot; SIB 2B</p>

            <!-- Daftar pengumuman -->
            <section class="flex flex-col gap-4">
                <?php foreach ($pengumuman as [$kategori, $penting, $judul, $penulis, $isi]): ?>
                    <article class="rounded-4xl border border-white/10 p-6 gap-3 flex flex-col bg-lime-300/20 hover:bg-lime-500/40 transition duration-300">
                        <?php if ($penting): ?>
                            <p class="flex items-center gap-2 text-sm font-semibold text-red-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 21V4m0 0h12l-2 4 2 4H5"/></svg>
                                <?= $kategori ?> &middot; Penting
                            </p>
                        <?php else: ?>
                            <p class="text-sm text-neutral-300"><?= $kategori ?></p>
                        <?php endif; ?>

                        <h2 class="text-2xl font-semibold tracking-tight text-neutral-100"><?= $judul ?></h2>
                        <p class="text-sm text-neutral-400"><?= $penulis ?></p>
                        <p class="text-sm leading-6 text-neutral-300"><?= $isi ?></p>
                        <a href="#" class="text-sm font-semibold text-lime-500 hover:text-lime-300 transition duration-300">Baca selengkapnya &rarr;</a>
                    </article>
                <?php endforeach; ?>
            </section>

        </div>
    </main>
</body>
</html>