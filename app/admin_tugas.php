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
    int $idUser,
    int $matkulId,
    string $judul,
    string $deskripsi,
    string $deadline,
    string $jenis
): void {
    $judul = trim($judul);
    $deskripsi = trim($deskripsi);
    $jenis = strtolower(trim($jenis));

    if (
        $kelasId <= 0 ||
        $idUser <= 0 ||
        $matkulId <= 0 ||
        $judul === '' ||
        $deskripsi === '' ||
        $deadline === ''
    ) {
        throw new InvalidArgumentException('Lengkapi semua kolom wajib.');
    }

    $panjangJudul = text_length($judul);
    if ($panjangJudul === null || $panjangJudul > 200) {
        throw new InvalidArgumentException('Judul maksimal 200 karakter.');
    }

    if (!in_array($jenis, ['tugas', 'praktikum', 'proyek'], true)) {
        throw new InvalidArgumentException('Jenis tugas tidak valid.');
    }

    $zona = new DateTimeZone('Asia/Jakarta');

    $tanggal = DateTimeImmutable::createFromFormat(
        '!Y-m-d\TH:i',
        $deadline,
        $zona
    );

    if (!$tanggal || $tanggal->format('Y-m-d\TH:i') !== $deadline) {
        throw new InvalidArgumentException('Format deadline tidak valid.');
    }

    if ($tanggal <= new DateTimeImmutable('now', $zona)) {
        throw new InvalidArgumentException('Deadline harus di masa depan.');
    }

    // Pastikan mata kuliah milik kelas admin.
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
        throw new InvalidArgumentException(
            'Mata kuliah tidak ditemukan di kelasmu.'
        );
    }

    $stmt = db()->prepare(
        'INSERT INTO tugas
            (
                id_matkul,
                dibuat_oleh,
                judul_tugas,
                deskripsi,
                deadline,
                jenis_tugas
            )
         VALUES
            (
                :matkul,
                :dibuat_oleh,
                :judul,
                :deskripsi,
                :deadline,
                :jenis
            )'
    );

    $stmt->execute([
        'matkul' => $matkulId,
        'dibuat_oleh' => $idUser,
        'judul' => $judul,
        'deskripsi' => $deskripsi,
        'deadline' => $tanggal->format('Y-m-d H:i:sP'),
        'jenis' => $jenis,
    ]);
}

/**
 * Ambil satu tugas aktif untuk form edit.
 * Hanya tugas dari kelas admin yang dapat dibaca.
 */
function admin_tugas_detail(
    int $kelasId,
    int $idUser,
    int $idTugas
): ?array {
    if ($kelasId <= 0 || $idUser <= 0 || $idTugas <= 0) {
        return null;
    }

    return db_one(
        "SELECT
            t.id_tugas,
            t.id_matkul,
            t.judul_tugas,
            t.deskripsi,
            t.jenis_tugas,
            to_char(
                t.deadline AT TIME ZONE 'Asia/Jakarta',
                'YYYY-MM-DD\"T\"HH24:MI'
            ) AS deadline_form
         FROM tugas t
         JOIN mata_kuliah m ON m.id_matkul = t.id_matkul
         JOIN users u ON u.id_user = :admin
         WHERE t.id_tugas = :tugas
           AND m.id_kelas = :kelas
           AND u.id_kelas = m.id_kelas
           AND u.role = 'admin'
           AND t.aktif = TRUE
         LIMIT 1",
        [
            'admin' => $idUser,
            'tugas' => $idTugas,
            'kelas' => $kelasId,
        ]
    ) ?: null;
}

/**
 * Update tugas aktif dalam kelas admin.
 */
function admin_tugas_update(
    int $kelasId,
    int $idUser,
    int $idTugas,
    int $matkulId,
    string $judul,
    string $deskripsi,
    string $deadline,
    string $jenis
): bool {
    $judul = trim($judul);
    $deskripsi = trim($deskripsi);
    $jenis = strtolower(trim($jenis));

    if (
        $kelasId <= 0 ||
        $idUser <= 0 ||
        $idTugas <= 0 ||
        $matkulId <= 0 ||
        $judul === '' ||
        $deskripsi === '' ||
        $deadline === ''
    ) {
        throw new InvalidArgumentException('Lengkapi semua kolom wajib.');
    }

    $panjangJudul = text_length($judul);
    if ($panjangJudul === null || $panjangJudul > 200) {
        throw new InvalidArgumentException('Judul maksimal 200 karakter.');
    }

    if (!in_array($jenis, ['tugas', 'praktikum', 'proyek'], true)) {
        throw new InvalidArgumentException('Jenis tugas tidak valid.');
    }

    $zona = new DateTimeZone('Asia/Jakarta');

    $tanggal = DateTimeImmutable::createFromFormat(
        '!Y-m-d\TH:i',
        $deadline,
        $zona
    );

    if (!$tanggal || $tanggal->format('Y-m-d\TH:i') !== $deadline) {
        throw new InvalidArgumentException('Format deadline tidak valid.');
    }

    // UPDATE hanya boleh dilakukan oleh admin kelas terkait.
    // Mata kuliah baru juga wajib berasal dari kelas yang sama.
    $stmt = db()->prepare(
        "UPDATE tugas AS t
         SET
            id_matkul = :matkul,
            judul_tugas = :judul,
            deskripsi = :deskripsi,
            deadline = :deadline,
            jenis_tugas = :jenis
         WHERE t.id_tugas = :tugas
           AND t.aktif = TRUE
           AND EXISTS (
                SELECT 1
                FROM mata_kuliah lama
                JOIN users u ON u.id_kelas = lama.id_kelas
                WHERE lama.id_matkul = t.id_matkul
                  AND lama.id_kelas = :kelas
                  AND u.id_user = :admin
                  AND u.role = 'admin'
           )
           AND EXISTS (
                SELECT 1
                FROM mata_kuliah baru
                WHERE baru.id_matkul = :matkul_baru
                  AND baru.id_kelas = :kelas_baru
           )
         RETURNING t.id_tugas"
    );

    $stmt->execute([
        'matkul' => $matkulId,
        'judul' => $judul,
        'deskripsi' => $deskripsi,
        'deadline' => $tanggal->format('Y-m-d H:i:sP'),
        'jenis' => $jenis,
        'tugas' => $idTugas,
        'kelas' => $kelasId,
        'admin' => $idUser,
        'matkul_baru' => $matkulId,
        'kelas_baru' => $kelasId,
    ]);

    return $stmt->fetchColumn() !== false;
}

/**
 * Soft delete: sembunyikan tugas tanpa menghapus data.
 */
function admin_tugas_nonaktifkan(
    int $kelasId,
    int $idUser,
    int $idTugas
): bool {
    if ($kelasId <= 0 || $idUser <= 0 || $idTugas <= 0) {
        return false;
    }

    $stmt = db()->prepare(
        "UPDATE tugas AS t
         SET aktif = FALSE
         WHERE t.id_tugas = :tugas
           AND t.aktif = TRUE
           AND EXISTS (
                SELECT 1
                FROM mata_kuliah m
                JOIN users u ON u.id_kelas = m.id_kelas
                WHERE m.id_matkul = t.id_matkul
                  AND m.id_kelas = :kelas
                  AND u.id_user = :admin
                  AND u.role = 'admin'
           )
         RETURNING t.id_tugas"
    );

    $stmt->execute([
        'tugas' => $idTugas,
        'kelas' => $kelasId,
        'admin' => $idUser,
    ]);

    return $stmt->fetchColumn() !== false;
}
