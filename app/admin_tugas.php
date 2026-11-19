<?php
/**
 * SQL + helper data untuk Admin Tugas.
 * Semua query database berada di sini.
 */

function admin_tugas_kelas(int $kelasId): ?array
{
    if ($kelasId <= 0) {
        return null;
    }

    return db_one(
        'SELECT id_kelas, nama_kelas
         FROM kelas
         WHERE id_kelas = :kelas
         LIMIT 1',
        ['kelas' => $kelasId]
    ) ?: null;
}

function admin_tugas_matkul(int $kelasId): array
{
    if ($kelasId <= 0) {
        return [];
    }

    return db_all(
        'SELECT id_matkul, nama_matkul
         FROM mata_kuliah
         WHERE id_kelas = :kelas
         ORDER BY nama_matkul ASC',
        ['kelas' => $kelasId]
    );
}

/**
 * Terbitkan tugas ke mata kuliah milik kelas admin.
 */
function admin_tugas_simpan(
    int $kelasId,
    int $matkulId,
    string $judul,
    string $deskripsi,
    string $deadline,
    string $jenis
): void {
    $judul = trim($judul);
    $deskripsi = trim($deskripsi);

    if ($kelasId <= 0 || $matkulId <= 0 || $judul === '' || $deadline === '') {
        throw new InvalidArgumentException('Lengkapi semua kolom wajib.');
    }

    if (mb_strlen($judul) > 200) {
        throw new InvalidArgumentException('Judul maksimal 200 karakter.');
    }

    if (!in_array($jenis, ['Tugas', 'Praktikum', 'Proyek', 'Kuis', 'Ujian'], true)) {
        throw new InvalidArgumentException('Jenis tugas tidak valid.');
    }

    $zona = new DateTimeZone('Asia/Jakarta');
    $tanggal = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $deadline, $zona);

    if (!$tanggal || $tanggal->format('Y-m-d\TH:i') !== $deadline) {
        throw new InvalidArgumentException('Format deadline tidak valid.');
    }

    if ($tanggal <= new DateTimeImmutable('now', $zona)) {
        throw new InvalidArgumentException('Deadline harus di masa depan.');
    }

    $matkul = db_one(
        'SELECT id_matkul
         FROM mata_kuliah
         WHERE id_matkul = :matkul
           AND id_kelas = :kelas
         LIMIT 1',
        [
            'matkul' => $matkulId,
            'kelas' => $kelasId,
        ]
    );

    if (!$matkul) {
        throw new InvalidArgumentException('Mata kuliah tidak ditemukan di kelasmu.');
    }

    $stmt = db()->prepare(
        'INSERT INTO tugas
            (id_matkul, judul_tugas, deskripsi, deadline, jenis_tugas)
         VALUES
            (:matkul, :judul, :deskripsi, :deadline, :jenis)'
    );

    $stmt->execute([
        'matkul' => $matkulId,
        'judul' => $judul,
        'deskripsi' => $deskripsi,
        'deadline' => $tanggal->format('Y-m-d H:i:s'),
        'jenis' => $jenis,
    ]);
}