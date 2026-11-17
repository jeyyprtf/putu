<?php
$page='jadwal';
$title='Kelola jadwal';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../jadwal_admin.php';
$user=require_login();
if (($user['role']??'')!=='admin') { http_response_code(403); exit('403 - Khusus admin kelas.'); }
$kelasId=(int)$user['id_kelas'];
$error='';
if (is_post()) {
    if (!csrf_verify()) {
        $error='Token keamanan tidak valid. Muat ulang halaman.';
    } else {
        try {
            $aksi=post_str('aksi');
            $raw=post_str('id_jadwal');
            $id=ctype_digit($raw) && strlen($raw)<=18 ? (int)$raw : 0;
            if ($aksi==='simpan') {
                jadwal_admin_simpan($kelasId,$id,$_POST);
            } elseif ($aksi==='hapus') {
                if (!jadwal_admin_hapus($kelasId,$id)) throw new InvalidArgumentException('Jadwal tidak ditemukan.');
            } else throw new InvalidArgumentException('Aksi tidak dikenal.');
            redirect(url('admin/jadwal', ['ok' => 1]));
        } catch (InvalidArgumentException $e) { $error=$e->getMessage(); }
          catch (Throwable $e) { error_log('[jadwal-admin] '.$e->getMessage()); $error='Gagal menyimpan perubahan. Coba lagi.'; }
    }
}
$opsi = ['matkul' => [], 'dosen' => [], 'ruangan' => []];
$jadwal = [];

// Pisahkan kedua operasi untuk menentukan query mana yang gagal.
try {
    $opsi = jadwal_opsi($kelasId);
} catch (Throwable $e) {
    error_log('[jadwal-admin][opsi] ' . get_class($e) . ': ' . $e->getMessage());
    $error = 'Gagal memuat pilihan mata kuliah, dosen, atau ruangan. Periksa log PHP [jadwal-admin][opsi].';
}

