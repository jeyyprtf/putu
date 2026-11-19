<?php
/**
 * Halaman Admin — Tambah Tugas.
 *
 * 1. Guard admin
 * 2. Proses POST
 * 3. Muat data
 * 4. Render HTML
 */

$page = 'admin-tugas';
$title = 'Tambah tugas';

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../admin_tugas.php';

$user = require_login();
$idUser = (int) $user['id_user'];
$kelasId = (int) $user['id_kelas'];

if (($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Akses hanya untuk administrator kelas.');
}

$judul = '';
$matkulId = 0;
$deskripsi = '';
$deadline = '';
$jenis = 'Tugas';

$jenisPilihan = ['Tugas', 'Praktikum', 'Proyek', 'Kuis', 'Ujian'];

$error = '';
$galatMuat = '';
$ok = ($_GET['ok'] ?? '') === 'terbit';

$kelas = null;
$matkul = [];

// ------------------------------------------------ Aksi POST

if (is_post()) {
    $judul = post_str('judul');
    $matkulId = (int) post_str('id_matkul');
    $deskripsi = post_str('deskripsi');
    $deadline = post_str('deadline');
    $jenis = post_str('jenis');

    try {
        if (!csrf_verify()) {
            $error = 'Sesi form kedaluwarsa. Muat ulang halaman.';
        } else {
            admin_tugas_simpan(
                $kelasId,
                $matkulId,
                $judul,
                $deskripsi,
                $deadline,
                $jenis
            );

            redirect(url('admin/tugas', ['ok' => 'terbit']));
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log('[admin_tugas] ' . $e->getMessage());
        $error = 'Tugas gagal diterbitkan. Coba lagi.';
    }
}

// ------------------------------------------------ Muat data

try {
    $kelas = admin_tugas_kelas($kelasId);
    $matkul = admin_tugas_matkul($kelasId);
} catch (Throwable $e) {
    error_log('[admin_tugas] muat: ' . $e->getMessage());
    $galatMuat = 'Data mata kuliah belum bisa dimuat.';
}

$labelKelas = trim((string) ($kelas['nama_kelas'] ?? ''));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../../components/head.php'; ?>
</head>

<body class="bg-stone-950 text-stone-100 font-sans antialiased">

    <a href="#konten"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 button">
        Lewati navigasi
    </a>

    <?php require __DIR__ . '/../../components/sidebar.php'; ?>

    <div class="lg:ml-68">

        <?php require __DIR__ . '/../../components/navbar.php'; ?>

        <main id="konten"
              class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10 lg:pt-10">

            <!-- KEMBALI -->
            <a href="<?= e(url('tugas')) ?>"
               class="mb-8 inline-flex items-center gap-2 text-sm font-medium text-stone-400 transition hover:text-stone-100">
                <span>←</span>
                Daftar tugas
            </a>

            <!-- HEADER -->
            <section class="mb-8">
                <p class="mb-3 text-xs font-bold uppercase tracking-wide text-lime-300">
                    Administrator
                </p>

                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    Tambah tugas<span class="text-lime-300">.</span>
                </h1>

                <p class="mt-3 text-sm text-stone-400">
                    Berikan petunjuk yang jelas untuk kelas
                    <?= e($labelKelas !== '' ? $labelKelas : 'Anda') ?>.
                </p>
            </section>

            <!-- PESAN -->
            <?php if ($ok): ?>
                <div role="status"
                    class="mb-5 rounded-2xl border border-lime-300/30 bg-lime-300/10 px-4 py-3 text-sm text-lime-200">
                    Tugas berhasil diterbitkan.
                </div>
            <?php endif; ?>

            <?php if ($error !== '' || $galatMuat !== ''): ?>
                <div role="alert"
                     class="mb-5 rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-sm text-red-300">
                    <?= e($error !== '' ? $error : $galatMuat) ?>
                </div>
            <?php endif; ?>

            <!-- FORM TUGAS -->
            <form method="POST"
                  class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_21rem]">

                <?= csrf_field() ?>

                <!-- INFORMASI TUGAS -->
                <section class="panel p-5 sm:p-7">
                    <h2 class="mb-6 text-xl font-semibold">
                        Informasi tugas
                    </h2>

                    <div class="space-y-6">

                        <div>
                            <label for="judul" class="mb-2 block text-sm">
                                Judul *
                            </label>

                            <input
                                id="judul"
                                name="judul"
                                type="text"
                                required
                                maxlength="200"
                                value="<?= e($judul) ?>"
                                placeholder="Masukkan judul tugas"
                                class="field w-full"
                            >
                        </div>

                        <div>
                            <label for="id_matkul" class="mb-2 block text-sm">
                                Mata kuliah *
                            </label>

                            <select
                                id="id_matkul"
                                name="id_matkul"
                                required
                                class="field w-full"
                            >
                                <option value="">Pilih mata kuliah</option>

                                <?php foreach ($matkul as $m): ?>
                                    <option
                                        value="<?= (int) $m['id_matkul'] ?>"
                                        <?= $matkulId === (int) $m['id_matkul'] ? 'selected' : '' ?>
                                    >
                                        <?= e($m['nama_matkul']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="deskripsi" class="mb-2 block text-sm">
                                Petunjuk pengerjaan
                            </label>

                            <textarea
                                id="deskripsi"
                                name="deskripsi"
                                rows="10"
                                placeholder="Tuliskan petunjuk pengerjaan tugas..."
                                class="field w-full resize-y leading-relaxed"
                            ><?= e($deskripsi) ?></textarea>
                        </div>

                    </div>
                </section>

                <!-- PANEL KANAN -->
                <div class="space-y-5">

                    <section class="panel p-5 sm:p-6">

                        <h2 class="mb-6 text-xl font-semibold">
                            Tenggat dan jenis
                        </h2>

                        <div class="space-y-6">

                            <div>
                                <label for="deadline" class="mb-2 block text-sm">
                                    Deadline · tanggal &amp; waktu *
                                </label>

                                <input
                                    id="deadline"
                                    name="deadline"
                                    type="datetime-local"
                                    required
                                    value="<?= e($deadline) ?>"
                                    class="field w-full"
                                >

                                <p class="mt-3 text-xs leading-relaxed text-stone-500">
                                    Zona waktu WIB · tentukan tenggat sebelum diterbitkan.
                                </p>
                            </div>

                            <div>
                                <label for="jenis" class="mb-2 block text-sm">
                                    Jenis tugas
                                </label>

                                <select
                                    id="jenis"
                                    name="jenis"
                                    class="field w-full"
                                >
                                    <?php foreach ($jenisPilihan as $pilihan): ?>
                                        <option
                                            value="<?= e($pilihan) ?>"
                                            <?= $jenis === $pilihan ? 'selected' : '' ?>
                                        >
                                            <?= e($pilihan) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- QUICK SELECT -->
                                <div class="mt-4 flex flex-wrap gap-2"
                                     role="group"
                                     aria-label="Pilih jenis tugas">

                                    <?php foreach ($jenisPilihan as $pilihan): ?>
                                        <button
                                            type="button"
                                            data-jenis="<?= e($pilihan) ?>"
                                            aria-pressed="false"
                                            class="rounded-xl border border-stone-700 px-3 py-2 text-xs font-medium text-stone-400 transition hover:border-stone-500 hover:text-white"
                                        >
                                            <?= e($pilihan) ?>
                                        </button>
                                    <?php endforeach; ?>

                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- TERBITKAN -->
                    <section class="panel p-5 sm:p-6">

                        <h2 class="mb-4 text-xl font-semibold">
                            Terbitkan ke kelas
                        </h2>

                        <p class="mb-5 text-xs leading-relaxed text-stone-400">
                            * Wajib diisi. Tugas yang diterbitkan akan muncul di daftar tugas mahasiswa.
                        </p>

                        <div class="space-y-3">

                            <button
                                type="submit"
                                <?= $galatMuat !== '' || $matkul === [] ? 'disabled' : '' ?>
                                class="button w-full justify-center bg-lime-500 py-3 text-stone-950 hover:bg-lime-400 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Terbitkan tugas
                            </button>

                            <a
                                href="<?= e(url('tugas')) ?>"
                                class="button button-secondary w-full justify-center py-3"
                            >
                                Batal
                            </a>

                        </div>

                    </section>

                </div>
            </form>
        </main>
    </div>

    <?php require __DIR__ . '/../../components/dock.php'; ?>

    <script>
        (() => {
            const select = document.getElementById('jenis');
            const buttons = [...document.querySelectorAll('[data-jenis]')];

            if (!select) return;

            const update = () => {
                buttons.forEach((button) => {
                    const active = button.dataset.jenis === select.value;

                    button.setAttribute('aria-pressed', String(active));

                    button.classList.toggle('border-lime-300', active);
                    button.classList.toggle('bg-lime-300/10', active);
                    button.classList.toggle('text-lime-200', active);

                    button.classList.toggle('border-stone-700', !active);
                    button.classList.toggle('text-stone-400', !active);
                });
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    select.value = button.dataset.jenis;
                    update();
                });
            });

            select.addEventListener('change', update);
            update();
        })();
    </script>

</body>
</html>