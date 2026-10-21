<?php
date_default_timezone_set('Asia/Jakarta');

$kelas = 'SIB - 2B';

// Data dummy. Ganti dengan query database dengan struktur yang sama.
$jadwal = [
    'Senin' => [
        ['mulai' => '07:00', 'selesai' => '09:30', 'matkul' => 'Algoritma Struktur Data', 'ruang' => 'LPR3, Gedung A', 'jenis' => 'Teori'],
    ],
    'Selasa' => [
        ['mulai' => '13:00', 'selesai' => '15:30', 'matkul' => 'Analisis Proses Bisnis', 'ruang' => 'R.204, Gedung B', 'jenis' => 'Teori'],
    ],
    'Rabu' => [
        ['mulai' => '09:00', 'selesai' => '11:30', 'matkul' => 'Sistem Operasi', 'ruang' => 'R.108, Gedung A', 'jenis' => 'Teori'],
    ],
    'Kamis' => [
        ['mulai' => '10:30', 'selesai' => '14:30', 'matkul' => 'Basis Data', 'ruang' => 'LPR7, Gedung B', 'jenis' => 'Praktikum'],
    ],
    'Jumat' => [
        ['mulai' => '08:00', 'selesai' => '10:30', 'matkul' => 'Matematika Diskrit', 'ruang' => 'R.201, Gedung A', 'jenis' => 'Teori'],
    ],
];

$namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$hariIni  = $namaHari[(int) date('N') - 1];

$totalMatkul = array_sum(array_map('count', $jadwal));

function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <link rel="stylesheet" href="../../public/assets/css/app.css">
    <title>Jadwal - PUTULAGI</title>
</head>
<body class="font-sans antialiased bg-emerald-950">
    <nav class="border-b border-white/40 backdrop-blur-md fixed top-0 left-0 w-full z-50">
        <div class="flex items-center justify-between p-4">
            <div class="flex flex-row gap-2 items-center">
                <a
                    href="dashboard.php"
                    onclick="if (history.length > 1) { history.back(); return false; }"
                    aria-label="Kembali ke halaman sebelumnya"
                    class="w-10 h-10 flex items-center justify-center rounded-xl text-white hover:bg-white/10 transition duration-300"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
                </a>
                <img
                    src="../../public/assets/images/logo.svg"
                    alt="Logo PUTU"
                    class="w-10 h-10 p-2 text-emerald-950 bg-white rounded-xl"
                />
                <a href="dashboard.php" class="text-stone-100 text-2xl font-extrabold tracking-tighter">
                    putu<span class="text-lime-500">.</span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="dashboard.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Beranda</a>
                <a href="tugas.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Tugas</a>
                <a href="jadwal.php" aria-current="page" class="bg-emerald-200 text-emerald-950 font-semibold tracking-tighter py-2 px-5 rounded-lg">Jadwal</a>
                <a href="pengumuman.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Pengumuman</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-20 pt-32 pb-20">
        <section class="mx-auto lg:max-w-3xl w-full">
            <h1 class="text-5xl leading-tight tracking-tighter text-stone-100">
                Jadwal kuliah
                <br>
                <span class="text-[#A0CBB5]">Kelas <?= e($kelas) ?>.</span>
            </h1>

            <div class="mt-8 grid grid-cols-2 gap-4">
                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-5">
                    <p class="text-4xl font-extrabold tracking-tighter text-stone-100"><?= $totalMatkul ?></p>
                    <p class="mt-1 text-sm text-emerald-100/70">Mata kuliah</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-5">
                    <p class="text-4xl font-extrabold tracking-tighter text-stone-100"><?= count($jadwal) ?></p>
                    <p class="mt-1 text-sm text-emerald-100/70">Hari kuliah</p>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-8">
                <?php foreach ($jadwal as $hari => $daftar): ?>
                    <section aria-labelledby="hari-<?= e(strtolower($hari)) ?>">
                        <div class="flex items-center gap-3">
                            <h2 id="hari-<?= e(strtolower($hari)) ?>" class="text-2xl font-extrabold tracking-tighter text-stone-100"><?= e($hari) ?></h2>
                            <?php if ($hari === $hariIni): ?>
                                <span class="rounded-full bg-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-950">Hari ini</span>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3 flex flex-col gap-3">
                            <?php foreach ($daftar as $j): ?>
                                <article class="flex items-start gap-5 rounded-2xl border bg-emerald-900/40 p-5 <?= $hari === $hariIni ? 'border-emerald-200/60' : 'border-white/10' ?>">
                                    <div class="w-16 shrink-0 border-r border-white/20 pr-5">
                                        <p class="text-lg font-extrabold tracking-tighter text-stone-100"><?= e($j['mulai']) ?></p>
                                        <p class="text-sm text-emerald-100/70"><?= e($j['selesai']) ?></p>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-extrabold tracking-tighter text-stone-100"><?= e($j['matkul']) ?></h3>
                                        <p class="mt-1 text-sm text-emerald-100/70"><?= e($j['ruang']) ?></p>
                                        <p class="mt-2 inline-block rounded-full bg-emerald-200/10 px-3 py-1 text-xs font-semibold text-emerald-200"><?= e($j['jenis']) ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>