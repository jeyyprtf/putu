<?php
$page = 'admin';
$title = 'Kelola data akademik';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../akademik.php';

// Guard
$user = require_login();
$idUser = (int) $user['id_user'];
$kelasId = (int) $user['id_kelas'];

if (($user['role'] ?? '') !== 'admin' || $kelasId <= 0) {
    redirect(url('dashboard'));
    exit;
}

// Tab dan mode tampilan
$route = 'admin/admin';
$tabs = akademik_tabs();
$tab = $_GET['tab'] ?? 'dosen';
$mode = $_GET['mode'] ?? 'daftar';

if (!is_string($tab) || !isset($tabs[$tab]) || !in_array($mode, ['daftar', 'tambah', 'edit'], true)) {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}

$meta = $tabs[$tab];
$label = strtolower($meta['label']);
$listUrl = url($route, ['tab' => $tab]);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$form = array_fill_keys($meta['kolom'], '');
$rows = [];
$error = '';
$ok = $_SESSION['akademik_pesan'] ?? '';
unset($_SESSION['akademik_pesan']);

// Aksi POST
if (is_post()) {
    try {
        $aksi = post_str('aksi');

        if ($mode !== 'daftar') {
            foreach ($meta['kolom'] as $kolom) {
                $form[$kolom] = trim(post_str($kolom));
            }
        }
        if (!csrf_verify()) {
            throw new DomainException('Sesi form kedaluwarsa. Muat ulang lalu coba lagi.');
        }

        $aksiForm = $mode === 'daftar' ? 'hapus' : $mode;
        if ($aksi !== $aksiForm) {
            throw new DomainException('Aksi tidak sesuai dengan formulir.');
        }
        if ($aksi === 'hapus') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        }

        akademik_simpan($tab, $aksi, $idUser, $kelasId, $id ?: null, $form);
        $hasil = ['tambah' => 'ditambahkan', 'edit' => 'diperbarui', 'hapus' => 'dihapus'];
        $_SESSION['akademik_pesan'] = $meta['label'] . ' berhasil ' . $hasil[$aksi] . '.';
        redirect($listUrl);
        exit;
    } catch (DomainException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log('[admin/akademik] ' . $e->getMessage());
        $error = match ((string) $e->getCode()) {
            '23503' => 'Data masih dipakai oleh jadwal atau tugas sehingga belum bisa dihapus.',
            '23505' => 'Data dengan kombinasi nilai tersebut sudah terdaftar.',
            default => 'Perubahan gagal disimpan. Silakan coba lagi.',
        };
    }
}

// Muat data. Saat validasi POST gagal, form mempertahankan isian pengguna.
try {
    if ($mode === 'edit' && !is_post()) {
        $record = $id ? (akademik_data($tab, $kelasId, $id)[0] ?? null) : null;
        if ($record) {
            $form = $record;
        } else {
            $mode = 'daftar';
            $error = 'Data yang ingin diedit tidak ditemukan.';
        }
    }
    if ($mode === 'daftar') {
        $rows = akademik_data($tab, $kelasId);
    }
} catch (Throwable $e) {
    error_log('[admin/akademik/muat] ' . $e->getMessage());
    $mode = 'daftar';
    $error = trim($error . ' Data gagal dimuat. Silakan muat ulang halaman.');
}

