<?php
$page = 'anggota';
$title = 'Anggota Kelas';

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../anggota.php';

$user = require_login();
$idUser = (int) $user['id_user'];

$anggota = [];
$kelas = null;
$kelasId = 0;
$semester = '';
$isAdminKelas = false;
$galatMuat = '';
$galatAksi = '';

$pesanOk = $_SESSION['anggota_pesan'] ?? '';
unset($_SESSION['anggota_pesan']);

$tokenAnggota = $_SESSION['anggota_token'] ??= bin2hex(random_bytes(32));

try {
    // Baca kelas dan jabatan terbaru, termasuk jika sesi login masih aktif
    // setelah pengguna diturunkan jabatannya atau dikeluarkan dari kelas.
    $stmt = db()->prepare(
        'SELECT id_kelas, role FROM users WHERE id_user = :id'
    );
    $stmt->execute(['id' => $idUser]);
    $akun = $stmt->fetch(PDO::FETCH_ASSOC);

    $kelasId = (int) ($akun['id_kelas'] ?? 0);
    $isAdminKelas = $kelasId > 0 && ($akun['role'] ?? '') === 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $tokenPost = $_POST['token_anggota'] ?? null;

            if (!is_string($tokenPost) || !hash_equals($tokenAnggota, $tokenPost)) {
                throw new DomainException('Formulir sudah kedaluwarsa. Muat ulang halaman.');
            }

            $kelasForm = filter_input(INPUT_POST, 'id_kelas', FILTER_VALIDATE_INT);
            $idTarget = filter_input(INPUT_POST, 'id_target', FILTER_VALIDATE_INT);
            $aksi = $_POST['aksi'] ?? '';
            $roleBaru = $_POST['role'] ?? '';

            if (!$kelasForm || $kelasForm !== $kelasId) {
                throw new DomainException('Kelas sudah berubah. Muat ulang halaman.');
            }

            anggota_kelola(
                $idUser,
                $kelasId,
                (int) $idTarget,
                is_string($aksi) ? $aksi : '',
                is_string($roleBaru) ? $roleBaru : ''
            );

            $_SESSION['anggota_pesan'] = $aksi === 'keluarkan'
                ? 'Anggota berhasil dikeluarkan dari kelas.'
                : 'Jabatan anggota berhasil diperbarui.';

            // Pertahankan URL halaman, termasuk parameter router jika ada.
            $urlHalaman = '/' . ltrim($_SERVER['REQUEST_URI'], '/');
            header('Location: ' . $urlHalaman, true, 303);
            exit;
        } catch (DomainException $e) {
            $galatAksi = $e->getMessage();
        } catch (Throwable $e) {
            error_log('[anggota/kelola] ' . $e->getMessage());
            $galatAksi = 'Perubahan gagal disimpan. Silakan coba lagi.';
        }
    }

    if ($kelasId > 0) {
        $kelas = anggota_kelas($kelasId);
        $semester = anggota_semester($kelasId);
        $anggota = anggota_daftar($kelasId, $idUser);
    }
} catch (Throwable $e) {
    error_log('[anggota/muat] ' . $e->getMessage());
    $galatMuat = 'Data anggota gagal dimuat. Silakan muat ulang halaman.';
}

$totalAnggota = count($anggota);
$totalAdmin = count(array_keys(array_column($anggota, 'role'), 'admin', true));
$labelKelas = trim((string) ($kelas['nama_kelas'] ?? ''));
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
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 sm:pt-8 lg:px-10 lg:py-10 lg:pt-10">
                <section class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:mt-0 sm:text-4xl">
                        Anggota kelas<span class="text-lime-300">.</span>
                    </h1>
                    <p class="shrink-0 text-sm text-stone-400">
                        <?php if ($labelKelas !== ''): ?><?= e($labelKelas) ?> · <?php endif; ?><?= $totalAnggota ?> anggota
                    </p>
                </section>

                <?php if ($pesanOk !== ''): ?>
                    <p role="status" class="mb-4 rounded-2xl bg-lime-300/10 px-4 py-3 text-sm text-lime-200">
                        <?= e($pesanOk) ?>
                    </p>
                <?php endif; ?>

                <?php if ($galatAksi !== ''): ?>
                    <p role="alert" class="mb-4 rounded-2xl bg-red-300/10 px-4 py-3 text-sm text-red-300">
                        <?= e($galatAksi) ?>
                    </p>
                <?php endif; ?>
                <?php if ($galatMuat !== ''): ?>
                    <p role="alert" class="mb-4 rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-sm text-red-300"><?= e($galatMuat) ?></p>
                <?php endif; ?>

                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <label for="member-search" class="sr-only">Cari nama anggota</label>
                        <input id="member-search" type="search" autocomplete="off" placeholder="Cari nama anggota" class="field">
                    </div>
                    <div class="flex shrink-0 gap-2 overflow-x-auto" role="group" aria-label="Filter jabatan">
                        <button type="button" data-member-filter="Semua" aria-pressed="true" class="filter-tab">Semua</button>
                        <button type="button" data-member-filter="Admin kelas" aria-pressed="false" class="filter-tab">Admin kelas</button>
                        <button type="button" data-member-filter="Mahasiswa" aria-pressed="false" class="filter-tab">Mahasiswa</button>
                    </div>
                </div>

                <p id="member-result" role="status" aria-live="polite" class="sr-only"><?= $totalAnggota ?> anggota</p>

                <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_21rem]">
                    <section id="member-list" aria-label="Daftar anggota" class="flex flex-col gap-3">
                        <?php if ($anggota === [] && $galatMuat === ''): ?>
                            <div class="panel px-6 py-14 text-center">
                                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('users') ?></span>
                                <h2 class="mt-4 text-lg font-semibold">Belum ada anggota lain.</h2>
                                <p class="mt-2 text-sm text-stone-400">
                                    Anggota kelas akan muncul di sini setelah bergabung.
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($anggota as $m): ?>
                            <?php require __DIR__ . '/../components/member-card.php'; ?>
                        <?php endforeach; ?>

                        <div id="members-empty" class="panel hidden px-6 py-14 text-center">
                            <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-lime-300/10 text-lime-300"><?= icon('users') ?></span>
                            <h2 class="mt-4 text-lg font-semibold">Tidak ada anggota yang cocok.</h2>
                            <p class="mt-2 text-sm text-stone-400">Coba kata kunci atau filter lainnya.</p>
                            <button type="button" data-member-reset class="button button-secondary mt-5">Reset filter</button>
                        </div>
                    </section>

                    <aside class="panel p-5 xl:sticky xl:top-24">
                        <h2 class="text-lg font-semibold">Tentang kelas</h2>
                        <dl class="mt-2 divide-y divide-stone-800 text-sm">
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Kelas</dt>
                                <dd class="text-right font-medium"><?= e($labelKelas !== '' ? $labelKelas : '—') ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Semester</dt>
                                <dd class="text-right font-medium"><?= e($semester !== '' ? $semester : '—') ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Anggota</dt>
                                <dd class="text-right font-medium"><?= $totalAnggota ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="text-stone-400">Admin kelas</dt>
                                <dd class="text-right font-medium"><?= $totalAdmin ?></dd>
                            </div>
                        </dl>
                    </aside>
                </div>

                <?php if ($isAdminKelas && $galatMuat === ''): ?>
                    <?php require __DIR__ . '/../components/member-manage.php'; ?>
                <?php endif; ?>
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
