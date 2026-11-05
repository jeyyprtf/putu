<?php
/**
 * Operasi tugas dan progres pribadi mahasiswa.
 *
 * Semua SQL tugas ada di file ini, jadi halaman cukup memanggil fungsinya.
 * Aturan keamanan:
 *   - daftar tugas dibatasi id_kelas dari sesi;
 *   - mengubah progres hanya untuk tugas yang memang ada di kelas user;
 *   - status hanya boleh todo / doing / done.
 */

/**
 * Daftar tugas satu kelas, lengkap dengan status progres user yang sedang login.
 *
 * Hasilnya sengaja dibentuk sama dengan data contoh di app/demo.php
 * (id, title, course, code, type, description, deadline, status) supaya
 * komponen task-card.php dan app.js tidak perlu diubah bentuk datanya.
 *
 * deadline dikirim sebagai tanggal (YYYY-MM-DD) waktu Asia/Jakarta, bukan
 * timestamp penuh, karena tampilan dan label deadline hanya butuh tanggal.
 */
function tugas_daftar(int $idUser, int $kelasId): array
{
    $baris = db_all(
        "SELECT t.id_tugas, t.judul_tugas, t.deskripsi, t.jenis_tugas,
                to_char(t.deadline AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD') AS deadline,
                m.nama_matkul,
                COALESCE(p.status, 'todo') AS status
         FROM tugas AS t
         JOIN mata_kuliah AS m ON m.id_matkul = t.id_matkul
         LEFT JOIN progres_tugas AS p
                ON p.id_tugas = t.id_tugas AND p.id_user = :user
         WHERE m.id_kelas = :kelas
         ORDER BY t.deadline ASC, t.id_tugas ASC",
        ['user' => $idUser, 'kelas' => $kelasId]
    );

    $labelJenis = ['tugas' => 'Tugas', 'praktikum' => 'Praktikum', 'proyek' => 'Proyek'];

    return array_map(fn (array $b): array => [
        'id'          => (int) $b['id_tugas'],
        'title'       => $b['judul_tugas'],
        'course'      => $b['nama_matkul'],
        'code'        => '',
        'type'        => $labelJenis[$b['jenis_tugas']] ?? $b['jenis_tugas'],
        'description' => $b['deskripsi'],
        'deadline'    => $b['deadline'],
        'status'      => $b['status'],
    ], $baris);
}

/**
 * Hitung ringkasan tugas (total, aktif, mendesak, santai, per status).
 * Dipakai halaman tugas dan nanti dashboard, supaya angkanya selalu sama
 * dengan daftar yang ditampilkan.
 */
function tugas_hitung(array $tasks): array
{
    $hitung = [
        'all'      => count($tasks),
        'active'   => 0,
        'priority' => 0,
        'calm'     => 0,
        'todo'     => 0,
        'doing'    => 0,
        'done'     => 0,
    ];

    // Mendesak = deadline dalam 2 hari atau sudah lewat.
    $batasMendesak = (new DateTimeImmutable('today'))->modify('+2 days')->format('Y-m-d');

    foreach ($tasks as $task) {
        $hitung[$task['status']]++;

        if ($task['status'] !== 'done') {
            $hitung['active']++;
            if ($task['deadline'] <= $batasMendesak) {
                $hitung['priority']++;
            }
        }
    }

    $hitung['calm'] = $hitung['active'] - $hitung['priority'];

    return $hitung;
}

/**
 * Simpan status progres satu tugas untuk satu user.
 *
 * Tabel progres_tugas punya constraint: status 'done' wajib punya selesai_at,
 * status lain wajib NULL. Karena itu selesai_at diisi dalam satu query yang sama.
 *
 * Unique (id_user, id_tugas) membuat kita memakai UPSERT: kalau baris progres
 * belum ada (masih default 'todo' dari view) maka dibuat, kalau sudah ada
 * maka diperbarui.
 *
 * @return bool false kalau tugas tidak ada di kelas user (mis. id dipalsukan)
 */
function progres_set(int $idUser, int $idTugas, string $status): bool
{
    if (!in_array($status, ['todo', 'doing', 'done'], true)) {
        throw new InvalidArgumentException('Status tugas tidak dikenal.');
    }

    // Cek kepemilikan: tugas harus terhubung ke kelas tempat user berada.
    // Ini mencegah user mengubah progres tugas kelas lain lewat id karangan.
    $milik = db_one(
        'SELECT 1 AS ada
         FROM tugas AS t
         JOIN mata_kuliah AS m ON m.id_matkul = t.id_matkul
         JOIN users AS u ON u.id_kelas = m.id_kelas
         WHERE u.id_user = :user AND t.id_tugas = :tugas',
        ['user' => $idUser, 'tugas' => $idTugas]
    );

    if ($milik === null) {
        return false;
    }

    // null dikirim apa adanya; PDO mengubahnya menjadi NULL di PostgreSQL.
    $selesai = $status === 'done' ? (new DateTimeImmutable())->format('Y-m-d H:i:sP') : null;

    db_exec(
        'INSERT INTO progres_tugas (id_user, id_tugas, status, selesai_at)
         VALUES (:user, :tugas, :status, :selesai)
         ON CONFLICT (id_user, id_tugas)
         DO UPDATE SET status = EXCLUDED.status, selesai_at = EXCLUDED.selesai_at',
        [
            'user'    => $idUser,
            'tugas'   => $idTugas,
            'status'  => $status,
            'selesai' => $selesai,
        ]
    );

    return true;
}
