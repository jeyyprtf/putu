<p class="eyebrow">Putu / Academic workspace</p>
<h1 class="mt-4 text-3xl font-semibold tracking-tighter sm:text-4xl"><?= $isRegister ? 'Mulai perjalananmu.' : 'Senang kamu kembali.' ?></h1>
<p class="mt-3 text-sm leading-7 text-stone-400"><?= $isRegister ? 'Satu ruang untuk hari kuliah yang lebih teratur. Mulai dari sini.' : 'Masuk dan lihat apa yang perlu kamu fokuskan hari ini.' ?></p>
<form action="<?= e(url($isRegister ? 'register' : 'login')) ?>" method="POST" data-auth-form class="mt-8 space-y-5">
    <?php if ($isRegister): ?>
        <div><label for="nama" class="mb-2 block text-xs font-semibold">Nama lengkap</label><input id="nama" name="nama" type="text" autocomplete="name" required maxlength="100" placeholder="Siapa nama kamu?" class="field"></div>
    <?php endif; ?>
    <div><label for="email" class="mb-2 block text-xs font-semibold">Email</label><input id="email" name="email" type="email" autocomplete="email" required maxlength="254" placeholder="nama@email.com" class="field"></div>
    <div>
        <label for="password" class="mb-2 block text-xs font-semibold">Password</label>
        <div class="relative"><input id="password" name="password" type="password" autocomplete="<?= $isRegister ? 'new-password' : 'current-password' ?>" <?= $isRegister ? 'minlength="8" aria-describedby="password-help"' : '' ?> required placeholder="<?= $isRegister ? 'Minimal 8 karakter' : 'Masukkan password kamu' ?>" class="field pr-14!"><button type="button" data-password-toggle aria-label="Tampilkan password" aria-controls="password" aria-pressed="false" class="absolute top-0 right-1 flex size-12 items-center justify-center rounded-xl text-stone-400 hover:text-lime-200"><?= icon('eye', 'size-4') ?></button></div>
        <?php if ($isRegister): ?><p id="password-help" class="mt-2 text-xs text-stone-400">Gunakan minimal 8 karakter.</p><?php endif; ?>
    </div>
    <?php if (!$isRegister): ?><div class="text-right"><button type="button" data-forgot-password class="min-h-11 text-xs text-lime-300 hover:text-lime-200">Lupa password?</button></div><?php endif; ?>
    <button type="submit" class="button w-full"><?= $isRegister ? 'Buat akun PUTU' : 'Masuk ke PUTU' ?><?= icon('arrow', 'size-4') ?></button>
    <p id="auth-feedback" role="status" aria-live="polite" class="text-xs leading-6 text-amber-200" <?= ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' ? 'hidden' : '' ?>>Autentikasi belum terhubung. Kamu bisa menjelajahi pratinjau di bawah ini; data form tidak disimpan.</p>
</form>
<p class="mt-6 text-center text-xs leading-6 text-stone-400"><?= $isRegister ? 'Sudah punya akun?' : 'Belum punya akun?' ?> <a href="<?= e(url($isRegister ? 'login' : 'register')) ?>" class="font-semibold text-lime-300 hover:text-lime-200"><?= $isRegister ? 'Masuk sekarang' : 'Daftar sekarang' ?></a></p>
<div class="mt-8 border-t border-stone-800 pt-6"><a href="<?= e(url('dashboard')) ?>" class="flex min-h-11 items-center justify-center gap-2 rounded-2xl border border-stone-800 px-4 text-xs text-stone-300 hover:border-stone-600 hover:text-lime-200">Jelajahi pratinjau mahasiswa <?= icon('arrow', 'size-4') ?></a><p class="mt-3 text-center text-[10px] leading-5 text-stone-500">Pratinjau frontend · Belum membuat atau masuk ke akun.</p></div>
