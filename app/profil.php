<?php
/**
 * Operasi profil pengguna.
 * ID pengguna harus berasal dari sesi login, bukan dari input form.
 */

/** Ambil nomor telepon pengguna yang sedang login. */
function profil_ambil(int $idUser): ?array
{
    return db_one(
        'SELECT no_hp FROM users WHERE id_user = :id',
        ['id' => $idUser]
    );
}

/** Perbarui nama dan nomor telepon pengguna. */
function profil_ubah(int $idUser, string $nama, ?string $noHp): bool
{
    return db_exec(
        'UPDATE users
         SET nama = :nama, no_hp = :no_hp
         WHERE id_user = :id',
        [
            'id'    => $idUser,
            'nama'  => $nama,
            'no_hp' => $noHp,
        ]
    ) === 1;
}