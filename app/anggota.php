<?php
/**
 * SQL + helper data untuk halaman anggota kelas.
 * Semua query ke tabel users/kelas/mata_kuliah ada di sini.
 */

/** Daftar anggota satu kelas, user sendiri di paling atas. */
function anggota_daftar(int $kelasId, int $sayaId = 0): array
{
    if ($kelasId <= 0) {
        return [];
    }

    $stmt = db()->prepare(
        "SELECT id_user, nama, email, role, no_hp
           FROM users
          WHERE id_kelas = :kelas
          ORDER BY (id_user = :saya) DESC, nama ASC"
    );
    $stmt->execute(['kelas' => $kelasId, 'saya' => $sayaId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Baris kelas untuk panel "Tentang kelas". */
function anggota_kelas(int $kelasId): ?array
{
    if ($kelasId <= 0) {
        return null;
    }

    $stmt = db()->prepare('SELECT id_kelas, nama_kelas FROM kelas WHERE id_kelas = :kelas LIMIT 1');
    $stmt->execute(['kelas' => $kelasId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/** Semester kelas — schema tidak menyimpan semester di tabel kelas, jadi ambil dari matkul. */
function anggota_semester(int $kelasId): string
{
    if ($kelasId <= 0) {
        return '';
    }

    $stmt = db()->prepare('SELECT MIN(semester) FROM mata_kuliah WHERE id_kelas = :kelas');
    $stmt->execute(['kelas' => $kelasId]);
    $nilai = $stmt->fetchColumn();

    return $nilai === false || $nilai === null ? '' : (string) $nilai;
}
