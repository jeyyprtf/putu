<?php
/**
 * Jadwal kuliah satu kelas, dibaca dari jadwal_kuliah + mata_kuliah + dosen + ruangan.
 *
 * Semua SQL jadwal ada di file ini, jadi halaman cukup memanggil fungsinya.
 * Aturan keamanan dipegang oleh pemanggil (app/pages/jadwal.php):
 *   - id_kelas SELALU dari sesi, bukan dari URL/form;
 *   - jadwal hanya diambil lewat mata_kuliah milik kelas itu.
 *
 * Catatan: dosen dan ruangan tidak punya id_kelas (dipakai bersama), jadi pembatasan
 * kelas dilakukan lewat mata_kuliah.id_kelas.
 */

/**
 * Semua jadwal satu kelas, urut hari lalu jam mulai.
 *
 * Bentuk tiap baris sengaja sama dengan data contoh di app/demo.php
 * (day, start, end, course, code, room, lecturer, type) supaya tampilan dan
 * app.js tidak perlu diubah. day memakai indeks 0 = Senin sampai 6 = Minggu
 * (kolom hari di database: 1 = Senin sampai 7 = Minggu).
 */
function jadwal_daftar(int $kelasId): array
{
    $baris = db_all(
        "SELECT j.id_jadwal, j.hari,
                to_char(j.jam_mulai, 'HH24:MI') AS jam_mulai,
                to_char(j.jam_selesai, 'HH24:MI') AS jam_selesai,
                j.jenis_pertemuan,
                m.nama_matkul, d.nama_dosen, r.nama_ruangan, r.gedung
         FROM jadwal_kuliah AS j
         JOIN mata_kuliah AS m ON m.id_matkul = j.id_matkul
         JOIN dosen AS d ON d.id_dosen = j.id_dosen
         JOIN ruangan AS r ON r.id_ruangan = j.id_ruangan
         WHERE m.id_kelas = :kelas
         ORDER BY j.hari ASC, j.jam_mulai ASC, j.id_jadwal ASC",
        ['kelas' => $kelasId]
    );

    $labelJenis = ['teori' => 'Teori', 'praktikum' => 'Praktikum'];

    return array_map(fn (array $b): array => [
        'id'       => (int) $b['id_jadwal'],
        'day'      => max(0, min(6, (int) $b['hari'] - 1)),
        'start'    => $b['jam_mulai'],
        'end'      => $b['jam_selesai'],
        'course'   => $b['nama_matkul'],
        'code'     => '',
        'room'     => $b['nama_ruangan'] . ' · ' . $b['gedung'],
        'lecturer' => $b['nama_dosen'],
        'type'     => $labelJenis[$b['jenis_pertemuan']] ?? $b['jenis_pertemuan'],
    ], $baris);
}

/**
 * Kelompokkan hasil jadwal_daftar() per hari: indeks 0..6, tiap isinya daftar kuliah.
 * Selalu mengembalikan 7 hari (hari kosong = array kosong).
 */
function jadwal_per_hari(array $jadwal): array
{
    $perHari = array_fill(0, 7, []);
    foreach ($jadwal as $kuliah) {
        $perHari[$kuliah['day']][] = $kuliah;
    }
    return $perHari;
}