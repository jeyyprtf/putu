<?php
/** Kompatibilitas untuk pemanggil pengelolaan jadwal lama. */
require_once __DIR__ . '/jadwal.php';

function jadwal_admin_daftar(int $kelasId): array
{
    return db_all("SELECT j.*, m.nama_matkul, d.nama_dosen, r.nama_ruangan, r.gedung,
                          to_char(j.jam_mulai, 'HH24:MI') AS mulai, to_char(j.jam_selesai, 'HH24:MI') AS selesai
                   FROM jadwal_kuliah j
                   JOIN mata_kuliah m ON m.id_matkul = j.id_matkul AND m.id_kelas = j.id_kelas
                   JOIN dosen d ON d.id_dosen = j.id_dosen AND d.id_kelas = j.id_kelas
                   JOIN ruangan r ON r.id_ruangan = j.id_ruangan AND r.id_kelas = j.id_kelas
                   WHERE j.id_kelas = :kelas ORDER BY j.hari, j.jam_mulai, j.id_jadwal", ['kelas' => $kelasId]);
}

function jadwal_admin_simpan(int $kelasId, int $id, array $data): void
{
    jadwal_simpan($kelasId, $id, $data);
}

function jadwal_admin_hapus(int $kelasId, int $id): bool
{
    return $id > 0 && jadwal_hapus($kelasId, $id);
}
