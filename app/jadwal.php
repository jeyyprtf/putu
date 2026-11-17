
<?php
/**
 * Fungsi untuk membaca jadwal kuliah dari database.
 *
 * Tabel yang digunakan:
 * - jadwal_kuliah
 * - mata_kuliah
 * - dosen
 * - ruangan
 *
 * Jadwal dibatasi berdasarkan id_kelas.
 * id_kelas harus diperoleh dari sesi pengguna.
 */

/**
 * Mengambil seluruh jadwal berdasarkan kelas.
 *
 * @param int $kelasId ID kelas pengguna
 * @return array Daftar jadwal kuliah
 */
function jadwal_daftar(int $kelasId): array
{
    $baris = db_all(
        "SELECT
            j.id_jadwal,
            j.hari,
            to_char(j.jam_mulai, 'HH24:MI') AS jam_mulai,
            to_char(j.jam_selesai, 'HH24:MI') AS jam_selesai,
            j.jenis_pertemuan,
            m.nama_matkul,
            d.nama_dosen,
            r.nama_ruangan,
            r.gedung
         FROM jadwal_kuliah AS j
         JOIN mata_kuliah AS m
            ON m.id_matkul = j.id_matkul
         JOIN dosen AS d
            ON d.id_dosen = j.id_dosen
         JOIN ruangan AS r
            ON r.id_ruangan = j.id_ruangan
         WHERE m.id_kelas = :kelas
         ORDER BY
            j.hari ASC,
            j.jam_mulai ASC,
            j.id_jadwal ASC",
        [
            'kelas' => $kelasId
        ]
    );

    $labelJenis = [
        'teori' => 'Teori',
        'praktikum' => 'Praktikum'
    ];

    return array_map(
        fn(array $b): array => [
            'id' => (int) $b['id_jadwal'],

            // Database: 1-7
            // Frontend: 0-6
            'day' => max(
                0,
                min(6, (int) $b['hari'] - 1)
            ),

            'start' => $b['jam_mulai'],
            'end' => $b['jam_selesai'],
            'course' => $b['nama_matkul'],
            'code' => '',
            'room' => $b['nama_ruangan']
                . ' · '
                . $b['gedung'],
            'lecturer' => $b['nama_dosen'],
            'type' => $labelJenis[
                $b['jenis_pertemuan']
            ] ?? $b['jenis_pertemuan'],
        ],
        $baris
    );
}

/**
 * Mengelompokkan jadwal berdasarkan hari.
 *
 * Index:
 * 0 = Senin
 * 1 = Selasa
 * 2 = Rabu
 * 3 = Kamis
 * 4 = Jumat
 * 5 = Sabtu
 * 6 = Minggu
 *
 * @param array $jadwal
 * @return array
 */
function jadwal_per_hari(array $jadwal): array
{
    $perHari = array_fill(0, 7, []);

    foreach ($jadwal as $kuliah) {
        $hari = (int) $kuliah['day'];

        if ($hari >= 0 && $hari <= 6) {
            $perHari[$hari][] = $kuliah;
        }
    }

    return $perHari;
}
