<?php
/** Daftar pengumuman kelas dan aksi hapus untuk admin. */
$page = 'pengumuman';
$title = 'Pengumuman';
require_once __DIR__ . '/../bootstrap.php';

$user = require_login();
$isAdmin = ($user['role'] ?? '') === 'admin';
$kelasId = (int) ($user['id_kelas'] ?? 0);
if ($kelasId < 1) {
    http_response_code(403);
    exit('Kamu belum tergabung dalam kelas.');
}

$pesanOk = [
    'dibuat' => 'Pengumuman diterbitkan.',
    'diubah' => 'Pengumuman diperbarui.',
    'dihapus' => 'Pengumuman dihapus.',
];
$okKey = $_GET['ok'] ?? '';
$ok = is_string($okKey) ? ($pesanOk[$okKey] ?? '') : '';
$error = '';

// Aksi hapus dari kartu pengumuman.
if (is_post()) {
    if (!$isAdmin) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
    try {
        if (!csrf_verify()) {
            $error = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        } elseif (post_str('aksi') !== 'hapus') {
            $error = 'Aksi tidak dikenal.';
        } else {
            $id = filter_var(post_str('id_pengumuman'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false && pengumuman_hapus($id, $kelasId)) {
                redirect(url('pengumuman', ['ok' => 'dihapus']));
                exit;
            }
            $error = 'Pengumuman tidak ditemukan di kelasmu.';
        }
    } catch (Throwable $e) {
        error_log('[pengumuman] hapus: ' . $e->getMessage());
        $error = 'Pengumuman gagal dihapus. Coba lagi nanti.';
    }
}

// Muat daftar untuk kelas pengguna.
$pengumuman = [];
$galatMuat = '';
try {
    $pengumuman = pengumuman_daftar($kelasId);
} catch (Throwable $e) {
    error_log('[pengumuman] muat daftar: ' . $e->getMessage());
    $galatMuat = 'Pengumuman belum bisa dimuat. Coba muat ulang halaman.';
}
$bukaId = is_string($_GET['id'] ?? null) ? $_GET['id'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../components/head.php'; ?>
</head>
<body class="bg-stone-950 text-stone-100 font-sans antialiased">
    <?php require __DIR__ . '/../components/sidebar.php'; ?>
    <div class="lg:ml-68">
        <?php require __DIR__ . '/../components/navbar.php'; ?>
        <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Kabar kelas<span class="text-lime-300">.</span></h1>
                <?php if ($isAdmin): ?>
                    <a
                        href="<?= e(url('admin/pengumuman')) ?>"
                        class="button page-add"
                        aria-label="Tambah pengumuman"
                    >
                        <?= icon('plus', 'page-add-icon') ?>
                        <span class="page-add-label">Tambah pengumuman</span>
                    </a>
                <?php endif; ?>
            </header>

            <?php if ($ok !== ''): ?>
                <p role="status" class="mb-4 rounded-2xl border border-lime-300/30 bg-lime-300/10 px-4 py-3 text-sm text-lime-200"><?= e($ok) ?></p>
            <?php endif; ?>
            <?php foreach ([$error, $galatMuat] as $pesan): ?>
                <?php if ($pesan !== ''): ?>
                    <p role="alert" class="mb-4 rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-sm text-red-300"><?= e($pesan) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="mb-3 flex items-center justify-between gap-3">
                <p id="news-result" role="status" aria-live="polite" class="text-base md:text-lg font-semibold text-stone-400"><span class="text-lime-400"><?= count($pengumuman) ?></span> pengumuman</p>
                <div class="flex gap-2" role="group" aria-label="Kategori pengumuman">
                    <button type="button" data-news-filter="Semua" aria-pressed="true" class="filter-tab">Semua</button>
                    <button type="button" data-news-filter="Penting" aria-pressed="false" class="filter-tab">Penting</button>
                </div>
            </div>
            <div class="space-y-3 sm:space-y-4">
                <?php if ($pengumuman === [] && $galatMuat === ''): ?>
                    <div class="panel px-6 py-14 text-center">
                        <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('bell') ?></span>
                        <h2 class="mt-4 text-lg font-semibold">Belum ada pengumuman.</h2>
                        <p class="mt-2 text-sm text-stone-400"><?= $isAdmin ? 'Pilih Tambah pengumuman untuk menulis kabar pertama kelasmu.' : 'Kabar dari admin kelas akan muncul di sini.' ?></p>
                    </div>
                <?php endif; ?>

                <?php foreach ($pengumuman as $item): ?>
                    <?php
                    $id = (int) $item['id_pengumuman'];
                    $isi = $item['isi_pengumuman'];
                    $penting = in_array($item['is_penting'], [true, 1, '1', 't', 'true'], true);
                    $ringkas = pengumuman_ringkas($isi);
                    $adaDetail = str_contains($isi, "\n") || $ringkas !== preg_replace('/\s+/u', ' ', trim($isi));
                    ?>
                    <article id="news-<?= e($id) ?>" data-news-card data-important="<?= $penting ? 'true' : 'false' ?>" class="panel p-4 sm:p-7">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs md:text-sm text-stone-400">Oleh <?= e($item['penulis']) ?></p>
                            <?php if ($penting): ?>
                                <span class="tag bg-amber-300/10 text-amber-200"><?= icon('bell', 'size-3') ?> Penting</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="mt-3 text-xl font-semibold tracking-tight"><?= e($item['judul']) ?></h2>
                        <p class="mt-2 text-sm leading-6 text-stone-400"><?= e($ringkas) ?></p>
                        <?php if ($adaDetail): ?>
                            <details class="mt-2" <?= $bukaId === (string) $id ? 'open' : '' ?>>
                                <summary class="flex min-h-11 w-fit cursor-pointer items-center gap-2 text-xs md:text-sm font-semibold text-lime-300">
                                    Baca selengkapnya <?= icon('chevron', 'size-3') ?>
                                </summary>
                                <div class="whitespace-pre-line text-sm leading-7 text-stone-300"><?= e($isi) ?></div>
                            </details>
                        <?php endif; ?>
                        <footer class="mt-4 flex flex-col gap-3 border-t border-stone-800 pt-3 lg:flex-row lg:items-center lg:justify-between">
                            <span class="text-xs text-stone-400 md:text-sm">
                                <?= e(date_id($item['tanggal'])) ?>
                            </span>

                            <?php if ($isAdmin): ?>
                                <div class="grid w-full grid-cols-2 gap-2 lg:flex lg:w-auto lg:shrink-0 lg:items-center">
                                    <a
                                        href="<?= e(url('admin/pengumuman', ['id' => $id])) ?>"
                                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-4xl border border-stone-700 px-3 py-2 text-xs font-semibold text-stone-300 transition hover:bg-stone-800 lg:w-auto"
                                    >
                                        <?= icon('edit', 'size-4') ?>
                                        Edit
                                    </a>

                                    <form
                                        action="<?= e(url('pengumuman')) ?>"
                                        method="POST"
                                        class="min-w-0"
                                        onsubmit="return confirm('Hapus pengumuman ini secara permanen untuk seluruh kelas?');"
                                    >
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id_pengumuman" value="<?= e($id) ?>">

                                        <button
                                            type="submit"
                                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-4xl border border-red-400/40 px-3 py-2 text-xs font-semibold text-red-400 transition hover:bg-red-400/10 lg:w-auto"
                                        >
                                            <?= icon('trash', 'size-4') ?>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
                <div id="news-empty" hidden class="panel px-6 py-14 text-center">
                    <h2 class="text-lg font-semibold">Belum ada pengumuman untuk filter ini.</h2>
                    <button type="button" data-news-reset class="button button-secondary mt-5">Lihat semua</button>
                </div>
            </div>
        </main>
    </div>
    <?php require __DIR__ . '/../components/dock.php'; ?>
</body>
</html>