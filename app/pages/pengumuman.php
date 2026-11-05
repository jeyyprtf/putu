<?php
/**
 * Halaman pengumuman kelas.
 *
 * Urutan baca file ini:
 *   1. guard        : wajib login + wajib punya kelas
 *   2. identitas    : role dan id_kelas diambil dari SESI, bukan dari URL/form
 *   3. aksi POST    : admin menerbitkan / menghapus pengumuman
 *   4. muat data    : daftar pengumuman kelas
 *   5. render HTML
 *
 * Semua SQL ada di app/pengumuman.php. File ini hanya mengatur input,
 * menampilkan pesan, dan menyusun HTML.
 */
$page = 'pengumuman';
$title = 'Pengumuman';
require_once __DIR__ . '/../bootstrap.php';

$user = require_login();
$isAdmin = $user['role'] === 'admin';
$kelasId = (int) $user['id_kelas'];

// ---------------------------------------------------------------- Pesan status

$pesanOk = [
    'dibuat'  => 'Pengumuman diterbitkan.',
    'dihapus' => 'Pengumuman dihapus.',
];
$okKey = $_GET['ok'] ?? '';
$ok = is_string($okKey) ? ($pesanOk[$okKey] ?? '') : '';

$error = '';
$form = ['judul' => '', 'isi' => '', 'penting' => false];

// ---------------------------------------------------------------- Aksi admin (POST)

if (is_post()) {
    if (!$isAdmin) {
        http_response_code(403);
        exit('Akses ditolak.');
    }

    $aksi = post_str('aksi');

    try {
        if (!csrf_verify()) {
            $error = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';

        } elseif ($aksi === 'terbit') {
            // Rapikan spasi berlebih, lalu simpan nilai form supaya tidak hilang
            // saat validasi gagal.
            $form['judul'] = preg_replace('/\s+/u', ' ', post_str('judul')) ?? '';
            $isiMentah = is_string($_POST['isi'] ?? null) ? $_POST['isi'] : '';
            $form['isi'] = trim(str_replace("\r\n", "\n", $isiMentah));
            $form['penting'] = ($_POST['is_penting'] ?? '') === '1';

            $panjangJudul = text_length($form['judul']);
            $panjangIsi = text_length($form['isi'], allowNewline: true);

            if ($panjangJudul === null || $panjangJudul < 3 || $panjangJudul > 200) {
                $error = 'Judul harus 3–200 karakter.';
            } elseif ($panjangIsi === null || $panjangIsi < 1 || $panjangIsi > 5000) {
                $error = 'Isi pengumuman harus 1–5000 karakter (tanpa karakter kontrol).';
            } else {
                pengumuman_buat($kelasId, (int) $user['id_user'], $form['judul'], $form['isi'], $form['penting']);
                redirect(url('pengumuman', ['ok' => 'dibuat']));
            }

        } elseif ($aksi === 'hapus') {
            $idHapus = post_str('id_pengumuman');
            $idValid = ctype_digit($idHapus) && strlen($idHapus) <= 18 ? (int) $idHapus : 0;

            if (pengumuman_hapus($idValid, $kelasId)) {
                redirect(url('pengumuman', ['ok' => 'dihapus']));
            }
            $error = 'Pengumuman tidak ditemukan di kelasmu.';

        } else {
            $error = 'Aksi tidak dikenal.';
        }
    } catch (Throwable $e) {
        error_log('[pengumuman] ' . $e->getMessage());
        $error = 'Terjadi kesalahan di server. Coba lagi nanti.';
    }
}

// ---------------------------------------------------------------- Muat data

$pengumuman = [];
$galatMuat = '';

