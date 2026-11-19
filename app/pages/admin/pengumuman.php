<?php
/** Form tambah dan edit pengumuman untuk admin kelas. */
$page = 'pengumuman';
require_once __DIR__ . '/../../bootstrap.php';

$user = require_login();
$kelasId = (int) ($user['id_kelas'] ?? 0);
if (($user['role'] ?? '') !== 'admin' || $kelasId < 1) {
    http_response_code(403);
    exit('Halaman ini khusus admin kelas.');
}

$edit = array_key_exists('id', $_GET);
$id = $edit ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;
if ($edit && $id === false) {
    http_response_code(404);
    exit('Pengumuman tidak ditemukan di kelasmu.');
}
$title = $edit ? 'Edit pengumuman' : 'Tambah pengumuman';
$formUrl = url('admin/pengumuman', $edit ? ['id' => $id] : []);
$form = ['judul' => '', 'isi' => '', 'penting' => false];
$error = '';

try {
    // Isi form saat admin membuka halaman edit.
    if ($edit && !is_post()) {
        $data = pengumuman_ambil($id, $kelasId);
        if ($data === null) {
            http_response_code(404);
            exit('Pengumuman tidak ditemukan di kelasmu.');
        }
        $form = [
            'judul' => $data['judul'],
            'isi' => $data['isi_pengumuman'],
            'penting' => in_array($data['is_penting'], [true, 1, '1', 't', 'true'], true),
        ];
    }

    // Pertahankan input pengguna jika validasi gagal.
    if (is_post()) {
        $form['judul'] = trim(preg_replace('/\s+/u', ' ', post_str('judul')) ?? '');
        $isiMentah = is_string($_POST['isi'] ?? null) ? $_POST['isi'] : '';
        $form['isi'] = trim(str_replace("\r\n", "\n", $isiMentah));
        $form['penting'] = ($_POST['is_penting'] ?? '') === '1';
        $panjangJudul = text_length($form['judul']);
        $panjangIsi = text_length($form['isi'], allowNewline: true);

        if (!csrf_verify()) {
            $error = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        } elseif ($panjangJudul === null || $panjangJudul < 3 || $panjangJudul > 200) {
            $error = 'Judul harus 3–200 karakter.';
        } elseif ($panjangIsi === null || $panjangIsi < 1 || $panjangIsi > 5000) {
            $error = 'Isi pengumuman harus 1–5000 karakter (tanpa karakter kontrol).';
        } elseif ($edit) {
            if (pengumuman_ubah($id, $kelasId, $form['judul'], $form['isi'], $form['penting'])) {
                redirect(url('pengumuman', ['ok' => 'diubah', 'id' => $id]));
                exit;
            }
            http_response_code(404);
            $error = 'Pengumuman tidak ditemukan di kelasmu.';
        } else {
            pengumuman_buat($kelasId, (int) $user['id_user'], $form['judul'], $form['isi'], $form['penting']);
            redirect(url('pengumuman', ['ok' => 'dibuat']));
            exit;
        }
    }
} catch (Throwable $e) {
    error_log('[admin/pengumuman] ' . $e->getMessage());
    if (!is_post()) {
        http_response_code(500);
        exit('Pengumuman belum bisa dimuat. Coba muat ulang halaman.');
    }
    $error = 'Pengumuman gagal disimpan. Coba lagi nanti.';
}
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
        <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10">
            <h1 class="mb-6 text-3xl font-semibold tracking-tight sm:text-4xl"><?= e($title) ?><span class="text-lime-300">.</span></h1>
            <form action="<?= e($formUrl) ?>" method="POST" class="panel max-w-4xl space-y-4 p-5 sm:p-7">
                <?= csrf_field() ?>
                <div>
                    <label for="p-judul" class="mb-2 block text-xs font-semibold">Judul</label>
                    <input id="p-judul" name="judul" type="text" required minlength="3" maxlength="200" value="<?= e($form['judul']) ?>" placeholder="Contoh: Perubahan ruang kelas Basis Data" class="field">
                </div>
                <div>
                    <label for="p-isi" class="mb-2 block text-xs font-semibold">Isi pengumuman</label>
                    <textarea id="p-isi" name="isi" rows="6" required maxlength="5000" class="field"><?= e($form['isi']) ?></textarea>
                </div>
                <label class="flex min-h-11 items-center gap-3 text-sm text-stone-300">
                    <input type="checkbox" name="is_penting" value="1" <?= $form['penting'] ? 'checked' : '' ?> class="size-4 accent-lime-500">
                    Tandai sebagai penting
                </label>
                <?php if ($error !== ''): ?>
                    <p role="alert" class="text-sm text-red-300"><?= e($error) ?></p>
                <?php endif; ?>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="button"><?= icon('check', 'size-4') ?> <?= $edit ? 'Simpan perubahan' : 'Terbitkan' ?></button>
                    <a href="<?= e(url('pengumuman')) ?>" class="button button-secondary">Batal</a>
                </div>
            </form>
        </main>
    </div>
    <?php require __DIR__ . '/../../components/dock.php'; ?>
</body>
</html>