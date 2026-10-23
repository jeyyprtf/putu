<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <link rel="stylesheet" href="../../public/assets/css/app.css">
    <title>Beranda - PUTULAGI</title>
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
                <a href="dashboard.php" class="text-stone-100 text-2xl font-extrabold tracking-tighter">
                    putu<span class="text-lime-500">.</span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="dashboard.php" class="bg-emerald-200 text-emerald-950 font-semibold tracking-tighter py-2 px-5 rounded-lg">Beranda</a>
                <a href="tugas.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Tugas</a>
                <a href="jadwal.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Jadwal</a>
                <a href="pengumuman.php" class="text-white font-semibold tracking-tighter hover:text-emerald-200 transition duration-300 px-3">Pengumuman</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen px-20 pt-32 pb-20">
        <section class="mx-auto lg:max-w-5xl w-full">
            <h1 class="text-5xl leading-tight tracking-tighter text-stone-100">
                Halo, Juan.
                <br>
                <span class="text-[#A0CBB5]">Yuk, buat hari ini sedikit lebih teratur.</span>
            </h1>

            <div class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-5">
                    <p class="text-sm text-emerald-100/70">Tugas aktif</p>
                    <p class="mt-2 text-4xl font-extrabold tracking-tighter text-stone-100">03</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-5">
                    <p class="text-sm text-emerald-100/70">Perlu diprioritaskan</p>
                    <p class="mt-2 text-4xl font-extrabold tracking-tighter text-stone-100">01</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-5">
                    <p class="text-sm text-emerald-100/70">Sedang dikerjakan</p>
                    <p class="mt-2 text-4xl font-extrabold tracking-tighter text-stone-100">01</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-5">
                    <p class="text-sm text-emerald-100/70">Sudah selesai</p>
                    <p class="mt-2 text-4xl font-extrabold tracking-tighter text-stone-100">00</p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-extrabold tracking-tighter text-stone-100">Tugas terdekat</h2>
                        <a href="tugas.php" class="text-sm font-semibold text-emerald-200 hover:text-white transition duration-300">Lihat semua</a>
                    </div>
                    <div class="mt-4 flex flex-col gap-3">
                        <div class="rounded-xl bg-white/5 p-4">
                            <p class="font-semibold text-stone-100">Normalisasi database minimarket</p>
                            <p class="mt-1 text-sm text-emerald-100/70">Basis Data, 16 Okt</p>
                            <p class="mt-2 text-xs font-semibold text-red-200">Terlambat</p>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4">
                            <p class="font-semibold text-stone-100">Program inheritance Java</p>
                            <p class="mt-1 text-sm text-emerald-100/70">Pemrograman Berorientasi Objek, 22 Okt</p>
                            <p class="mt-2 text-xs font-semibold text-emerald-200">Dikerjakan</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-emerald-900/40 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-extrabold tracking-tighter text-stone-100">Agenda kuliah</h2>
                        <a href="jadwal.php" class="text-sm font-semibold text-emerald-200 hover:text-white transition duration-300">Jadwal lengkap</a>
                    </div>
                    <div class="mt-4 flex flex-col gap-3">
                        <div class="rounded-xl bg-white/5 p-4">
                            <p class="font-semibold text-stone-100">Basis Data</p>
                            <p class="mt-1 text-sm text-emerald-100/70">08.00-09.40, Ruang TI</p>
                        </div>
                        <div class="rounded-xl bg-white/5 p-4">
                            <p class="font-semibold text-stone-100">Pemrograman Berorientasi Objek</p>
                            <p class="mt-1 text-sm text-emerald-100/70">10.00-11.40, Laboratorium</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>