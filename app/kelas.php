<?php
/**
 * Operasi kelas: buat kelas baru dan gabung dengan kode.
 */

/**
 * Kode gabung acak 8 karakter tanpa karakter yang mirip (0/O, 1/I).
 */
function kelas_kode_baru(): string
{
    $alfabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $kode = '';
    for ($i = 0; $i < 8; $i++) {
        $kode .= $alfabet[random_int(0, strlen($alfabet) - 1)];
    }
    return $kode;
}

/**
 * Buat kelas dan jadikan user sebagai admin kelas tersebut (satu transaksi).
 *
 * @return string|null kode gabung kelas baru, atau null kalau user ternyata sudah punya kelas
 * @throws PDOException kalau gagal 5x membuat kode unik atau error DB lain
 */
function kelas_buat(int $idUser, string $nama): ?string
{
    $pdo = db();

    for ($percobaan = 0; $percobaan < 5; $percobaan++) {
        $kode = kelas_kode_baru();

        try {
            $pdo->beginTransaction();

            $kelas = db_one(
                'INSERT INTO kelas (nama_kelas, kode_gabung) VALUES (:nama, :kode) RETURNING id_kelas',
                ['nama' => $nama, 'kode' => $kode]
            );

            // "AND id_kelas IS NULL" mencegah user yang sudah punya kelas membuat kelas lagi.
            $diubah = db_exec(
                "UPDATE users SET id_kelas = :kelas, role = 'admin'
                 WHERE id_user = :user AND id_kelas IS NULL",
                ['kelas' => $kelas['id_kelas'], 'user' => $idUser]
            );

            if ($diubah === 0) {
                $pdo->rollBack();
                return null;
            }

            $pdo->commit();
            return $kode;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // 23505 = unique_violation: kode kebetulan sudah dipakai, coba kode lain.
            if ($e->getCode() !== '23505') {
                throw $e;
            }
        }
    }

    throw new RuntimeException('Gagal membuat kode kelas unik.');
}

/**
 * Gabungkan user (role tetap mahasiswa) ke kelas berdasarkan kode.
 *
 * @return bool false kalau kode tidak ditemukan atau user sudah punya kelas
 */
function kelas_gabung(int $idUser, string $kode): bool
{
    $kelas = db_one('SELECT id_kelas FROM kelas WHERE kode_gabung = :kode', ['kode' => $kode]);
    if ($kelas === null) {
        return false;
    }

    return db_exec(
        'UPDATE users SET id_kelas = :kelas WHERE id_user = :user AND id_kelas IS NULL',
        ['kelas' => $kelas['id_kelas'], 'user' => $idUser]
    ) === 1;
}