try {
    $jadwal = jadwal_admin_daftar($kelasId);
} catch (Throwable $e) {
    error_log('[jadwal-admin][daftar] ' . get_class($e) . ': ' . $e->getMessage());
    $error .= ($error !== '' ? ' ' : '')
        . 'Gagal memuat daftar jadwal. Periksa log PHP [jadwal-admin][daftar].';
}
$hari=['','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php require __DIR__ . '/../../components/head.php'; ?>
</head>

<body class="bg-stone-950 text-stone-100 font-sans antialiased">

    <!-- SIDEBAR -->
    <?php require __DIR__ . '/../../components/sidebar.php'; ?>

    <div class="lg:ml-68">

        <!-- NAVBAR -->
        <?php require __DIR__ . '/../../components/navbar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="workspace-content mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10 lg:py-10">

            <!-- HEADER -->
            <div class="mb-8">
                <h1 class="text-3xl font-semibold mb-4">
                    Kelola jadwal kuliah
                </h1>

                <a
                    href="<?= e(url('jadwal')) ?>"
                    class="text-lime-300 hover:text-lime-200 underline"
                >
                    &larr; Kembali ke jadwal
                </a>
            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['ok'])): ?>
                <div
                    role="status"
                    class="mb-6 rounded-xl border border-lime-500/30 bg-lime-500/10 p-4 text-lime-300"
                >
                    Perubahan jadwal berhasil disimpan.
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div
                    role="alert"
                    class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-red-300"
                >
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <!-- FORM TAMBAH / EDIT JADWAL -->
            <section class="panel mb-8 p-6">

                <div class="mb-6">
                    <h2 class="text-xl font-semibold">
                        Tambah / edit jadwal
                    </h2>

                    <p class="mt-2 text-sm text-stone-400">
                        Atur mata kuliah, dosen, ruangan, dan waktu perkuliahan.
                    </p>
                </div>

                <form
                    method="post"
                    id="form-jadwal"
                    class="grid grid-cols-1 gap-5 md:grid-cols-2"
                >
                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="aksi"
                        value="simpan"
                    >

                    <input
                        type="hidden"
                        name="id_jadwal"
                        id="id_jadwal"
                        value="0"
                    >

                    <!-- MATA KULIAH -->
                    <div>
                        <label for="id_matkul" class="mb-2 block text-sm font-medium">
                            Mata kuliah
                        </label>

                        <select
                            required
                            name="id_matkul"
                            id="id_matkul"
                            class="w-full rounded-xl border border-stone-700 bg-stone-900 px-4 py-3"
                        >
                            <option value="">Pilih mata kuliah</option>

                            <?php foreach ($opsi['matkul'] ?? [] as $o): ?>
                                <option value="<?= (int) $o['id_matkul'] ?>">
                                    <?= e($o['nama_matkul']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- DOSEN -->
                    <div>
                        <label for="id_dosen" class="mb-2 block text-sm font-medium">
                            Dosen
                        </label>

                        <select
                            required
                            name="id_dosen"
                            id="id_dosen"
                            class="w-full rounded-xl border border-stone-700 bg-stone-900 px-4 py-3"
                        >
                            <option value="">Pilih dosen</option>

                            <?php foreach ($opsi['dosen'] ?? [] as $o): ?>
                                <option value="<?= (int) $o['id_dosen'] ?>">
                                    <?= e($o['nama_dosen']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- RUANGAN -->
                    <div>
                        <label for="id_ruangan" class="mb-2 block text-sm font-medium">
                            Ruangan
                        </label>

                        <select
                            required
                            name="id_ruangan"
                            id="id_ruangan"
                            class="w-full rounded-xl border border-stone-700 bg-stone-900 px-4 py-3"
                        >
                            <option value="">Pilih ruangan</option>

                            <?php foreach ($opsi['ruangan'] ?? [] as $o): ?>
                                <option value="<?= (int) $o['id_ruangan'] ?>">
                                    <?= e($o['gedung'] . ' · ' . $o['nama_ruangan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- HARI -->
                    <div>
                        <label for="hari" class="mb-2 block text-sm font-medium">
                            Hari
                        </label>

                        <select
                            required
                            name="hari"
                            id="hari"
                            class="w-full rounded-xl border border-stone-700 bg-stone-900 px-4 py-3"
                        >
                            <?php for ($i = 1; $i <= 7; $i++): ?>
                                <option value="<?= $i ?>">
                                    <?= e($hari[$i]) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- JAM MULAI -->
                    <div>
                        <label for="jam_mulai" class="mb-2 block text-sm font-medium">
                            Jam mulai
                        </label>

                        <input
                            required
                            type="time"
                            name="jam_mulai"
                            id="jam_mulai"
                            class="w-full rounded-xl border border-stone-700 bg-stone-900 px-4 py-3"
                        >
                    </div>

                    <!-- JAM SELESAI -->
                    <div>
                        <label for="jam_selesai" class="mb-2 block text-sm font-medium">
                            Jam selesai
                        </label>

                        <input
                            required
                            type="time"
                            name="jam_selesai"
                            id="jam_selesai"
                            class="w-full rounded-xl border border-stone-700 bg-stone-900 px-4 py-3"
                        >
                    </div>

                    <!-- JENIS PERTEMUAN -->
                    <div>
                        <label for="jenis_pertemuan" class="mb-2 block text-sm font-medium">
                            Jenis pertemuan
                        </label>

                        <select
                            name="jenis_pertemuan"
                            id="jenis_pertemuan"
                            class="w-full rounded-xl border border-stone-700 bg-stone-900 px-4 py-3"
                        >
                            <option value="teori">Teori</option>
                            <option value="praktikum">Praktikum</option>
                        </select>
                    </div>

                    <!-- TOMBOL AKSI -->
                    <div class="flex flex-wrap items-end gap-3">

                        <button
                            type="submit"
                            class="rounded-xl bg-lime-300 px-5 py-3 font-semibold text-stone-950 hover:bg-lime-200"
                        >
                            Simpan jadwal
                        </button>

                        <button
                            type="button"
                            id="reset-jadwal"
                            class="rounded-xl border border-stone-600 px-5 py-3 hover:bg-stone-800"
                        >
                            Batal edit
                        </button>

                    </div>
                </form>
            </section>

            <!-- DAFTAR JADWAL -->
            <section class="panel p-6">

                <div class="mb-6">
                    <h2 class="text-xl font-semibold">
                        Jadwal kelas
                    </h2>

                    <p class="mt-2 text-sm text-stone-400">
                        Daftar jadwal kuliah yang sudah tersimpan.
                    </p>
                </div>

                <div class="overflow-x-auto">

                    <table class="w-full text-left text-sm">

                        <thead>
                            <tr class="border-b border-stone-700 text-stone-400">
                                <th class="p-3">Hari & jam</th>
                                <th class="p-3">Mata kuliah</th>
                                <th class="p-3">Dosen</th>
                                <th class="p-3">Ruangan</th>
                                <th class="p-3">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($jadwal as $j): ?>

                                <?php
                                $dataEdit = [
                                    'id_jadwal'       => (int) $j['id_jadwal'],
                                    'id_matkul'       => (int) $j['id_matkul'],
                                    'id_dosen'       => (int) $j['id_dosen'],
                                    'id_ruangan'     => (int) $j['id_ruangan'],
                                    'hari'           => (int) $j['hari'],
                                    'jam_mulai'      => $j['mulai'],
                                    'jam_selesai'    => $j['selesai'],
                                    'jenis_pertemuan'=> $j['jenis_pertemuan']
                                ];
                                ?>

                                <tr class="border-b border-stone-800">

                                    <td class="p-3">
                                        <?= e($hari[(int) $j['hari']]) ?>,
                                        <?= e($j['mulai']) ?>–
                                        <?= e($j['selesai']) ?>
                                    </td>

                                    <td class="p-3">
                                        <?= e($j['nama_matkul']) ?>
                                    </td>

                                    <td class="p-3">
                                        <?= e($j['nama_dosen']) ?>
                                    </td>

                                    <td class="p-3">
                                        <?= e($j['gedung'] . ' · ' . $j['nama_ruangan']) ?>
                                    </td>

                                    <td class="p-3">
                                        <div class="flex items-center gap-4">

                                            <!-- EDIT -->
                                            <button
                                                type="button"
                                                class="edit-jadwal text-lime-300 hover:underline"
                                                data-jadwal="<?= htmlspecialchars(
                                                    json_encode($dataEdit, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >
                                                Edit
                                            </button>

                                            <!-- HAPUS -->
                                            <form
                                                method="post"
                                                onsubmit="return confirm('Hapus jadwal ini?')"
                                            >
                                                <?= csrf_field() ?>

                                                <input
                                                    type="hidden"
                                                    name="aksi"
                                                    value="hapus"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_jadwal"
                                                    value="<?= (int) $j['id_jadwal'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="text-red-300 hover:underline"
                                                >
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                            <?php if (!$jadwal): ?>
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-stone-400">
                                        Belum ada jadwal kuliah.
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <?php require __DIR__ . '/../../components/dock.php'; ?>

    <!-- JAVASCRIPT -->
    <script>
        const formJadwal = document.getElementById('form-jadwal');
        const inputIdJadwal = document.getElementById('id_jadwal');

        // Mengisi form ketika tombol Edit diklik
        document.querySelectorAll('.edit-jadwal').forEach(button => {
            button.addEventListener('click', () => {

                const data = JSON.parse(button.dataset.jadwal);

                Object.entries(data).forEach(([key, value]) => {
                    const field = document.getElementById(key);

                    if (field) {
                        field.value = value;
                    }
                });

                formJadwal.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        // Mengembalikan form ke mode tambah jadwal
        document.getElementById('reset-jadwal').addEventListener('click', () => {
            formJadwal.reset();
            inputIdJadwal.value = '0';
        });
    </script>

</body>
</html>
