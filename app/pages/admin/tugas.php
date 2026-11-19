<?php
$page = 'admin-tugas';

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../admin_tugas.php';

$user = require_login();

$idUser = (int) $user['id_user'];
$kelasId = (int) $user['id_kelas'];

if (($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Akses hanya untuk administrator kelas.');
}

// Mode edit melalui URL ?edit=ID
$editRaw = $_GET['edit'] ?? '';
$editId = is_string($editRaw) && ctype_digit($editRaw)
    ? (int) $editRaw
    : 0;

$isEdit = $editId > 0;

$title = $isEdit ? 'Edit tugas' : 'Tambah tugas';

$judul = '';
$matkulId = 0;
$deskripsi = '';
$deadline = '';
$jenis = 'tugas';

$jenisPilihan = ['tugas', 'praktikum', 'proyek'];

$error = '';
$galatMuat = '';

$okKey = $_GET['ok'] ?? '';
$ok = is_string($okKey) && $okKey === 'terbit';

$kelas = null;
$matkul = [];

// ------------------------------------------------ Aksi POST

if (is_post()) {
    $aksi = post_str('aksi');

    try {
        if (!csrf_verify()) {
            throw new InvalidArgumentException(
                'Sesi form kedaluwarsa. Muat ulang halaman.'
            );
        }

        if ($aksi === 'nonaktifkan') {
            $idRaw = post_str('id_tugas');

            $idTugas = ctype_digit($idRaw)
                ? (int) $idRaw
                : 0;

            if (!admin_tugas_nonaktifkan($kelasId, $idUser, $idTugas)) {
                throw new InvalidArgumentException(
                    'Tugas tidak ditemukan atau tidak dapat dinonaktifkan.'
                );
            }

            redirect(url('tugas', ['ok' => 'nonaktif']));

        } elseif ($aksi === 'simpan') {
            $judul = post_str('judul');

            $matkulRaw = post_str('id_matkul');
            $matkulId = ctype_digit($matkulRaw)
                ? (int) $matkulRaw
                : 0;

            $deskripsi = post_str('deskripsi');
            $deadline = post_str('deadline');
            $jenis = post_str('jenis');

            if ($isEdit) {
                $berhasil = admin_tugas_update(
                    $kelasId, $idUser, $editId, $matkulId, $judul, $deskripsi, $deadline, $jenis
                );

                if (!$berhasil) {
                    throw new InvalidArgumentException(
                        'Tugas tidak ditemukan atau tidak dapat diedit.'
                    );
                }

                redirect(url('tugas', ['ok' => 'edit']));
            }

            admin_tugas_simpan(
                $kelasId, $idUser, $matkulId, $judul, $deskripsi, $deadline, $jenis
            );

            redirect(url('admin/tugas', ['ok' => 'terbit']));

        } else {
            throw new InvalidArgumentException('Aksi tidak dikenal.');
        }

    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();

    } catch (Throwable $e) {
        error_log('[admin_tugas] ' . $e->getMessage());

        $error = 'Operasi tugas gagal. Silakan coba lagi.';
    }
}

// ------------------------------------------------ Muat data

try {
    $kelas = admin_tugas_kelas($kelasId);
    $matkul = admin_tugas_matkul($kelasId);
    if ($isEdit && !is_post()) {
        $tugasEdit = admin_tugas_detail(
            $kelasId, $idUser, $editId
        );
        if (!$tugasEdit) {
            http_response_code(404);
            exit('Tugas tidak ditemukan.');
        }
        $judul = (string) $tugasEdit['judul_tugas'];
        $matkulId = (int) $tugasEdit['id_matkul'];
        $deskripsi = (string) $tugasEdit['deskripsi'];
        $deadline = (string) $tugasEdit['deadline_form'];
        $jenis = (string) $tugasEdit['jenis_tugas'];
    }
} catch (Throwable $e) {
    error_log('[admin_tugas] muat: ' . $e->getMessage());
    $galatMuat = 'Data tugas belum bisa dimuat.';
}

$labelKelas = trim((string) ($kelas['nama_kelas'] ?? ''));

// ------------------------------------------------ Pesan status

$toast = null;

if ($ok) {
    $toast = [
        'type' => 'success',
        'message' => 'Tugas berhasil diterbitkan.',
    ];
} elseif ($error !== '') {
    $toast = [
        'type' => 'error',
        'message' => $error,
    ];
} elseif ($galatMuat !== '') {
    $toast = [
        'type' => 'error',
        'message' => $galatMuat,
    ];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../../components/head.php'; ?>
</head>

<body class="bg-stone-950 text-stone-100 font-sans antialiased">

    <a
        href="#konten"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 button"
    >
        Lewati navigasi
    </a>

    <!-- SIDEBAR -->
    <?php require __DIR__ . '/../../components/sidebar.php'; ?>

    <div class="lg:ml-68">

        <!-- NAVBAR -->
        <?php require __DIR__ . '/../../components/navbar.php'; ?>

        <main
            id="konten"
            class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10 lg:pt-10"
        >

            <!-- KEMBALI -->
            <a
                href="<?= e(url('tugas')) ?>"
                class="mb-8 inline-flex items-center gap-2 text-sm font-medium text-stone-400 transition hover:text-stone-100"
            >
                <span>←</span>
                Daftar tugas
            </a>

            <!-- HEADER -->
            <section class="mb-8">

                <p class="mb-3 text-xs font-bold uppercase tracking-wide text-lime-300">
                    Administrator
                </p>

                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    <?= $isEdit ? 'Edit tugas' : 'Tambah tugas' ?>
                    <span class="text-lime-300">.</span>
                </h1>

                <p class="mt-3 text-sm text-stone-400">
                    Berikan petunjuk yang jelas untuk kelas
                    <?= e($labelKelas !== '' ? $labelKelas : 'Anda') ?>.
                </p>

            </section>

            <!-- NOTIFIKASI -->
            <?php if ($toast !== null): ?>

                <div
                    id="toast"
                    role="<?= $toast['type'] === 'success' ? 'status' : 'alert' ?>"
                    class="mb-5 rounded-2xl border px-4 py-3 text-sm
                        <?= $toast['type'] === 'success'
                            ? 'border-lime-300/30 bg-lime-300/10 text-lime-200'
                            : 'border-red-300/30 bg-red-300/10 text-red-300' ?>"
                >
                    <?= e($toast['message']) ?>
                </div>

            <?php endif; ?>

            <!-- FORM TAMBAH TUGAS -->
            <form
                action="<?= e($isEdit
                    ? url('admin/tugas', ['edit' => $editId])
                    : url('admin/tugas')) ?>"
                method="POST"
                id="form-tugas"
                class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_21rem]"
            >

                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="simpan">

                <!-- INFORMASI TUGAS -->
                <section class="panel p-5 sm:p-7">

                    <h2 class="mb-6 text-xl font-semibold">
                        Informasi tugas
                    </h2>

                    <div class="space-y-6">

                        <!-- JUDUL -->
                        <div>
                            <label
                                for="judul"
                                class="mb-2 block text-sm font-medium"
                            >
                                Judul <span class="text-red-400!">*</span>
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

                        <!-- MATA KULIAH -->
                        <div>
                            <label
                                for="id_matkul"
                                class="mb-2 block text-sm font-medium"
                            >
                                Mata kuliah <span class="text-red-400!">*</span>
                            </label>

                            <select
                                id="id_matkul"
                                name="id_matkul"
                                required
                                class="field w-full"
                            >
                                <option value="">
                                    Pilih mata kuliah
                                </option>

                                <?php foreach ($matkul as $m): ?>

                                    <option
                                        value="<?= (int) $m['id_matkul'] ?>"
                                        <?= $matkulId === (int) $m['id_matkul']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= e($m['nama_matkul']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                        <!-- DESKRIPSI -->
                        <div>
                            <label
                                for="deskripsi"
                                class="mb-2 block text-sm font-medium"
                            >
                                Petunjuk pengerjaan <span class="text-red-400!">*</span>
                            </label>

                            <textarea
                                id="deskripsi"
                                name="deskripsi"
                                rows="10"
                                required
                                placeholder="Tuliskan petunjuk pengerjaan tugas..."
                                class="field w-full resize-y leading-relaxed"
                            ><?= e($deskripsi) ?></textarea>
                        </div>

                    </div>

                </section>

                <!-- PANEL KANAN -->
                <div class="space-y-5">

                    <!-- TENGGAT DAN JENIS -->
                    <section class="panel p-5 sm:p-6">

                        <h2 class="mb-6 text-xl font-semibold">
                            Tenggat dan jenis
                        </h2>

                        <div class="space-y-6">

                            <!-- DEADLINE -->
                            <div>
                                <label
                                    for="deadline"
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Deadline · tanggal &amp; waktu <span class="text-red-400!">*</span>
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

                            <!-- JENIS TUGAS -->
                            <div>
                                <label
                                    for="jenis"
                                    class="mb-2 block text-sm font-medium"
                                >
                                    Jenis tugas <span class="text-red-400!">*</span>
                                </label>

                                <select
                                    id="jenis"
                                    name="jenis"
                                    required
                                    class="field w-full"
                                >
                                    <?php foreach ($jenisPilihan as $pilihan): ?>

                                        <option
                                            value="<?= e($pilihan) ?>"
                                            <?= $jenis === $pilihan
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= e(ucfirst($pilihan)) ?>
                                        </option>

                                    <?php endforeach; ?>
                                </select>

                                <!-- QUICK SELECT -->
                                <div
                                    class="mt-4 flex flex-wrap gap-2"
                                    role="group"
                                    aria-label="Pilih jenis tugas"
                                >

                                    <?php foreach ($jenisPilihan as $pilihan): ?>

                                        <button
                                            type="button"
                                            data-jenis="<?= e($pilihan) ?>"
                                            aria-pressed="false"
                                            class="rounded-xl border border-stone-700 px-3 py-2 text-xs font-medium text-stone-400 transition hover:border-stone-500 hover:text-white"
                                        >
                                            <?= e(ucfirst($pilihan)) ?>
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
                            <span class="text-red-400!">*</span> Wajib diisi. Tugas yang diterbitkan akan
                            muncul di daftar tugas mahasiswa.
                        </p>

                        <div class="space-y-3">

                            <button
                                type="submit"
                                <?= $galatMuat !== '' || $matkul === []
                                    ? 'disabled'
                                    : '' ?>
                                class="button w-full justify-center bg-lime-500 py-3 text-stone-950 hover:bg-lime-400 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Simpan
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

    <!-- DOCK -->
    <?php require __DIR__ . '/../../components/dock.php'; ?>
</body>
</html>
