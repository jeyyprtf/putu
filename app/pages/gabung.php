<?php
$page = 'gabung';
$title = 'Gabung kelas';
require_once __DIR__ . '/../bootstrap.php';

$user = require_login(false); // boleh tanpa kelas, tapi harus login

// Sudah punya kelas: tidak perlu di halaman ini lagi.
if ($user['id_kelas'] !== null) {
    redirect(url('dashboard'));
}

$userName = $user['nama'];

// Mode dibaca dari URL: gabung.php?mode=create atau gabung.php?mode=join
$mode = $_GET['mode'] ?? '';
if (!in_array($mode, ['create', 'join'], true)) {
    $mode = '';
}

$notice = '';
$noticeIsError = false;

if (is_post() && $mode !== '') {
    try {
        if (!csrf_verify()) {
            $notice = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
            $noticeIsError = true;
        } elseif ($mode === 'create') {
            $name = post_str('name');
            $len = text_length($name);

            if ($len === null || $len < 3 || $len > 100) {
                $notice = 'Nama kelas harus 3–100 karakter.';
                $noticeIsError = true;
            } elseif (kelas_buat((int) $user['id_user'], $name) === null) {
                $notice = 'Kamu sudah tergabung di sebuah kelas.';
                $noticeIsError = true;
            } else {
                // Berhasil: user kini admin kelas. Kode gabung tampil di halaman pengaturan.
                redirect(url('dashboard'));
            }
        } else {
            // Kode disimpan huruf besar; spasi/tanda hubung dibuang agar input lebih toleran.
            $code = strtoupper(preg_replace('/[\s-]+/', '', post_str('code')));

            if ($code === '' || strlen($code) > 16) {
                $notice = 'Kode kelas wajib diisi.';
                $noticeIsError = true;
            } elseif (!kelas_gabung((int) $user['id_user'], $code)) {
                $notice = 'Kode kelas tidak ditemukan.';
                $noticeIsError = true;
            } else {
                redirect(url('dashboard'));
            }
        }
    } catch (Throwable $e) {
        error_log('[gabung] ' . $e->getMessage());
        $notice = 'Terjadi kesalahan di server. Coba lagi nanti.';
        $noticeIsError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
    </head>
    <body class="bg-stone-950 font-sans text-stone-100 antialiased">
        <div class="grid lg:grid-cols-2">
            <?php require __DIR__ . '/../components/auth-visual.php'; ?>
            <div class="relative flex flex-col px-6 py-6 sm:px-10 lg:px-12">
                <div class="flex items-center justify-between gap-3">
                    <a href="<?= e(url()) ?>" class="flex min-h-11 items-center gap-2 text-xs text-stone-400 hover:text-lime-200">
                        <?= icon('back', 'size-4') ?>Kembali ke beranda
                    </a>
                    <a href="<?= e(url()) ?>" class="text-2xl font-extrabold tracking-tighter lg:hidden">
                        putu<span class="text-lime-500">.</span>
                    </a>
                </div>
                <main class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-10 sm:py-14">
                    <p class="eyebrow">
                        Putu / Academic workspace
                    </p>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tighter sm:text-4xl">
                        Halo <?= e($userName) ?><span class="text-lime-300">.</span>
                    </h1>
                    <p class="mt-3 text-sm leading-7 text-stone-400">
                        Selamat datang di PUTU. Silahkan join kelas kamu dulu ya! atau kalau kamu admin kelas, kamu bisa buat kelas baru.
                    </p>

                    <?php if ($mode === ''): ?>
                        <div class="mt-4 lg:mt-8 flex w-full flex-col items-center justify-center gap-4 sm:mt-10">
                            <a href="<?= e(url('gabung', ['mode' => 'create'])) ?>" class="group flex min-h-50 w-full flex-col items-center justify-center gap-4 rounded-4xl border border-white/10 bg-lime-700/20 p-8 text-center transition-all duration-300 hover:bg-lime-500">
                                <p class="text-4xl font-bold text-white/80 transition-all duration-300 group-hover:text-white">Buat Kelas</p>
                                <p class="text-base font-semibold text-white/50 transition-all duration-300 group-hover:text-white/80">Jika kamu admin kelas <br> kamu bisa bikin kelas baru disini.</p>
                            </a>
                            <div class="relative flex w-full items-center justify-center">
                                <span class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-white/20"></span>
                                <span class="relative bg-stone-950 px-4 text-white/40">atau</span>
                            </div>
                            <a href="<?= e(url('gabung', ['mode' => 'join'])) ?>" class="group flex min-h-50 w-full flex-col items-center justify-center gap-4 rounded-4xl border border-white/10 bg-stone-700/20 p-8 text-center transition-all duration-300 hover:bg-stone-500/50">
                                <p class="text-4xl font-bold text-white/80 transition-all duration-300 group-hover:text-white">Gabung Kelas</p>
                                <p class="text-base font-semibold text-white/50 transition-all duration-300 group-hover:text-white/80">Masukkan kode invitation dari admin kelas kamu untuk bergabung kelas.</p>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mt-8 w-full sm:mt-10">
                            <a href="<?= e(url('gabung')) ?>" class="mb-6 flex min-h-11 items-center gap-2 text-xs text-stone-400 hover:text-lime-200">
                                <?= icon('back', 'size-4') ?>Pilih menu lain
                            </a>

                            <?php if ($mode === 'create'): ?>
                                <!-- Form: Buat Kelas -->
                                <form action="<?= e(url('gabung', ['mode' => 'create'])) ?>" method="POST" class="space-y-5 rounded-4xl border border-white/10 bg-stone-900/60 p-6 sm:p-8">
                                    <?= csrf_field() ?>
                                    <div>
                                        <h2 class="text-xl font-semibold tracking-tight">Buat Kelas Baru<span class="text-lime-300">.</span></h2>
                                        <p class="mt-1 text-xs leading-6 text-stone-400">Kamu akan menjadi admin kelas ini.</p>
                                    </div>
                                    <div>
                                        <label for="class-name" class="mb-2 block text-xs font-semibold">Nama Kelas</label>
                                        <input id="class-name" name="name" type="text" required maxlength="100" placeholder="Contoh: Basis Data IF-3A" value="<?= e($_POST['name'] ?? '') ?>" class="field">
                                    </div>
                                    <button type="submit" class="button w-full">
                                        Buat Kelas<?= icon('arrow', 'size-4') ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Form: Gabung Kelas -->
                                <form action="<?= e(url('gabung', ['mode' => 'join'])) ?>" method="POST" class="space-y-5 rounded-4xl border border-white/10 bg-stone-900/60 p-6 sm:p-8">
                                    <?= csrf_field() ?>
                                    <div>
                                        <h2 class="text-xl font-semibold tracking-tight">Gabung Kelas<span class="text-lime-300">.</span></h2>
                                        <p class="mt-1 text-xs leading-6 text-stone-400">Minta kode invitation dari admin kelas kamu.</p>
                                    </div>
                                    <div>
                                        <label for="class-code" class="mb-2 block text-xs font-semibold">Kode Kelas</label>
                                        <input id="class-code" name="code" type="text" required maxlength="16" placeholder="Contoh: K7M2X9QA" autocomplete="off" autocapitalize="characters" spellcheck="false" value="<?= e($_POST['code'] ?? '') ?>" class="field uppercase tracking-[0.2em]">
                                    </div>
                                    <button type="submit" class="button w-full">
                                        Gabung Kelas<?= icon('arrow', 'size-4') ?>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($notice !== ''): ?>
                                <p role="status" aria-live="polite" class="mt-4 text-xs leading-6 <?= $noticeIsError ? 'text-red-300' : 'text-lime-200' ?>">
                                    <?= e($notice) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </main>
                <footer class="text-center text-[10px] leading-5 text-stone-500">
                    © <?= date('Y') ?> PUTU. Make room for what matters.
                </footer>
            </div>
        </div>
    </body>
</html>
