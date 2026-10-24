<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUTU | Masuk</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require __DIR__ . '/../components/fonts.php'; ?>
</head>

<body class="font-sans antialiased min-h-screen bg-neutral-950 text-stone-100 flex items-center justify-center p-6">
    <a href="../../index.php"
        aria-label="Kembali ke beranda"
        class="fixed top-6 left-6 w-11 h-11 flex items-center justify-center rounded-2xl border border-stone-800 bg-stone-900 text-stone-100 hover:bg-stone-800 hover:text-lime-300 transition duration-300">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m6-6-6 6 6 6"></path>
        </svg>
    </a> 
    <main class="w-full flex flex-col gap-4 max-w-[390px] bg-stone-900 border border-stone-800 rounded-3xl p-7 shadow-sm">

        <a href="../../index.php" class="">    
            <h1 class="text-stone-100 text-5xl font-extrabold tracking-tighter">
                putu<span class="text-lime-500">.</span>
            </h1>
        </a>

        <div class="h-[1px] w-full bg-white/30 max-w-7xl mx-auto"></div>

        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-semibold text-stone-100 tracking-tight">
                Senang kamu kembali.
            </h1>
            
            <p class="text-sm leading-6 text-stone-400 max-w-[70%]">
                Masuk dan lihat apa yang perlu kamu fokuskan hari ini.
            </p>
        </div>

        <form action="#" method="POST">

            <label for="email" class="block text-sm font-semibold text-stone-100 mb-2">
                Email
            </label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Masukkan email kamu"
                autocomplete="email"
                required
                class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3.5 text-sm text-stone-100 placeholder:text-stone-500 outline-none focus:border-lime-300 focus:ring-1 focus:ring-lime-300 mb-5">

            <label for="password" class="block text-sm font-semibold text-stone-100 mb-2">
                Password
            </label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password kamu"
                autocomplete="current-password"
                required
                class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3.5 text-sm text-stone-100 placeholder:text-stone-500 outline-none focus:border-lime-300 focus:ring-1 focus:ring-lime-300">

            <div class="w-full flex text-right items-end justify-end">
                <a href="#" class="mt-3 inline-block text-sm font-semibold text-lime-500 hover:text-lime-300 hover:underline">
                    Lupa password?
                </a>
            </div>

            <button
                type="submit"
                class="w-full mt-3 rounded-2xl bg-lime-500 px-4 py-4 text-sm font-semibold text-stone-950 hover:bg-lime-300 transition cursor-pointer">
                Masuk ke PUTU
            </button>

        </form>

        <p class="mt-7 text-center text-sm text-stone-400">
            Belum punya akun?
            <a href="register.php" class="font-semibold text-lime-500 hover:text-lime-300 hover:underline">
                Daftar sekarang
            </a>
        </p>

    </main>

</body>
</html>