$judul = $mode === 'daftar' ? $title : ($mode === 'edit' ? 'Edit ' : 'Tambah ') . $label;
$components = __DIR__ . '/../../components';
?>
<!DOCTYPE html>
<html lang="id">
<head><?php require $components . '/head.php'; ?></head>
<body class="bg-stone-950 font-sans text-stone-100 antialiased">
    <?php require $components . '/sidebar.php'; ?>
    <div class="lg:ml-68">
        <?php require $components . '/navbar.php'; ?>
        <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-6 sm:px-8 lg:px-10 lg:py-10">
            <?php if ($mode !== 'daftar'): ?>
                <a href="<?= e($listUrl) ?>" class="mb-3 inline-flex min-h-11 items-center gap-2 text-sm text-stone-400">
                    <?= icon('back', 'size-4') ?> Daftar <?= e($label) ?>
                </a>
            <?php endif; ?>

            <header class="mb-6 flex flex-wrap items-center justify-between gap-3 sm:mb-8">
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    <?= e($judul) ?><span class="text-lime-300">.</span>
                </h1>
                <?php if ($mode === 'daftar'): ?>
                    <a href="<?= e(url($route, ['tab' => $tab, 'mode' => 'tambah'])) ?>"
                       class="button page-add" aria-label="<?= e('Tambah ' . $label) ?>">
                        <?= icon('plus', 'page-add-icon') ?>
                        <span class="page-add-label">Tambah <?= e($label) ?></span>
                    </a>
                <?php endif; ?>
            </header>

            <nav aria-label="Jenis data akademik" class="mb-4 grid grid-cols-3 gap-3 sm:flex sm:gap-4">
                <?php foreach ($tabs as $key => $item): ?>
                    <a href="<?= e(url($route, ['tab' => $key])) ?>"
                       <?= $tab === $key ? 'aria-current="page"' : '' ?>
                       class="filter-tab rounded-xl! px-2! sm:px-4!">
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <p class="mb-6 text-sm leading-6 text-stone-400">
                <?= $tab === 'matkul' ? 'Mata kuliah kelasmu.' : 'Data ini digunakan bersama oleh semua kelas.' ?>
            </p>
            <?php if ($error !== ''): ?>
                <p role="alert" class="mb-4 rounded-xl bg-red-300/10 p-3 text-sm text-red-300"><?= e($error) ?></p>
            <?php endif; ?>
            <?php if ($ok !== ''): ?>
                <p role="status" class="mb-4 rounded-xl bg-lime-300/10 p-3 text-sm text-lime-200"><?= e($ok) ?></p>
            <?php endif; ?>

            <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
                <form method="post" class="max-w-2xl space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="<?= e($mode) ?>">
                    <div class="panel grid gap-4 p-5 sm:p-6">
                        <?php require $components . '/form-' . $tab . '.php'; ?>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="button">Simpan <?= e($label) ?></button>
                        <a href="<?= e($listUrl) ?>" class="button button-secondary">Batal</a>
                    </div>
                </form>
            <?php else: ?>
                <section aria-label="<?= e('Daftar ' . $label) ?>" class="grid gap-3">
                    <?php if ($rows === [] && $error === ''): ?>
                        <p class="panel p-5 text-sm text-stone-400">Belum ada data <?= e($label) ?>.</p>
                    <?php endif; ?>

                    <?php foreach ($rows as $row): ?>
                        <article class="panel grid gap-4 p-4 lg:flex lg:items-center lg:justify-between lg:p-5">
                            <div class="min-w-0">
                                <h2 class="break-words font-semibold"><?= e($row[$meta['nama']]) ?></h2>
                                <?php if ($tab === 'matkul'): ?>
                                    <p class="mt-1 text-sm text-stone-400">
                                        <?= (int) $row['sks'] ?> SKS · Semester <?= (int) $row['semester'] ?>
                                    </p>
                                <?php elseif ($tab === 'ruang'): ?>
                                    <p class="mt-1 break-words text-sm text-stone-400">
                                        <?= e($row['gedung']) ?>
                                        <?php if ($row['kapasitas'] !== null): ?> · <?= (int) $row['kapasitas'] ?> orang<?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="grid grid-cols-2 gap-2 lg:flex lg:shrink-0">
                                <a href="<?= e(url($route, ['tab' => $tab, 'mode' => 'edit', 'id' => $row[$meta['id']]])) ?>"
                                   class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-stone-700 px-3 text-xs font-semibold text-stone-300 hover:bg-stone-800">
                                    <?= icon('edit', 'size-4') ?> Edit
                                </a>
                                <form method="post" action="<?= e($listUrl) ?>" data-nama="<?= e($row[$meta['nama']]) ?>"
                                      onsubmit="return confirm('Hapus ' + this.dataset.nama + '?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id" value="<?= (int) $row[$meta['id']] ?>">
                                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-red-400/40 px-3 text-xs font-semibold text-red-400 hover:bg-red-400/10">
                                        <?= icon('trash', 'size-4') ?> Hapus
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <?php require $components . '/dock.php'; ?>
</body>
</html>