<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUTU | Masuk</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require __DIR__ . '/../components/fonts.php'; ?>
</head>

<body class="font-sans antialiased min-h-screen bg-[#F7F9EF] flex items-center justify-center p-6">

    <a
        href="../../index.php"
        onclick="if (history.length > 1) { history.back(); return false; }"
        aria-label="Kembali ke halaman sebelumnya"
        class="fixed top-6 left-6 w-11 h-11 flex items-center justify-center rounded-xl bg-white text-[#1B3B2B] shadow-sm hover:bg-[#EAF4EC] transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m6-6-6 6 6 6"></path>
        </svg>
    </a>

    <main class="w-full max-w-[390px] bg-white rounded-3xl p-7 shadow-sm">

        <div class="w-12 h-12 flex items-center justify-center bg-[#EAF4EC] rounded-2xl text-[#1B3B2B] mb-7">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                </path>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-[#1B3B2B] tracking-tight">
            Senang kamu kembali.
        </h1>

        <p class="mt-3 mb-8 text-sm leading-6 text-gray-500">
            Masuk dan lihat apa yang perlu kamu fokuskan hari ini.
        </p>

        <form action="#" method="POST">

            <label for="email" class="block text-sm font-semibold text-[#1B3B2B] mb-2">
                Email
            </label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Masukkan email kamu"
                autocomplete="email"
                required
                class="w-full rounded-2xl border border-gray-200 px-4 py-3.5 text-sm outline-none focus:border-[#1B3B2B] mb-5">

            <label for="password" class="block text-sm font-semibold text-[#1B3B2B] mb-2">
                Password
            </label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password kamu"
                autocomplete="current-password"
                required
                class="w-full rounded-2xl border border-gray-200 px-4 py-3.5 text-sm outline-none focus:border-[#1B3B2B]">

            <a href="#" class="mt-3 inline-block text-sm font-semibold text-[#1B3B2B] hover:underline">
                Lupa password?
            </a>

            <button
                type="submit"
                class="w-full mt-7 rounded-2xl bg-[#1B3B2B] px-4 py-4 text-sm font-semibold text-white hover:bg-[#254C38] transition">
                Masuk ke PUTU
            </button>

        </form>

        <p class="mt-7 text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="register.php" class="font-semibold text-[#1B3B2B] hover:underline">
                Daftar sekarang
            </a>
        </p>

    </main>

</body>
</html>