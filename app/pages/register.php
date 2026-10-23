<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUTU | Daftar</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php require __DIR__ . '/../components/fonts.php'; ?>
</head>

<body class="font-sans antialiased min-h-screen bg-stone-950 text-stone-100 flex items-center justify-center p-6">

    <main class="w-full flex flex-col gap-3 max-w-[390px] bg-stone-900 border border-stone-800 rounded-3xl p-7 shadow-sm">

        <a href="../../index.php" class="flex gap-2">    
            <h1 class="text-stone-100 text-5xl font-extrabold tracking-tighter">
                putu<span class="text-lime-500">.</span>
            </h1>
        </a>

        <div class="h-[1px] w-full bg-white/30 max-w-7xl mx-auto"></div>

        <div class="mt-3 flex flex-col gap-2">
            <h1 class="text-3xl font-semibold text-stone-100 tracking-tight">
                Mulai perjalananmu.
            </h1>
    
            <p class="text-sm leading-6 text-stone-400 max-w-[70%]">
                Buat akun PUTU dan atur semua tugas kuliahmu dalam satu tempat.
            </p>
        </div>

        <form action="#" method="POST">

            <label for="nama" class="block text-sm font-semibold text-stone-100 mb-2">
                Nama lengkap
            </label>
            <input
                type="text"
                id="nama"
                name="nama"
                placeholder="Masukkan nama lengkap"
                autocomplete="name"
                required
                class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3.5 text-sm text-stone-100 placeholder:text-stone-500 outline-none focus:border-lime-300 focus:ring-1 focus:ring-lime-300 mb-5">

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
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                minlength="8"
                required
                class="w-full rounded-2xl border border-stone-700 bg-stone-950 px-4 py-3.5 text-sm text-stone-100 placeholder:text-stone-500 outline-none focus:border-lime-300 focus:ring-1 focus:ring-lime-300 mb-5">

            <button
                type="submit"
                class="w-full mt-7 rounded-2xl bg-lime-500 px-4 py-4 text-sm font-semibold text-stone-950 hover:bg-lime-300 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-lime-300 focus:ring-offset-1">
                Buat akun PUTU
            </button>

        </form>

        <p class="mt-7 text-center text-sm text-stone-400">
            Sudah punya akun?
            <a href="login.php" class="font-semibold text-lime-500 hover:text-lime-300 hover:underline">
                Masuk sekarang
            </a>
        </p>

    </main>

</body>
</html>