try {
    $pengumuman = pengumuman_daftar($kelasId);
} catch (Throwable $e) {
    error_log('[pengumuman] muat data: ' . $e->getMessage());
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
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 button">
            Lewati navigasi
        </a>
        <?php require __DIR__ . '/../components/sidebar.php'; ?>
        <div class="lg:ml-68">
            <?php require __DIR__ . '/../components/navbar.php'; ?>
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10 lg:pt-10">
                <section class="mb-4">
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:mt-0 sm:text-4xl">
                        Kabar kelas<span class="text-lime-300">.</span>
                    </h1>
                </section>

                <?php if ($ok !== ''): ?>
                    <p role="status" class="mb-4 max-w-4xl rounded-2xl border border-lime-300/30 bg-lime-300/10 px-4 py-3 text-sm text-lime-200"><?= e($ok) ?></p>
                <?php endif; ?>
                <?php if ($galatMuat !== ''): ?>
                    <p role="alert" class="mb-4 max-w-4xl rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-sm text-red-300"><?= e($galatMuat) ?></p>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <section aria-labelledby="tulis-title" class="panel mb-4 max-w-4xl p-4 sm:p-6">
                        <details <?= $error !== '' ? 'open' : '' ?>>
                            <summary id="tulis-title" class="flex min-h-11 items-center gap-2 text-sm font-semibold text-lime-300">
                                <?= icon('bell', 'size-4') ?>Tulis pengumuman untuk kelas
                            </summary>
                            <form action="<?= e(url('pengumuman')) ?>" method="POST" class="mt-4 space-y-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="aksi" value="terbit">
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
                                <p role="alert" aria-live="polite" class="text-xs leading-6 text-red-300" <?= $error === '' ? 'hidden' : '' ?>><?= e($error) ?></p>
                                <button type="submit" class="button"><?= icon('check', 'size-4') ?>Terbitkan</button>
                            </form>
                        </details>
                    </section>
                <?php elseif ($error !== ''): ?>
                    <p role="alert" class="mb-4 max-w-4xl text-xs leading-6 text-red-300"><?= e($error) ?></p>
                <?php endif; ?>

                <div class="mb-3 flex max-w-4xl items-center justify-between gap-3">
                    <p id="news-result" role="status" aria-live="polite" class="min-w-0 text-xs text-stone-400">
                        <?= count($pengumuman) ?> pengumuman
                    </p>
                    <div class="flex shrink-0 gap-2" role="group" aria-label="Kategori pengumuman">
                        <button type="button" data-news-filter="Semua" aria-pressed="true" class="filter-tab">
                            Semua
                        </button>
                        <button type="button" data-news-filter="Penting" aria-pressed="false" class="filter-tab">
                            Penting
                        </button>
                    </div>
                </div>
                <div class="max-w-4xl space-y-3 sm:space-y-4">
                    <?php if ($pengumuman === [] && $galatMuat === ''): ?>
                        <div class="panel px-6 py-14 text-center">
                            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('bell') ?></span>
                            <h2 class="mt-4 text-lg font-semibold">Belum ada pengumuman.</h2>
                            <p class="mt-2 text-sm text-stone-400">
                                <?= $isAdmin ? 'Tulis pengumuman pertama untuk kelasmu lewat form di atas.' : 'Kabar dari admin kelas akan muncul di sini.' ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($pengumuman as $n): ?>
                        <?php
                        $idN = (int) $n['id_pengumuman'];
                        $penting = (bool) $n['is_penting'];
                        $ringkas = pengumuman_ringkas($n['isi_pengumuman']);
                        $terpotong = $ringkas !== preg_replace('/\s+/u', ' ', trim($n['isi_pengumuman']));
                        ?>
                        <article class="panel p-4 sm:p-7" data-news-card data-important="<?= $penting ? 'true' : 'false' ?>" id="news-<?= e($idN) ?>">
                            <div class="flex flex-wrap items-center gap-2">
                                <?php if ($penting): ?>
                                    <span class="tag bg-amber-300/10 text-amber-200"><?= icon('bell', 'size-3') ?>Penting</span>
                                <?php endif; ?>
                                <span class="ml-auto text-xs text-stone-400"><?= e(date_id($n['tanggal'])) ?></span>
                            </div>
                            <h2 class="mt-2 text-xl font-semibold leading-7 tracking-tight sm:mt-5 sm:leading-8">
                                <?= e($n['judul']) ?>
                            </h2>
                            <p class="mt-1 text-xs text-stone-400 sm:mt-2">
                                Oleh <?= e($n['penulis']) ?>
                            </p>
                            <p class="mt-2 text-sm leading-6 text-stone-400 sm:mt-4 sm:leading-7">
                                <?= e($ringkas) ?>
                            </p>
                            <?php if ($terpotong || str_contains($n['isi_pengumuman'], "\n")): ?>
                                <details class="mt-1 sm:mt-4" <?= $bukaId === (string) $idN ? 'open' : '' ?>>
                                    <summary class="ml-auto flex min-h-11 w-fit items-center gap-2 text-xs font-semibold text-lime-300">
                                        Baca selengkapnya<span class="details-arrow transition-transform"><?= icon('chevron', 'size-3') ?></span>
                                    </summary>
                                    <div class="mt-2 whitespace-pre-line border-t border-stone-800 pt-3 text-sm leading-6 text-stone-300 sm:mt-3 sm:pt-4 sm:leading-8">
                                        <?= e($n['isi_pengumuman']) ?>
                                    </div>
                                </details>
                            <?php endif; ?>
                            <?php if ($isAdmin): ?>
                                <details class="mt-3 border-t border-stone-800 pt-3">
                                    <summary class="flex min-h-11 w-fit items-center text-xs font-semibold text-stone-400 hover:text-red-300">Hapus pengumuman</summary>
                                    <form action="<?= e(url('pengumuman')) ?>" method="POST" class="mt-2 rounded-2xl border border-red-300/30 bg-red-300/10 p-4">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id_pengumuman" value="<?= e($idN) ?>">
                                        <p class="text-xs leading-6 text-red-200">Hapus pengumuman ini untuk seluruh kelas? Tindakan ini tidak bisa dibatalkan.</p>
                                        <button type="submit" class="mt-3 inline-flex min-h-11 items-center justify-center rounded-2xl bg-red-400 px-5 text-sm font-bold text-red-950 transition-colors hover:bg-red-300">
                                            Ya, hapus
                                        </button>
                                    </form>
                                </details>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                    <div id="news-empty" hidden class="panel px-6 py-14 text-center">
                        <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('bell') ?></span>
                        <h2 class="mt-4 text-lg font-semibold">
                            Belum ada pengumuman untuk filter ini.
                        </h2>
                        <p class="mt-2 text-sm text-stone-400">
                            Coba lihat semua pengumuman kelas.
                        </p>
                        <button type="button" data-news-reset class="button button-secondary mt-5">
                            Lihat semua
                        </button>
                    </div>
                </div>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
