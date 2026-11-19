<?php
$page = 'jadwal';
$title = 'Jadwal kuliah';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../jadwal.php';

$user = require_login();

// Form modal hanya dapat dipakai oleh admin kelas yang telah login.
$isAdmin = ($user['role'] ?? '') === 'admin';
$kelasId = (int) ($user['id_kelas'] ?? 0);
$pesanAdmin = '';
$gagalSimpan = false;
$opsi = ['matkul' => [], 'dosen' => [], 'ruangan' => []];

if ($isAdmin) {
    require_once __DIR__ . '/../adminJadwal.php';

    if (is_post()) {
        if (!csrf_verify()) {
            $pesanAdmin = 'Token keamanan tidak valid. Muat ulang halaman.';
            $gagalSimpan = true;
        } else {
            try {
                if (post_str('aksi') !== 'simpan') {
                    throw new InvalidArgumentException('Aksi tidak dikenal.');
                }
                // ID 0 berarti tambah baru; tidak menerima ID jadwal dari browser.
                jadwal_admin_simpan($kelasId, 0, $_POST);
                redirect(url('jadwal', ['ok' => 1]));
            } catch (InvalidArgumentException $e) {
                $pesanAdmin = $e->getMessage();
                $gagalSimpan = true;
            } catch (Throwable $e) {
                error_log('[jadwal][tambah] ' . get_class($e) . ': ' . $e->getMessage());
                $pesanAdmin = 'Gagal menyimpan jadwal. Periksa log PHP server.';
                $gagalSimpan = true;
            }
        }
    }

    try {
        $opsi = jadwal_opsi($kelasId);
    } catch (Throwable $e) {
        error_log('[jadwal][opsi] ' . get_class($e) . ': ' . $e->getMessage());
        $pesanAdmin .= ($pesanAdmin !== '' ? ' ' : '') . 'Pilihan mata kuliah, dosen, atau ruangan tidak dapat dimuat.';
    }
}

