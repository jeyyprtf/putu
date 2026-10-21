<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <link rel="stylesheet" href="../../public/assets/css/app.css">
    <title>Pengumuman - PUTULAGI</title>
</head>
<body class="font-sans antialiased bg-emerald-950">
    <nav class="border-b border-white/40 backdrop-blur-md fixed top-0 left-0 w-full z-50">
        <div class="flex items-center justify-between p-4">
            <div class="flex flex-row gap-2 items-center">
                <img
                    src="../../public/assets/images/logo.svg"
                    alt="Logo PUTU"
                    class="w-10 h-10 p-2 text-emerald-950 bg-white rounded-xl"
                />
                <a href="../../index.php" class="text-stone-100 text-2xl font-extrabold tracking-tighter">
                    putu<span class="text-lime-500">.</span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="dashboard.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Beranda</a>
                <a href="tugas.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Tugas</a>
                <a href="jadwal.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Jadwal</a>
                <a href="pengumuman.php" class="bg-emerald-200 text-emerald-950 font-semibold tracking-tighter py-2 px-5 rounded-lg">Pengumuman</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-20 pt-32 pb-20">
        <section class="mx-auto lg:max-w-3xl w-full">
            <h1 class="text-5xl leading-tight tracking-tighter text-stone-100">
                Pengumuman kampus
                <br>
                <span class="text-[#A0CBB5]">Informasi untuk SIB - 2B.</span>
            </h1>

            <div class="mt-10 flex flex-col gap-4">
                <article class="rounded-2xl border border-red-300/40 bg-emerald-900/40 p-6">
                    <p class="text-sm font-semibold text-red-200">Prioritas</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tighter text-stone-100">Perubahan ruang kelas Basis Data</h2>
                    <p class="mt-1 text-sm text-emerald-100/70">Admin Akademik, 20 Okt 2026</p>
                    <p class="mt-3 text-emerald-50/90">Perkuliahan Basis Data hari Kamis dipindahkan ke LPR7, Gedung B. Jam perkuliahan tetap mengikuti jadwal.</p>
                    <a href="#" class="mt-4 inline-block font-semibold tracking-tighter text-emerald-200 hover:text-white transition duration-300">Baca selengkapnya</a>
                </article>

                <article class="rounded-2xl border border-white/10 bg-emerald-900/40 p-6">
                    <p class="text-sm font-semibold text-emerald-200">Kemahasiswaan</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tighter text-stone-100">Saatnya upgrade skill: sertifikasi database</h2>
                    <p class="mt-1 text-sm text-emerald-100/70">Admin Kemahasiswaan, 18 Okt 2026</p>
                    <p class="mt-3 text-emerald-50/90">Pendaftaran sertifikasi database dibuka untuk mahasiswa Sistem Informasi Bisnis. Kuota terbatas.</p>
                    <a href="#" class="mt-4 inline-block font-semibold tracking-tighter text-emerald-200 hover:text-white transition duration-300">Baca selengkapnya</a>
                </article>

                <article class="rounded-2xl border border-white/10 bg-emerald-900/40 p-6">
                    <p class="text-sm font-semibold text-emerald-200">Akademik</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tighter text-stone-100">Persiapan pekan UTS</h2>
                    <p class="mt-1 text-sm text-emerald-100/70">Admin Akademik, 15 Okt 2026</p>
                    <p class="mt-3 text-emerald-50/90">UTS dimulai pekan depan. Cek jadwal ujian dan hadir 15 menit sebelum ujian dimulai.</p>
                    <a href="#" class="mt-4 inline-block font-semibold tracking-tighter text-emerald-200 hover:text-white transition duration-300">Baca selengkapnya</a>
                </article>
            </div>
        </section>
    </main>
</body>
</html>