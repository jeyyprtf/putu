<?php
/**
 * Kelola jadwal PUTU: satu halaman dengan modal tambah/edit.
 * Disesuaikan dengan struktur asli putu(2).zip (backend: app/adminJadwal.php).
 */
$page = 'jadwal';
$title = 'Kelola jadwal';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../adminJadwal.php';

$user = require_login();
if (($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('403 - Khusus admin kelas.');
}
$kelasId = (int) ($user['id_kelas'] ?? 0);
if ($kelasId < 1) {
    http_response_code(403);
    exit('403 - Akun belum tergabung dalam kelas.');
}

$error = '';
$formError = false;
if (is_post()) {
    if (!csrf_verify()) {
        $error = 'Token keamanan tidak valid. Muat ulang halaman.';
        $formError = true;
    } else {
        try {
            $aksi = post_str('aksi');
            $raw = post_str('id_jadwal');
            $id = ctype_digit($raw) && strlen($raw) <= 18 ? (int) $raw : 0;
            if ($aksi === 'simpan') {
                jadwal_admin_simpan($kelasId, $id, $_POST);
            } elseif ($aksi === 'hapus') {
                if (!jadwal_admin_hapus($kelasId, $id)) {
                    throw new InvalidArgumentException('Jadwal tidak ditemukan.');
                }
            } else {
                throw new InvalidArgumentException('Aksi tidak dikenal.');
            }
            redirect(url('admin/jadwal', ['ok' => 1]));
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
            $formError = $aksi === 'simpan';
        } catch (Throwable $e) {
            error_log('[jadwal-admin][simpan] ' . get_class($e) . ': ' . $e->getMessage());
            $error = 'Gagal menyimpan perubahan. Periksa log server PHP.';
            $formError = ($aksi ?? '') === 'simpan';
        }
    }
}

$opsi = ['matkul' => [], 'dosen' => [], 'ruangan' => []];
$jadwal = [];
$opsiGagal = false;
try {
    $opsi = jadwal_opsi($kelasId);
} catch (Throwable $e) {
    error_log('[jadwal-admin][opsi] ' . get_class($e) . ': ' . $e->getMessage());
    $opsiGagal = true;
    $error .= ($error !== '' ? ' ' : '') . 'Gagal memuat pilihan mata kuliah, dosen, atau ruangan.';
}
try {
    $jadwal = jadwal_admin_daftar($kelasId);
} catch (Throwable $e) {
    error_log('[jadwal-admin][daftar] ' . get_class($e) . ': ' . $e->getMessage());
    $error .= ($error !== '' ? ' ' : '') . 'Gagal memuat daftar jadwal.';
}
$hari = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$oldForm = $formError ? [
    'id_jadwal' => post_str('id_jadwal'),
    'id_matkul' => post_str('id_matkul'),
    'id_dosen' => post_str('id_dosen'),
    'id_ruangan' => post_str('id_ruangan'),
    'hari' => post_str('hari'),
    'jam_mulai' => post_str('jam_mulai'),
    'jam_selesai' => post_str('jam_selesai'),
    'jenis_pertemuan' => post_str('jenis_pertemuan'),
] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require __DIR__ . '/../../components/head.php'; ?>
    <style>
        .jadwal-modal[hidden] { display: none !important; }
        .jadwal-modal { position: fixed; inset: 0; z-index: 100; display: flex; justify-content: center; align-items: center; padding: 20px; background: rgba(0,0,0,.78); backdrop-filter: blur(5px); }
        .jadwal-modal__card { width: min(100%, 980px); max-height: min(92dvh, 900px); overflow-y: auto; border-radius: 24px; border: 1px solid #44403c; background: #100f0e; box-shadow: 0 30px 100px rgba(0,0,0,.7); }
        .jadwal-modal__grid { display: grid; grid-template-columns: minmax(0,1.8fr) minmax(230px,1fr); gap: 18px; }
        .jadwal-modal__field { width: 100%; min-width: 0; min-height: 46px; border: 1px solid #44403c; border-radius: 11px; background: #171513; color: #fafaf9; padding: 10px 12px; color-scheme: dark; }
        .jadwal-modal__field:focus { outline: 2px solid #a3e635; outline-offset: 1px; }
        .jadwal-modal__label { display: flex; flex-direction: column; gap: 9px; font-size: 13px; color: #e7e5e4; }
        .jadwal-modal__panel { border: 1px solid #383330; border-radius: 16px; background: #1c1917; padding: 23px; }
        @media(max-width: 700px) { .jadwal-modal { padding: 10px; align-items: flex-start; } .jadwal-modal__card { max-height: calc(100dvh - 20px); } .jadwal-modal__grid { grid-template-columns: 1fr; } .jadwal-modal__panel { padding: 16px; } }
    </style>
</head>
<body class="bg-stone-950 text-stone-100 font-sans antialiased">
<?php require __DIR__ . '/../../components/sidebar.php'; ?>
<div class="lg:ml-68">
    <?php require __DIR__ . '/../../components/navbar.php'; ?>
    <main class="workspace-content mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-5">
            <div>
                <a href="<?= e(url('jadwal')) ?>" class="mb-4 inline-flex items-center gap-2 text-sm text-stone-400 hover:text-lime-300">&larr; Kembali ke jadwal</a>
                <p class="text-xs font-semibold uppercase tracking-widest text-lime-400">Admin kelas</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Kelola jadwal kuliah<span class="text-lime-400">.</span></h1>
                <p class="mt-2 text-sm text-stone-400">Kelola mata kuliah, waktu, dosen dan ruangan kelasmu.</p>
            </div>
            <button type="button" id="tambah-jadwal" class="rounded-xl bg-lime-400 px-5 py-3 font-semibold text-stone-950 transition hover:bg-lime-300">+ Tambah jadwal</button>
        </div>
        <?php if (isset($_GET['ok'])): ?>
            <p role="status" class="mb-5 rounded-xl border border-lime-500/30 bg-lime-500/10 px-5 py-4 text-lime-300">Perubahan jadwal berhasil disimpan.</p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p role="alert" class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-red-300"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if (!$opsiGagal && empty($opsi['matkul'])): ?>
            <p role="status" class="mb-5 rounded-xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-amber-200">
                Belum ada mata kuliah untuk kelas aktif (ID: <?= (int) $kelasId ?>).
                Tambahkan mata kuliah dengan id_kelas yang sesuai di database, atau periksa kelas akun admin yang sedang login.
            </p>
        <?php endif; ?>
        <?php if (!$opsiGagal && (empty($opsi['dosen']) || empty($opsi['ruangan']))): ?>
            <p role="status" class="mb-5 rounded-xl border border-amber-500/30 bg-amber-500/10 px-5 py-4 text-amber-200">
                Data <?= empty($opsi['dosen']) ? 'dosen' : '' ?><?= empty($opsi['dosen']) && empty($opsi['ruangan']) ? ' dan ' : '' ?><?= empty($opsi['ruangan']) ? 'ruangan' : '' ?> masih kosong di database.
            </p>
        <?php endif; ?>
        <section class="panel rounded-2xl p-5 sm:p-7">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="text-xl font-semibold">Jadwal kelas</h2><p class="mt-1 text-sm text-stone-400">Daftar jadwal yang sudah diterbitkan.</p></div>
                <span class="rounded-full border border-stone-700 px-3 py-1 text-xs text-stone-300"><?= count($jadwal) ?> jadwal</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[660px] text-left text-sm">
                    <thead><tr class="border-b border-stone-700 text-stone-400"><th class="px-3 py-4">Hari &amp; jam</th><th class="px-3 py-4">Mata kuliah</th><th class="px-3 py-4">Dosen</th><th class="px-3 py-4">Ruangan</th><th class="px-3 py-4">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($jadwal as $j): ?>
                        <?php $dataEdit = [
                            'id_jadwal' => (int) $j['id_jadwal'],
                            'id_matkul' => (int) $j['id_matkul'],
                            'id_dosen' => (int) $j['id_dosen'],
                            'id_ruangan' => (int) $j['id_ruangan'],
                            'hari' => (int) $j['hari'],
                            'jam_mulai' => $j['mulai'],
                            'jam_selesai' => $j['selesai'],
                            'jenis_pertemuan' => $j['jenis_pertemuan'],
                        ]; ?>
                        <tr class="border-b border-stone-800 last:border-0">
                            <td class="px-3 py-4 whitespace-nowrap"><?= e($hari[(int)$j['hari']] ?? '—') ?> · <?= e($j['mulai']) ?>–<?= e($j['selesai']) ?></td>
                            <td class="px-3 py-4 font-medium"><?= e($j['nama_matkul']) ?></td>
                            <td class="px-3 py-4 text-stone-300"><?= e($j['nama_dosen']) ?></td>
                            <td class="px-3 py-4 text-stone-300"><?= e($j['gedung'] . ' · ' . $j['nama_ruangan']) ?></td>
                            <td class="px-3 py-4"><div class="flex gap-4 items-center">
                                <button type="button" class="edit-jadwal font-medium text-lime-300 hover:underline" data-jadwal="<?= e(json_encode($dataEdit, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">Edit</button>
                                <form method="post" onsubmit="return confirm('Hapus jadwal ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id_jadwal" value="<?= (int)$j['id_jadwal'] ?>">
                                    <button type="submit" class="text-red-300 hover:underline">Hapus</button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$jadwal): ?><tr><td colspan="5" class="px-4 py-12 text-center text-stone-400">Belum ada jadwal kuliah. Klik “Tambah jadwal” untuk mulai.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php require __DIR__ . '/../../components/dock.php'; ?>

<!-- Modal tetap dalam halaman yang sama; tidak ada navigasi ke halaman baru. -->
<div id="modal-jadwal" class="jadwal-modal" hidden>
    <section id="modal-jadwal-card" class="jadwal-modal__card" role="dialog" aria-modal="true" aria-labelledby="judul-modal" tabindex="-1">
        <div class="flex items-start justify-between gap-4 border-b border-stone-800 px-5 py-5 sm:px-7">
            <div><p class="text-xs font-semibold uppercase tracking-widest text-lime-400">Administrator</p><h2 id="judul-modal" class="mt-2 text-2xl font-semibold">Tambah jadwal</h2><p class="mt-2 text-sm text-stone-400">Atur waktu dan tempat perkuliahan.</p></div>
            <button id="tutup-modal" type="button" aria-label="Tutup popup" class="rounded-lg border border-stone-700 px-3 py-2 text-stone-300 hover:bg-stone-800">✕</button>
        </div>
        <div class="p-4 sm:p-6">
            <form method="post" id="form-jadwal">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="simpan">
                <input type="hidden" id="id_jadwal" name="id_jadwal" value="0">
                <div class="jadwal-modal__grid">
                    <div class="jadwal-modal__panel">
                        <h3 class="mb-5 text-lg font-semibold">Informasi perkuliahan</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <label class="jadwal-modal__label sm:col-span-2" for="id_matkul">Mata kuliah
                                <select class="jadwal-modal__field" id="id_matkul" name="id_matkul" required><option value=""><?= empty($opsi['matkul']) ? 'Belum ada mata kuliah untuk kelas ini' : 'Pilih mata kuliah' ?></option><?php foreach ($opsi['matkul'] ?? [] as $o): ?><option value="<?= (int)$o['id_matkul'] ?>"><?= e($o['nama_matkul']) ?></option><?php endforeach; ?></select>
                            </label>
                            <label class="jadwal-modal__label" for="hari">Hari
                                <select class="jadwal-modal__field" id="hari" name="hari" required><?php for ($i=1;$i<=7;$i++): ?><option value="<?= $i ?>"><?= e($hari[$i]) ?></option><?php endfor; ?></select>
                            </label>
                            <label class="jadwal-modal__label" for="jenis_pertemuan">Jenis kelas
                                <select class="jadwal-modal__field" id="jenis_pertemuan" name="jenis_pertemuan" required><option value="teori">Teori</option><option value="praktikum">Praktikum</option></select>
                            </label>
                            <label class="jadwal-modal__label" for="jam_mulai">Jam mulai
                                <input class="jadwal-modal__field" type="time" id="jam_mulai" name="jam_mulai" required>
                            </label>
                            <label class="jadwal-modal__label" for="jam_selesai">Jam selesai
                                <input class="jadwal-modal__field" type="time" id="jam_selesai" name="jam_selesai" required>
                            </label>
                            <label class="jadwal-modal__label sm:col-span-2" for="id_ruangan">Ruang kelas
                                <select class="jadwal-modal__field" id="id_ruangan" name="id_ruangan" required><option value=""><?= empty($opsi['ruangan']) ? 'Belum ada ruangan' : 'Pilih ruangan' ?></option><?php foreach ($opsi['ruangan'] ?? [] as $o): ?><option value="<?= (int)$o['id_ruangan'] ?>"><?= e($o['gedung'] . ' · ' . $o['nama_ruangan']) ?></option><?php endforeach; ?></select>
                            </label>
                            <label class="jadwal-modal__label sm:col-span-2" for="id_dosen">Dosen pengampu
                                <select class="jadwal-modal__field" id="id_dosen" name="id_dosen" required><option value=""><?= empty($opsi['dosen']) ? 'Belum ada dosen' : 'Pilih dosen' ?></option><?php foreach ($opsi['dosen'] ?? [] as $o): ?><option value="<?= (int)$o['id_dosen'] ?>"><?= e($o['nama_dosen']) ?></option><?php endforeach; ?></select>
                            </label>
                        </div>
                    </div>
                    <aside class="jadwal-modal__panel self-start">
                        <h3 class="mb-3 text-lg font-semibold">Periksa jadwal</h3>
                        <p class="mb-5 text-sm leading-6 text-stone-400">Gunakan format 24 jam (WIB). Pastikan hari, jam, dosen, dan ruangan sudah sesuai sebelum menerbitkan.</p>
                        <div class="mb-5 border-t border-stone-700 pt-4"><span class="text-xs text-stone-400">Kelas</span><div class="mt-1 font-semibold"><?= e($user['nama_kelas'] ?? 'Kelas aktif') ?></div><p class="mt-1 text-xs text-stone-400">ID kelas: <?= (int) $kelasId ?></p></div>
                        <button id="simpan-jadwal" type="submit" class="w-full rounded-xl bg-lime-500 px-4 py-3 font-semibold text-stone-950 transition hover:bg-lime-400">Terbitkan jadwal</button>
                        <button id="batal-modal" type="button" class="mt-3 w-full rounded-xl border border-stone-700 px-4 py-3 text-stone-200 hover:bg-stone-800">Batal</button>
                        <p id="pesan-waktu" role="alert" class="mt-3 hidden text-sm text-red-300">Jam selesai harus setelah jam mulai.</p>
                    </aside>
                </div>
            </form>
        </div>
    </section>
</div>
<script>
(() => {
    const modal = document.getElementById('modal-jadwal');
    const card = document.getElementById('modal-jadwal-card');
    const form = document.getElementById('form-jadwal');
    const closeButtons = [document.getElementById('tutup-modal'), document.getElementById('batal-modal')];
    const title = document.getElementById('judul-modal');
    const submit = document.getElementById('simpan-jadwal');
    const timeError = document.getElementById('pesan-waktu');
    let previousFocus = null;
    const keys = ['id_jadwal', 'id_matkul', 'id_dosen', 'id_ruangan', 'hari', 'jam_mulai', 'jam_selesai', 'jenis_pertemuan'];
    function open(data = null) {
        previousFocus = document.activeElement;
        form.reset();
        document.getElementById('id_jadwal').value = '0';
        if (data) for (const key of keys) {
            if (Object.prototype.hasOwnProperty.call(data, key)) document.getElementById(key).value = data[key];
        }
        title.textContent = data && Number(data.id_jadwal) > 0 ? 'Edit jadwal' : 'Tambah jadwal';
        submit.textContent = data && Number(data.id_jadwal) > 0 ? 'Simpan perubahan' : 'Terbitkan jadwal';
        timeError.classList.add('hidden');
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        card.focus();
    }
    function close() {
        modal.hidden = true;
        document.body.style.overflow = '';
        if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
    }
    document.getElementById('tambah-jadwal').addEventListener('click', () => open());
    document.querySelectorAll('.edit-jadwal').forEach(button => {
        button.addEventListener('click', () => {
            try { open(JSON.parse(button.dataset.jadwal)); }
            catch (error) { console.error('Data edit jadwal tidak valid', error); }
        });
    });
    closeButtons.forEach(button => button.addEventListener('click', close));
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => {
        if (modal.hidden) return;
        if (event.key === 'Escape') { close(); return; }
        if (event.key === 'Tab') {
            const focusable = Array.from(card.querySelectorAll('button:not([disabled]), select:not([disabled]), input:not([disabled])')).filter(el => el.offsetParent !== null);
            if (!focusable.length) return;
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    form.addEventListener('submit', event => {
        const start = document.getElementById('jam_mulai').value;
        const end = document.getElementById('jam_selesai').value;
        if (start && end && end <= start) {
            event.preventDefault();
            timeError.classList.remove('hidden');
            document.getElementById('jam_selesai').focus();
        }
    });
    <?php if ($oldForm !== null): ?>
    // Jika validasi server menolak POST, buka kembali modal dengan input terakhir.
    open(<?= json_encode($oldForm, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>);
    <?php endif; ?>
})();
</script>
</body>
</html>
