<?php
/**
 * Operasi pengumuman kelas.
 *
 * Semua SQL pengumuman ada di file ini, jadi halaman cukup memanggil fungsinya.
 * Aturan keamanan dipegang oleh pemanggil (app/pages/pengumuman.php):
 *   - id_kelas SELALU dari sesi, bukan dari URL/form;
 *   - menulis/menghapus hanya untuk admin, dan selalu dibatasi id_kelas.
 */

/**
 * Semua pengumuman milik satu kelas, terbaru di atas.
 * Sudah termasuk nama penulis supaya halaman tidak perlu JOIN sendiri.
 */
function pengumuman_daftar(int $kelasId): array
{
    return db_all(
        "SELECT p.id_pengumuman, p.judul, p.isi_pengumuman, p.is_penting,
                to_char(p.created_at AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD') AS tanggal,
                u.nama AS penulis
         FROM pengumuman AS p
         JOIN users AS u ON u.id_user = p.dibuat_oleh
         WHERE p.id_kelas = :kelas
         ORDER BY p.created_at DESC, p.id_pengumuman DESC",
        ['kelas' => $kelasId]
    );
}

/**
 * Simpan pengumuman baru untuk satu kelas.
 *
 * Catatan soal $penting: nilainya dikirim sebagai teks 'true'/'false'.
 * PDO mengirim parameter lewat execute() sebagai string, dan PostgreSQL
 * tidak menerima string kosong untuk kolom boolean. Jadi jangan diganti
 * menjadi (bool) PHP mentah-mentah.
 */
function pengumuman_buat(int $kelasId, int $idAdmin, string $judul, string $isi, bool $penting): void
{
    db_exec(
        'INSERT INTO pengumuman (id_kelas, dibuat_oleh, judul, isi_pengumuman, is_penting)
         VALUES (:kelas, :admin, :judul, :isi, :penting)',
        [
            'kelas'   => $kelasId,
            'admin'   => $idAdmin,
            'judul'   => $judul,
            'isi'     => $isi,
            'penting' => $penting ? 'true' : 'false',
        ]
    );
}

/**
 * Hapus satu pengumuman. Filter id_kelas membuat admin kelas A
 * tidak bisa menghapus pengumuman kelas B walau tahu id-nya.
 *
 * @return bool true kalau memang ada satu baris yang terhapus
 */
function pengumuman_hapus(int $idPengumuman, int $kelasId): bool
{
    return db_exec(
        'DELETE FROM pengumuman WHERE id_pengumuman = :id AND id_kelas = :kelas',
        ['id' => $idPengumuman, 'kelas' => $kelasId]
    ) === 1;
}

/**
 * Ringkasan satu baris untuk kartu; isi lengkap tetap bisa dibuka.
 * Dipotong berdasarkan jumlah karakter, bukan byte, supaya aman untuk UTF-8.
 */
function pengumuman_ringkas(string $teks, int $maks = 160): string
{
    $satuBaris = preg_replace('/\s+/u', ' ', trim($teks)) ?? '';
    $jumlah = preg_match_all('/./su', $satuBaris);

    if ($jumlah === false || $jumlah <= $maks) {
        return $satuBaris;
    }

    preg_match('/^.{' . $maks . '}/su', $satuBaris, $cocok);

    return rtrim($cocok[0] ?? '') . '…';
}
