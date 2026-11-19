<?php
$page = 'jadwal';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../jadwal.php';

$user = require_login();
$kelasId = (int) ($user['id_kelas'] ?? 0);
if (($user['role'] ?? '') !== 'admin' || $kelasId < 1) {
    http_response_code(403);
    exit('Halaman ini khusus admin kelas.');
}

$edit = array_key_exists('id', $_GET);
$id = $edit ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 0;
if ($id === false) {
    http_response_code(404);
    exit('Jadwal tidak ditemukan.');
}
$title = $edit ? 'Edit jadwal' : 'Tambah jadwal';
$formUrl = url('admin/jadwal', $edit ? ['id' => $id] : []);
$hari = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$form = ['id_matkul' => '', 'id_dosen' => '', 'id_ruangan' => '', 'hari' => '1',
         'jenis_pertemuan' => 'teori', 'jam_mulai' => '', 'jam_selesai' => ''];
$error = '';

try {
    if ($edit) {
        $data = jadwal_ambil($kelasId, $id);
        if ($data === null) {
            http_response_code(404);
            exit('Jadwal tidak ditemukan di kelasmu.');
        }
        $form = array_replace($form, $data);
    }
    $opsi = jadwal_opsi($kelasId);
} catch (Throwable $e) {
    error_log('[admin/jadwal] muat form: ' . $e->getMessage());
    http_response_code(500);
    exit('Form jadwal belum bisa dimuat. Coba muat ulang halaman.');
}

if (is_post()) {
    if (!csrf_verify()) {
        http_response_code(403);
        $error = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } else {
        $aksi = post_str('aksi');
        if ($aksi === 'simpan') {
            foreach (array_keys($form) as $key) {
                $form[$key] = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
            }
        }
        try {
            if ($aksi === 'hapus' && $edit) {
                if (!jadwal_hapus($kelasId, $id)) {
                    throw new InvalidArgumentException('Jadwal sudah tidak tersedia di kelasmu.');
                }
                $status = 'dihapus';
            } elseif ($aksi === 'simpan') {
                jadwal_simpan($kelasId, $id, $form);
                $status = $edit ? 'diubah' : 'dibuat';
            } else {
                throw new InvalidArgumentException('Aksi jadwal tidak dikenal.');
            }
            redirect(url('jadwal', ['ok' => $status]));
            exit;
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (Throwable $e) {
            error_log('[admin/jadwal] ' . $e->getMessage());
            $error = 'Perubahan jadwal gagal disimpan. Coba lagi nanti.';
        }
    }
}
$opsiLengkap = $opsi['matkul'] !== [] && $opsi['dosen'] !== [] && $opsi['ruangan'] !== [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../../components/head.php'; ?>
</head>
<body class="bg-stone-950 text-stone-100 font-sans antialiased">
    <?php require __DIR__ . '/../../components/sidebar.php'; ?>
    <div class="lg:ml-68">
        <?php require __DIR__ . '/../../components/navbar.php'; ?>
        <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10">
            <h1 class="mb-6 text-3xl font-semibold tracking-tight sm:text-4xl"><?= e($title) ?><span class="text-lime-300">.</span></h1>
            <form action="<?= e($formUrl) ?>" method="POST" class="panel grid max-w-4xl gap-4 p-5 sm:grid-cols-2 sm:p-7">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="simpan">
                <?php if ($error !== ''): ?>
                    <p role="alert" class="text-sm text-red-300 sm:col-span-2"><?= e($error) ?></p>
                <?php endif; ?>
                <label class="grid gap-2 text-sm sm:col-span-2">Mata kuliah
                    <select name="id_matkul" required class="field">
                        <option value="">Pilih mata kuliah</option>
                        <?php foreach ($opsi['matkul'] as $m): ?>
                            <option value="<?= (int) $m['id_matkul'] ?>" <?= (string) $form['id_matkul'] === (string) $m['id_matkul'] ? 'selected' : '' ?>><?= e($m['nama_matkul']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">Hari
                    <select name="hari" required class="field">
                        <?php foreach ($hari as $nomor => $nama): ?>
                            <option value="<?= $nomor ?>" <?= (string) $form['hari'] === (string) $nomor ? 'selected' : '' ?>><?= e($nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">Jenis kelas
                    <select name="jenis_pertemuan" required class="field">
                        <?php foreach (['teori' => 'Teori', 'praktikum' => 'Praktikum'] as $nilai => $label): ?>
                            <option value="<?= e($nilai) ?>" <?= $form['jenis_pertemuan'] === $nilai ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">Jam mulai (WIB)
                    <input type="time" name="jam_mulai" required class="field" value="<?= e($form['jam_mulai']) ?>">
                </label>
                <label class="grid gap-2 text-sm">Jam selesai (WIB)
                    <input type="time" name="jam_selesai" required class="field" value="<?= e($form['jam_selesai']) ?>">
                </label>
                <label class="grid gap-2 text-sm">Ruang kelas
                    <select name="id_ruangan" required class="field">
                        <option value="">Pilih ruangan</option>
                        <?php foreach ($opsi['ruangan'] as $r): ?>
                            <option value="<?= (int) $r['id_ruangan'] ?>" <?= (string) $form['id_ruangan'] === (string) $r['id_ruangan'] ? 'selected' : '' ?>><?= e($r['gedung'] . ' · ' . $r['nama_ruangan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="grid gap-2 text-sm">Dosen pengampu
                    <select name="id_dosen" required class="field">
                        <option value="">Pilih dosen</option>
                        <?php foreach ($opsi['dosen'] as $d): ?>
                            <option value="<?= (int) $d['id_dosen'] ?>" <?= (string) $form['id_dosen'] === (string) $d['id_dosen'] ? 'selected' : '' ?>><?= e($d['nama_dosen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php if (!$opsiLengkap): ?>
                    <p class="text-sm text-amber-300 sm:col-span-2">Lengkapi data mata kuliah, dosen, dan ruangan sebelum menyimpan jadwal.</p>
                <?php endif; ?>
                <div class="flex flex-wrap gap-2 sm:col-span-2">
                    <button type="submit" class="button" <?= !$opsiLengkap ? 'disabled' : '' ?>><?= icon('check', 'size-4') ?> <?= $edit ? 'Simpan perubahan' : 'Terbitkan jadwal' ?></button>
                    <a href="<?= e(url('jadwal')) ?>" class="button button-secondary">Batal</a>
                </div>
            </form>
        </main>
    </div>
    <?php require __DIR__ . '/../../components/dock.php'; ?>
</body>
</html>