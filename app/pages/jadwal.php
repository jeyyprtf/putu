<?php
// Data jadwal: [mulai, selesai, mata kuliah, ruang, jenis & SKS, dosen]
$jadwal = [
    'Senin' => [
        ['07:00', '09:30', 'Algoritma &amp; Struktur Data', 'LPY4 &middot; Lantai 7', 'Praktikum &middot; 3 SKS', 'Vivin Ayu Lestari'],
        ['10:30', '12:10', 'Basis Data', 'LSI1 &middot; Lantai 6', 'Teori &middot; 3 SKS', 'Elok Nur Hamdana'],
    ],
    'Selasa' => [
        ['08:40', '11:20', 'Matematika Lanjut', 'LSI2 &middot; Lantai 6', 'Teori &middot; 2 SKS', 'Erfan Rohadi'],
        ['13:00', '15:30', 'Analisis Proses Bisnis', 'RT09 &middot; Lantai 8', 'Teori &middot; 2 SKS', 'Hendra Pradibta'],
    ],
    'Rabu' => [
        ['07:00', '10:30', 'Algoritma &amp; Struktur Data', 'LPR6 &middot; Gedung B', 'Praktikum &middot; 3 SKS', 'Vivin Ayu Lestari'],
    ],
    'Kamis' => [
        ['10:30', '14:30', 'Basis Data', 'LPR7 &middot; Gedung B', 'Praktikum &middot; 3 SKS', 'Elok Nur Hamdana'],
    ],
    'Jumat' => [
        ['07:00', '10:30', 'Sistem Operasi', 'LERP &middot; Lantai 7', 'Praktikum &middot; 3 SKS', 'Meyti Eka Apriyani'],
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <title>PUTU | Jadwal kuliah</title>
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
                <a href="jadwal.php" class="bg-lime-500 py-2 px-5 rounded-2xl text-lime-950 font-bold tracking-tighter">Jadwal</a>
                <a href="pengumuman.php" class="text-white font-bold tracking-tighter hover:text-lime-200 transition duration-300 px-4">Pengumuman</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-20 pt-32 pb-20">
        <div class="mx-auto w-full max-w-3xl flex flex-col gap-8">

            <section class="flex flex-col gap-3">
                <h1 class="text-6xl font-inter leading-tight tracking-tighter text-stone-100">Jadwal kuliah</h1>
                <p class="text-base text-stone-100/80">Tahu kapan, di mana, dan bersama siapa kamu belajar.</p>
            </section>

            <section class="grid grid-cols-3 gap-4">
                <div class="rounded-4xl border border-white/10 bg-lime-300/20 p-6">
                    <p class="text-4xl font-semibold tracking-tighter text-neutral-100">5</p>
                    <p class="mt-2 text-xs text-neutral-300">Mata kuliah</p>
                </div>
                <div class="rounded-4xl border border-white/10 bg-lime-300/20 p-6">
                    <p class="text-4xl font-semibold tracking-tighter text-neutral-100">7</p>
                    <p class="mt-2 text-xs text-neutral-300">Kelas / minggu</p>
                </div>
                <div class="rounded-4xl border border-white/10 bg-lime-300/20 p-6">
                    <p class="text-4xl font-semibold tracking-tighter text-neutral-100">22</p>
                    <p class="mt-2 text-xs text-neutral-300">Jam / minggu</p>
                </div>
            </section>

            <div>
                <label for="cari" class="block mb-2 text-sm font-semibold text-stone-100">Pencarian</label>
                <input id="cari" type="search" placeholder="Cari mata kuliah, ruang, atau dosen..."
                       class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3.5 text-sm text-stone-100 placeholder:text-stone-500 outline-none focus:border-lime-300">
            </div>

            <div class="flex items-center justify-between text-sm text-stone-100/60">
                <p>Semester 3 &middot; SIB 2B</p>
                <p>Jadwal mingguan &middot; Senin&ndash;Minggu</p>
            </div>

            <nav aria-label="Pilih hari" class="flex flex-wrap gap-2">
                <?php foreach ($jadwal as $hari => $daftar): ?>
                    <a href="#<?= strtolower($hari) ?>" class="rounded-full border border-white/20 px-4 py-1.5 text-sm font-semibold text-stone-200 hover:text-lime-200 transition duration-300"><?= substr($hari, 0, 3) ?></a>
                <?php endforeach; ?>
            </nav>

            <?php foreach ($jadwal as $hari => $daftar): ?>
                <section id="<?= strtolower($hari) ?>" class="flex flex-col gap-3 scroll-mt-28">
                    <h2 class="text-xl font-semibold text-stone-100"><?= $hari ?></h2>

                    <?php foreach ($daftar as [$mulai, $selesai, $matkul, $ruang, $jenis, $dosen]): ?>
                        <article class="rounded-4xl border border-white/10 p-6 gap-3 flex flex-col bg-lime-300/20 hover:bg-lime-500/40 transition duration-300">
                            <div class="flex items-center gap-5">
                                <div class="border-r-2 border-lime-500 pr-5">
                                    <p class="text-xl font-semibold text-neutral-100"><?= $mulai ?></p>
                                    <p class="text-sm text-neutral-400"><?= $selesai ?></p>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-neutral-100"><?= $matkul ?></h3>
                                    <p class="text-sm text-neutral-400"><?= $ruang ?></p>
                                </div>
                            </div>
                            <p class="text-sm text-neutral-300"><?= $jenis ?></p>
                            <p class="text-sm text-neutral-100"><?= $dosen ?></p>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>

            <p class="text-sm text-stone-100/60">Sabtu &amp; Minggu &middot; Tidak ada kelas terjadwal.</p>
        </div>
    </main>
</body>
</html>