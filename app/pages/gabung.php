
<?php
/**
 * Halaman Gabung / Buat Kelas.
 *
 * 1. Guard: wajib login, belum memiliki kelas
 * 2. Pilih mode: join / create
 * 3. Proses POST
 * 4. Render halaman
 *
 * Semua fungsi database tetap menggunakan app/kelas.php.
 */

$page = 'gabung';
$title = 'Gabung kelas';

require_once __DIR__ . '/../bootstrap.php';

// ------------------------------------------------ Guard

$user = require_login(false);

if ($user['id_kelas'] !== null) {
    redirect(url('dashboard'));
}

$idUser = (int) $user['id_user'];
$userName = $user['nama'] ?? 'Mahasiswa';

// ------------------------------------------------ Mode

// Default: Gabung kelas
$modeRaw = $_GET['mode'] ?? 'join';
$mode = is_string($modeRaw) && $modeRaw === 'create'
    ? 'create'
    : 'join';

// ------------------------------------------------ Form state

$notice = '';
$noticeIsError = false;

$className = '';
$classCode = '';

// ------------------------------------------------ Aksi POST

if (is_post()) {
    try {
        if (!csrf_verify()) {
            $notice = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
            $noticeIsError = true;

        } elseif ($mode === 'create') {

            $className = post_str('name');
            $len = text_length($className);

            if ($len === null || $len < 3 || $len > 100) {
                $notice = 'Nama kelas harus 3–100 karakter.';
                $noticeIsError = true;

            } elseif (kelas_buat($idUser, $className) === null) {
                $notice = 'Kamu sudah tergabung di sebuah kelas.';
                $noticeIsError = true;

            } else {
                redirect(url('dashboard'));
            }

        } else {

            $classCode = strtoupper(
                preg_replace('/[\s-]+/', '', post_str('code'))
            );

            if ($classCode === '' || strlen($classCode) > 16) {
                $notice = 'Kode kelas wajib diisi.';
                $noticeIsError = true;

            } elseif (!kelas_gabung($idUser, $classCode)) {
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

    <!-- LAYOUT UTAMA -->
    <div class="grid min-h-dvh lg:grid-cols-2">

        <!-- VISUAL KIRI: TETAP -->
        <?php require __DIR__ . '/../components/auth-visual.php'; ?>

        <!-- KONTEN KANAN -->
        <div class="relative flex min-h-dvh flex-col px-6 py-6 sm:px-10 lg:px-12">

            <!-- TOP NAVIGATION -->
            <div class="flex items-center justify-between gap-3">

                <a
                    href="<?= e(url()) ?>"
                    class="flex min-h-11 items-center gap-2 text-xs text-stone-400 transition hover:text-lime-200"
                >
                    <?= icon('back', 'size-4') ?>
                    Kembali ke beranda
                </a>

                <a
                    href="<?= e(url()) ?>"
                    class="text-2xl font-extrabold tracking-tighter lg:hidden"
                    aria-label="PUTU - Beranda"
                >
                    putu<span class="text-lime-500">.</span>
                </a>

            </div>

            <!-- MAIN CONTENT -->
            <main class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10 sm:py-14">

                <!-- HEADER -->
                <section class="mb-7">

                    <p class="eyebrow">
                        Selamat datang, <?= e($userName) ?>
                    </p>

                    <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                        Mulai dari kelasmu<span class="text-lime-300">.</span>
                    </h1>

                    <p class="mt-3 text-sm leading-7 text-stone-400">
                        Gabung bersama temanmu atau buat ruang kelas baru.
                        Tugas, jadwal, dan pengumuman akan muncul setelah
                        kamu memiliki kelas.
                    </p>

                </section>

                <!-- TAB PILIHAN -->
                <nav
                    aria-label="Pilih cara memulai kelas"
                    class="mb-5 flex flex-wrap items-center gap-2"
                >

                    <!-- TAB GABUNG -->
                    <a
                        href="<?= e(url('gabung', ['mode' => 'join'])) ?>"
                        <?= $mode === 'join'
                            ? 'aria-current="page"'
                            : '' ?>
                        class="inline-flex min-h-10 items-center justify-center
                               rounded-xl border px-4 py-2
                               text-xs font-medium transition
                               <?= $mode === 'join'
                                   ? 'border-lime-300/50 bg-lime-300/10 text-lime-100'
                                   : 'border-stone-800 bg-stone-900 text-stone-400 hover:border-stone-600 hover:text-stone-100' ?>"
                    >
                        Gabung kelas
                    </a>

                    <!-- TAB BUAT -->
                    <a
                        href="<?= e(url('gabung', ['mode' => 'create'])) ?>"
                        <?= $mode === 'create'
                            ? 'aria-current="page"'
                            : '' ?>
                        class="inline-flex min-h-10 items-center justify-center
                               rounded-xl border px-4 py-2
                               text-xs font-medium transition
                               <?= $mode === 'create'
                                   ? 'border-lime-300/50 bg-lime-300/10 text-lime-100'
                                   : 'border-stone-800 bg-stone-900 text-stone-400 hover:border-stone-600 hover:text-stone-100' ?>"
                    >
                        Buat kelas
                    </a>

                </nav>

                <!-- NOTIFIKASI -->
                <?php if ($notice !== ''): ?>
                    <div
                        role="<?= $noticeIsError ? 'alert' : 'status' ?>"
                        class="mb-5 rounded-2xl border px-4 py-3 text-sm leading-6
                               <?= $noticeIsError
                                   ? 'border-red-300/30 bg-red-300/10 text-red-300'
                                   : 'border-lime-300/30 bg-lime-300/10 text-lime-200' ?>"
                    >
                        <?= e($notice) ?>
                    </div>
                <?php endif; ?>

                <!-- FORM BERDASARKAN MODE -->
                <?php if ($mode === 'join'): ?>

                    <!-- ================================ -->
                    <!-- GABUNG KELAS                     -->
                    <!-- ================================ -->

                    <section
                        aria-labelledby="join-title"
                        class="panel p-5 sm:p-7"
                    >

                        <div class="mb-6">

                            <h2
                                id="join-title"
                                class="text-xl font-semibold tracking-tight"
                            >
                                Gabung dengan kode undangan
                            </h2>

                            <div
                                class="mt-3 flex size-5 items-center justify-center text-lime-400"
                                aria-hidden="true"
                            >
                                <?= icon('key', 'size-5') ?>
                            </div>

                        </div>

                        <p class="mb-5 text-sm leading-6 text-stone-400">
                            Minta kode kelas dari admin atau teman sekelasmu.
                        </p>

                        <form
                            action="<?= e(url('gabung', ['mode' => 'join'])) ?>"
                            method="POST"
                            class="space-y-5"
                        >

                            <?= csrf_field() ?>

                            <div>

                                <label
                                    for="class-code"
                                    class="mb-2 block text-xs font-semibold"
                                >
                                    Kode undangan kelas
                                </label>

                                <input
                                    id="class-code"
                                    name="code"
                                    type="text"
                                    required
                                    maxlength="16"
                                    placeholder="Masukkan kode dari admin"
                                    autocomplete="off"
                                    autocapitalize="characters"
                                    spellcheck="false"
                                    value="<?= e($classCode) ?>"
                                    class="field uppercase tracking-[0.12em]"
                                >

                            </div>

                            <button
                                type="submit"
                                class="button w-full justify-center"
                            >
                                Gabung kelas
                            </button>

                        </form>

                    </section>

                    <!-- INFORMASI BAWAH -->
                    <aside class="mt-5 rounded-2xl bg-stone-900 px-4 py-4">

                        <p class="text-xs leading-6 text-stone-400">
                            Sudah bergabung? Kelasmu akan menampilkan
                            tugas, jadwal, dan pengumuman yang tersedia.
                        </p>

                    </aside>

                <?php else: ?>

                    <!-- ================================ -->
                    <!-- BUAT KELAS                       -->
                    <!-- ================================ -->

                    <section
                        aria-labelledby="create-title"
                        class="panel p-5 sm:p-7"
                    >

                        <div class="mb-6">

                            <h2
                                id="create-title"
                                class="text-xl font-semibold tracking-tight"
                            >
                                Ruang baru untuk kelasmu
                            </h2>

                            <div
                                class="mt-5 flex size-6 items-center justify-center text-lime-400"
                                aria-hidden="true"
                            >
                                <?= icon('users', 'size-5') ?>
                            </div>

                        </div>

                        <form
                            action="<?= e(url('gabung', ['mode' => 'create'])) ?>"
                            method="POST"
                            class="space-y-6"
                        >

                            <?= csrf_field() ?>

                            <!-- NAMA KELAS -->
                            <div>

                                <label
                                    for="class-name"
                                    class="mb-2 block text-xs font-semibold"
                                >
                                    Nama kelas
                                </label>

                                <input
                                    id="class-name"
                                    name="name"
                                    type="text"
                                    required
                                    minlength="3"
                                    maxlength="100"
                                    placeholder="Contoh: SIB · 2B"
                                    autocomplete="off"
                                    value="<?= e($className) ?>"
                                    class="field"
                                >

                            </div>

                            <!-- PENJELASAN -->
                            <p class="text-xs leading-6 text-stone-400">
                                Kamu akan menjadi admin kelas dan
                                dapat membagikan kode undangan setelah
                                kelas berhasil dibuat.
                            </p>

                            <!-- SUBMIT -->
                            <button
                                type="submit"
                                class="button w-full justify-center"
                            >
                                Buat kelas
                            </button>

                        </form>

                    </section>

                <?php endif; ?>

            </main>

            <!-- FOOTER -->
            <footer class="text-center text-[10px] leading-5 text-stone-500">
                © <?= date('Y') ?> PUTU. Make room for what matters.
            </footer>

        </div>

    </div>

</body>
</html>
