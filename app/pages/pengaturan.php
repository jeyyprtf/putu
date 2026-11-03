<?php
$page = 'pengaturan';
$title = 'Profil & pengaturan';
require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
    </head>
    <body class="bg-stone-950 text-stone-100 font-sans antialiased">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 button">Lewati navigasi</a>
        <?php require __DIR__ . '/../components/sidebar.php'; ?>
        <div class="lg:ml-68">
            <?php require __DIR__ . '/../components/navbar.php'; ?>
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
                <div class="mb-7">
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Profil & pengaturan<span class="text-lime-300">.</span></h1>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Ruang kecil untuk hal-hal tentang kamu.</p>
                </div>
                <div class="grid items-start gap-5 xl:grid-cols-3">
                    <section aria-labelledby="profile-title" class="panel rounded-3xl! min-w-0 overflow-hidden xl:col-span-2">
                        <div class="flex items-center gap-4 border-b border-stone-800 bg-stone-950/30 p-5 sm:p-6">
                            <span class="flex size-16 shrink-0 items-center justify-center rounded-3xl bg-lime-300/15 text-2xl font-semibold text-lime-200"><?= e($profile['initial']) ?></span>
                            <div class="min-w-0">
                                <p class="truncate text-lg font-semibold"><?= e($profile['name']) ?></p>
                                <p class="mt-1 text-xs text-stone-400">Mahasiswa · SIB 2B</p>
                            </div>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h2 id="profile-title" class="text-base font-semibold">Informasi pribadi</h2>
                            <form id="profile-form" action="<?= e(url('pengaturan')) ?>" method="POST" class="mt-6 space-y-5">
                                <div>
                                    <label for="profile-name" class="mb-2 block text-xs font-semibold">Nama lengkap</label>
                                    <input id="profile-name" name="name" type="text" value="<?= e($profile['name']) ?>" required maxlength="80" autocomplete="name" class="field rounded-3xl!">
                                </div>
                                <div>
                                    <label for="profile-email" class="mb-2 block text-xs font-semibold">Email</label>
                                    <input id="profile-email" name="email" type="email" value="<?= e($profile['email']) ?>" required maxlength="254" autocomplete="email" class="field rounded-3xl!">
                                </div>
                                <div class="flex flex-wrap justify-end gap-3 border-t border-stone-800 pt-5">
                                    <button type="reset" class="button rounded-3xl! button-secondary">Batal</button>
                                    <!-- Aktifkan setelah handler POST memiliki autentikasi, validasi server, dan CSRF. -->
                                    <button id="profile-save" type="submit" class="button rounded-3xl! disabled:cursor-not-allowed disabled:opacity-50" aria-describedby="profile-save-hint" disabled><?= icon('check', 'size-4') ?>Simpan perubahan</button>
                                </div>
                                <p id="profile-save-hint" class="text-xs leading-6 text-stone-400">Perubahan profil belum tersedia.</p>
                            </form>
                        </div>
                    </section>
                    <section aria-labelledby="class-title" class="panel rounded-3xl! min-w-0 p-5 sm:p-6">
                        <span class="icon-button rounded-3xl! text-lime-300"><?= icon('book') ?></span>
                        <h2 id="class-title" class="mt-4 text-base font-semibold">Kelas kamu</h2>
                        <p class="mt-2 text-xs leading-6 text-stone-400">Tempat tugas, jadwal, dan kabar kelasmu berkumpul.</p>
                        <dl class="mt-5 space-y-4 border-t border-stone-800 pt-5 text-sm">
                            <div><dt class="text-xs text-stone-400">Program studi</dt><dd class="mt-1.5 font-medium">Sistem Informasi Bisnis</dd></div>
                            <div><dt class="text-xs text-stone-400">Kelas</dt><dd class="mt-1.5 font-medium">SIB · 2B</dd></div>
                            <div><dt class="text-xs text-stone-400">Peran</dt><dd class="mt-1.5 font-medium">Mahasiswa</dd></div>
                        </dl>
                        <a href="<?= e(url('jadwal')) ?>" class="mt-5 flex min-h-11 items-center justify-between gap-2 border-t border-stone-800 pt-4 text-xs font-semibold text-lime-300">Lihat jadwal kelas <?= icon('arrow', 'size-4') ?></a>
                    </section>
                </div>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
