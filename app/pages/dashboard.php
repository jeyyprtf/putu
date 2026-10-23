<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require dirname(__DIR__) . '/components/fonts.php'; ?>
    <link rel="stylesheet" href="../../public/assets/css/app.css">
    <title>PUTU | Beranda</title>
</head>
<body class="font-sans antialiased bg-stone-950 text-stone-100">

    <!-- Navbar -->
    <nav class="w-full sticky top-0 z-50 border-b border-stone-800  backdrop-blur-sm">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 p-4">
            <a href="dashboard.php" class="flex items-center gap-2">
                <span class="hidden text-4xl font-extrabold tracking-tighter text-stone-100 sm:inline">putu<span class="text-lime-500">.</span></span>
            </a>

            
    </nav>

    <main class="min-h-screen px-5 py-12 sm:px-6 lg:py-16">
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-12 lg:gap-16">

            <!-- Sapaan -->
            <section>
                <p class="text-xs font-semibold uppercase tracking-wide text-lime-500">Beranda</p>
                <h1 class="mt-2 text-3xl font-semibold text-stone-100">Halo, Juan.</h1>
                <p class="mt-2 text-sm leading-6 text-stone-400">Yuk, buat hari ini sedikit lebih teratur.</p>
            </section>

            <!-- Ringkasan -->
            <section class="grid grid-cols-2 gap-6 lg:grid-cols-2">
                <div class="rounded-3xl border border-stone-800 bg-stone-900 p-5 sm:p-6">
                    <p class="text-xs font-semibold text-stone-400">Tugas aktif</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-100">03</p>
                </div>
                <div class="rounded-3xl border border-stone-800 bg-stone-900 p-5 sm:p-6">
                    <p class="text-xs font-semibold text-stone-400">Perlu diprioritaskan</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-100">01</p>
                </div>
                <div class="rounded-3xl border border-stone-800 bg-stone-900 p-5 sm:p-6">
                    <p class="text-xs font-semibold text-stone-400">Sedang dikerjakan</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-100">01</p>
                </div>
                <div class="rounded-3xl border border-stone-800 bg-stone-900 p-5 sm:p-6">
                    <p class="text-xs font-semibold text-stone-400">Sudah selesai</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-100">00</p>
                </div>
            </section>

            <!-- Tugas dan agenda -->
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                <div class="rounded-3xl border border-stone-800 bg-stone-900 p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-semibold text-stone-100">Mulai dari yang terdekat</h2>
                        <a href="tugas.php" class="text-sm font-semibold text-lime-500 hover:text-lime-300 hover:underline">Lihat semua &rarr;</a>
                    </div>
                    <div class="mt-5 flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-stone-800 bg-stone-950 p-4">
                            <div>
                                <p class="text-sm font-semibold text-stone-100">Normalisasi database minimarket</p>
                                <p class="mt-1 text-xs leading-4 text-stone-400">Basis Data, 16 Okt</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-red-950 px-3 py-1 text-xs font-semibold text-red-300">Terlambat</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-stone-800 bg-stone-950 p-4">
                            <div>
                                <p class="text-sm font-semibold text-stone-100">Program inheritance Java</p>
                                <p class="mt-1 text-xs leading-4 text-stone-400">Pemrograman Berorientasi Objek, 22 Okt</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-blue-950 px-3 py-1 text-xs font-semibold text-blue-300">Dikerjakan</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-stone-800 bg-stone-900 p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-semibold text-stone-100">Agenda kuliah</h2>
                        <a href="jadwal.php" class="text-sm font-semibold text-lime-500 hover:text-lime-300 hover:underline">Jadwal lengkap &rarr;</a>
                    </div>
                    <div class="mt-5 flex flex-col gap-3">
                        <div class="rounded-2xl border border-stone-800 bg-stone-950 p-4">
                            <p class="text-sm font-semibold text-stone-100">Basis Data</p>
                            <p class="mt-1 text-xs leading-4 text-stone-400">08.00-09.40, Ruang TI</p>
                        </div>
                        <div class="rounded-2xl border border-stone-800 bg-stone-950 p-4">
                            <p class="text-sm font-semibold text-stone-100">Pemrograman Berorientasi Objek</p>
                            <p class="mt-1 text-xs leading-4 text-stone-400">10.00-11.40, Laboratorium</p>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </main>
</body>
</html>