// 0 = Senin ... 6 = Minggu (sama dengan indeks yang dipakai app.js).
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$todayIndex = (int) (new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta')))->format('N') - 1;

$jadwal = [];
$galatMuat = '';
try {
    $jadwal = jadwal_daftar((int) $user['id_kelas']); // id_kelas dari sesi, bukan dari URL
} catch (Throwable $e) {
    error_log('[jadwal] ' . $e->getMessage());
    $galatMuat = 'Jadwal belum bisa dimuat. Coba muat ulang halaman.';
}
$classesByDay = jadwal_per_hari($jadwal);
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
        <?php if ($isAdmin): ?>
        <style>
            .tambah-modal[hidden] { display: none !important; }
            .tambah-modal { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(0,0,0,.82); backdrop-filter: blur(5px); }
            .tambah-modal__card { width: min(100%, 1050px); max-height: 92dvh; overflow-y: auto; border: 1px solid #44403c; border-radius: 22px; background: #100f0e; box-shadow: 0 24px 70px #000b; }
            .tambah-modal__grid { display: grid; grid-template-columns: minmax(0,1.8fr) minmax(230px,1fr); gap: 16px; }
            .tambah-modal__panel { border: 1px solid #37322e; border-radius: 16px; background: #1c1917; padding: 24px; }
            .tambah-modal__field { width: 100%; min-width: 0; min-height: 46px; border: 1px solid #44403c; border-radius: 10px; background: #171513; color: #fafaf9; padding: 10px 12px; color-scheme: dark; }
            .tambah-modal__field:focus { outline: 2px solid #a3e635; outline-offset: 1px; }
            .tambah-modal__label { display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: #e7e5e4; }
            @media (max-width:700px) { .tambah-modal { align-items: flex-start; padding: 8px; } .tambah-modal__card { max-height: calc(100dvh - 16px); } .tambah-modal__grid { grid-template-columns: 1fr; } .tambah-modal__panel { padding: 16px; } }
        </style>
        <?php endif; ?>
    </head>
    <body class="bg-stone-950 text-stone-100 font-sans antialiased">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 button">
            Lewati navigasi
        </a>
        <?php require __DIR__ . '/../components/sidebar.php'; ?>
        <div class="lg:ml-68">
            <?php require __DIR__ . '/../components/navbar.php'; ?>
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 pt-4 sm:px-8 lg:px-10 lg:py-10">
                <section class="mb-8 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl">
                            Jadwal kuliah<span class="text-lime-300">.</span>
                        </h1>
                    </div>
                    <span class="tag border border-stone-800 bg-stone-900 text-stone-300"><?= e($user['nama_kelas'] ?? 'Kelasmu') ?></span>
                </section>
                <?php if ($isAdmin): ?>
                    <div class="mb-5 flex flex-wrap items-center gap-3">
                        <button type="button" id="buka-tambah-jadwal" class="inline-flex items-center rounded-xl bg-lime-500 px-5 py-3 font-semibold text-stone-950 transition hover:bg-lime-400">
                            + Tambah jadwal
                        </button>
                        <?php if ($opsi['matkul'] === []): ?>
                            <span class="text-sm text-amber-300">Belum ada mata kuliah untuk kelas ini (ID <?= $kelasId ?>).</span>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($_GET['ok'])): ?>
                        <p role="status" class="mb-4 rounded-xl border border-lime-500/30 bg-lime-500/10 px-4 py-3 text-lime-200">Jadwal berhasil diterbitkan.</p>
                    <?php endif; ?>
                    <?php if ($pesanAdmin !== ''): ?>
                        <p role="alert" class="mb-4 rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-red-200"><?= e($pesanAdmin) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($galatMuat !== ''): ?>
                    <p role="alert" class="mb-4 rounded-2xl border border-red-300/30 bg-red-300/10 px-4 py-3 text-sm text-red-300"><?= e($galatMuat) ?></p>
                <?php elseif ($jadwal === []): ?>
                    <p role="status" class="mb-4 rounded-2xl border border-stone-800 bg-stone-900 px-4 py-3 text-sm text-stone-300">
                        Belum ada jadwal kuliah untuk kelasmu. Jadwal akan muncul di sini setelah diisi.
                    </p>
                <?php endif; ?>
                <div class="mb-3 flex  items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold">
                        Agenda mingguan
                    </h2>
                    <div role="group" aria-label="Tampilan jadwal" class="flex gap-2">
                        <button type="button" data-schedule-view="daily" aria-pressed="false" class="filter-tab">
                            Harian
                        </button>
                        <button type="button" data-schedule-view="weekly" aria-pressed="true" class="filter-tab">
                            Mingguan
                        </button>
                    </div>
                </div>
                    <div id="day-picker" hidden class="mb-3 grid w-full grid-cols-7 gap-1.5 py-1 sm:gap-2" role="group" aria-label="Pilih hari">
                    <?php foreach ($days as $index => $day): ?>
                        <button type="button" data-day="<?= $index ?>" aria-label="<?= e($day) ?>" aria-pressed="<?= $index === $todayIndex ? 'true' : 'false' ?>" class="filter-tab w-full min-w-0 px-1! text-xs sm:text-sm">
                            <?= e(substr($day, 0, 3)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="grid items-start gap-5 xl:grid-cols-2" id="schedule-list" data-today="<?= $todayIndex ?>">
                    <?php foreach ($days as $index => $day): ?>
                        <section class="panel p-5 sm:p-6" data-schedule-day="<?= $index ?>" aria-labelledby="day-<?= $index ?>">
                            <div class="flex items-center justify-between gap-3 border-b border-stone-800 pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 id="day-<?= $index ?>" class="font-semibold">
                                        <?= e($day) ?>
                                    </h3>
                                    <?php if ($index === $todayIndex): ?>
                                        <span class="tag bg-lime-300/10 text-lime-200">Hari ini</span>
                                    <?php endif; ?>
                                </div>
                                <span class="shrink-0 text-xs text-stone-400"><?= count($classesByDay[$index]) ?> kelas</span>
                            </div>
                            <div class="divide-y divide-stone-800">
                                <?php foreach ($classesByDay[$index] as $class): ?>
                                    <article class="flex gap-4 py-5">
                                        <div class="w-14 shrink-0 border-r border-stone-700 pr-3">
                                            <p class="text-sm font-semibold">
                                                <?= e($class['start']) ?>
                                            </p>
                                            <p class="mt-2 text-xs text-stone-400">
                                                <?= e($class['end']) ?>
                                            </p>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-medium leading-6">
                                                <?= e($class['course']) ?>
                                            </h4>
                                            <p class="mt-1 text-xs text-lime-200/80">
                                                <?= e($class['type']) ?>
                                            </p>
                                            <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-stone-400">
                                                <?= icon('location', 'mt-0.5 size-3 shrink-0') ?><?= e($class['room']) ?>
                                            </p>
                                            <p class="mt-1 text-xs leading-5 text-stone-400">
                                                <?= e($class['lecturer']) ?>
                                            </p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                                <?php if (!$classesByDay[$index]): ?>
                                    <p class="py-7 text-sm leading-6 text-stone-400">
                                        Tidak ada kuliah. Ruang untuk istirahat atau mengejar tugas.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
                <p id="schedule-feedback" class="sr-only" role="status" aria-live="polite">
                </p>
          
            </main>
        </div>
        <?php require __DIR__ . '/../components/dock.php'; ?>
        <?php if ($isAdmin): ?>
        <!-- Modal tambah jadwal: tetap berada di halaman Jadwal Kuliah. -->
        <div id="modal-tambah-jadwal" class="tambah-modal" hidden>
            <section id="modal-tambah-card" class="tambah-modal__card" role="dialog" aria-modal="true" aria-labelledby="judul-tambah-jadwal" tabindex="-1">
                <header class="flex items-start justify-between gap-4 border-b border-stone-800 p-5 sm:px-7 sm:py-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-lime-400">Administrator</p>
                        <h2 id="judul-tambah-jadwal" class="mt-2 text-2xl font-semibold">Tambah jadwal</h2>
                        <p class="mt-2 text-sm text-stone-400">Atur waktu dan tempat perkuliahan.</p>
                    </div>
                    <button type="button" id="tutup-tambah-jadwal" aria-label="Tutup popup" class="rounded-lg border border-stone-700 px-3 py-2 hover:bg-stone-800">✕</button>
                </header>
                <div class="p-4 sm:p-6">
                    <form id="form-tambah-jadwal" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="simpan">
                        <div class="tambah-modal__grid">
                            <div class="tambah-modal__panel">
                                <h3 class="mb-6 text-lg font-semibold">Informasi perkuliahan</h3>
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <label for="tambah-id-matkul" class="tambah-modal__label sm:col-span-2">Mata kuliah
                                        <select required class="tambah-modal__field" name="id_matkul" id="tambah-id-matkul">
                                            <option value="">Pilih mata kuliah</option>
                                            <?php foreach ($opsi['matkul'] as $o): ?>
                                                <option value="<?= (int)$o['id_matkul'] ?>"><?= e($o['nama_matkul']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label for="tambah-hari" class="tambah-modal__label">Hari
                                        <select required class="tambah-modal__field" name="hari" id="tambah-hari">
                                            <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $i => $namaHari): ?>
                                                <option value="<?= $i+1 ?>"><?= e($namaHari) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label for="tambah-jenis" class="tambah-modal__label">Jenis kelas
                                        <select required class="tambah-modal__field" name="jenis_pertemuan" id="tambah-jenis">
                                            <option value="teori">Teori</option>
                                            <option value="praktikum">Praktikum</option>
                                        </select>
                                    </label>
                                    <label for="tambah-mulai" class="tambah-modal__label">Jam mulai
                                        <input required class="tambah-modal__field" name="jam_mulai" id="tambah-mulai" type="time">
                                    </label>
                                    <label for="tambah-selesai" class="tambah-modal__label">Jam selesai
                                        <input required class="tambah-modal__field" name="jam_selesai" id="tambah-selesai" type="time">
                                    </label>
                                    <label for="tambah-ruangan" class="tambah-modal__label sm:col-span-2">Ruang kelas
                                        <select required class="tambah-modal__field" name="id_ruangan" id="tambah-ruangan">
                                            <option value="">Pilih ruangan</option>
                                            <?php foreach ($opsi['ruangan'] as $o): ?>
                                                <option value="<?= (int)$o['id_ruangan'] ?>"><?= e($o['gedung'] . ' · ' . $o['nama_ruangan']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label for="tambah-dosen" class="tambah-modal__label sm:col-span-2">Dosen pengampu
                                        <select required class="tambah-modal__field" name="id_dosen" id="tambah-dosen">
                                            <option value="">Pilih dosen</option>
                                            <?php foreach ($opsi['dosen'] as $o): ?>
                                                <option value="<?= (int)$o['id_dosen'] ?>"><?= e($o['nama_dosen']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            <aside class="tambah-modal__panel self-start">
                                <h3 class="mb-4 text-lg font-semibold">Periksa jadwal</h3>
                                <p class="mb-5 text-sm leading-6 text-stone-400">Gunakan format 24 jam (WIB). Pastikan hari, jam, dosen, dan ruangan sesuai sebelum menerbitkan.</p>
                                <div class="mb-5 border-t border-stone-700 pt-4">
                                    <p class="text-xs text-stone-400">Kelas</p>
                                    <p class="mt-1 font-semibold"><?= e($user['nama_kelas'] ?? 'Kelas aktif') ?></p>
                                </div>
                                <button type="submit" class="w-full rounded-xl bg-lime-500 px-4 py-3 font-semibold text-stone-950 hover:bg-lime-400" <?= ($opsi['matkul'] === [] || $opsi['dosen'] === [] || $opsi['ruangan'] === []) ? 'disabled aria-disabled="true"' : '' ?>>Terbitkan jadwal</button>
                                <button type="button" id="batal-tambah-jadwal" class="mt-3 w-full rounded-xl border border-stone-700 px-4 py-3 hover:bg-stone-800">Batal</button>
                                <p id="tambah-waktu-error" role="alert" class="mt-3 hidden text-sm text-red-300">Jam selesai harus setelah jam mulai.</p>
                                <?php if ($opsi['matkul'] === []): ?><p class="mt-3 text-xs text-amber-300">Mata kuliah kelas ini belum tersedia (ID <?= $kelasId ?>).</p><?php endif; ?>
                            </aside>
                        </div>
                    </form>
                </div>
            </section>
        </div>
        <script>
        (() => {
            const modal = document.getElementById('modal-tambah-jadwal');
            const card = document.getElementById('modal-tambah-card');
            const form = document.getElementById('form-tambah-jadwal');
            const trigger = document.getElementById('buka-tambah-jadwal');
            const timeError = document.getElementById('tambah-waktu-error');
            let previousFocus = null;
            function openModal() {
                previousFocus = document.activeElement;
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                card.focus();
            }
            function closeModal() {
                modal.hidden = true;
                document.body.style.overflow = '';
                if (previousFocus?.focus) previousFocus.focus();
            }
            trigger.addEventListener('click', openModal);
            document.getElementById('tutup-tambah-jadwal').addEventListener('click', closeModal);
            document.getElementById('batal-tambah-jadwal').addEventListener('click', closeModal);
            modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
            document.addEventListener('keydown', event => {
                if (modal.hidden) return;
                if (event.key === 'Escape') { closeModal(); return; }
                if (event.key === 'Tab') {
                    const focusable = [...card.querySelectorAll('button:not([disabled]),select:not([disabled]),input:not([disabled])')].filter(el => el.getClientRects().length);
                    if (!focusable.length) return;
                    const first = focusable[0], last = focusable[focusable.length - 1];
                    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
                }
            });
            form.addEventListener('submit', event => {
                const mulai = document.getElementById('tambah-mulai').value;
                const selesai = document.getElementById('tambah-selesai').value;
                if (mulai && selesai && selesai <= mulai) {
                    event.preventDefault();
                    timeError.classList.remove('hidden');
                    document.getElementById('tambah-selesai').focus();
                } else { timeError.classList.add('hidden'); }
            });
            <?php if ($gagalSimpan): ?>
            // Tampilkan form lagi ketika validasi server gagal, tanpa menghapus input sebelumnya.
            <?php foreach (['id_matkul','id_dosen','id_ruangan','hari','jam_mulai','jam_selesai','jenis_pertemuan'] as $name): ?>
            <?php if (isset($_POST[$name]) && is_scalar($_POST[$name])): ?>
            {
                const field = form.elements[<?= json_encode($name) ?>];
                if (field) field.value = <?= json_encode((string)$_POST[$name], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
            }
            <?php endif; ?>
            <?php endforeach; ?>
            openModal();
            <?php endif; ?>
        })();
        </script>
        <?php endif; ?>

    </body>
</html>