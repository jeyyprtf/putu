<?php
/** Konfigurasi tetap untuk tiga tab halaman admin. */
function akademik_tabs(): array
{
    return [
        'dosen' => [
            'label' => 'Dosen', 'tabel' => 'dosen', 'id' => 'id_dosen',
            'nama' => 'nama_dosen', 'kolom' => ['nama_dosen'],
        ],
        'matkul' => [
            'label' => 'Mata kuliah', 'tabel' => 'mata_kuliah', 'id' => 'id_matkul',
            'nama' => 'nama_matkul', 'kolom' => ['nama_matkul', 'sks', 'semester'],
        ],
        'ruang' => [
            'label' => 'Ruangan', 'tabel' => 'ruangan', 'id' => 'id_ruangan',
            'nama' => 'nama_ruangan', 'kolom' => ['nama_ruangan', 'gedung', 'kapasitas'],
        ],
    ];
}

/** Ambil daftar atau satu baris untuk mode edit. Mata kuliah dibatasi per kelas. */
function akademik_data(string $tab, int $kelasId, ?int $id = null): array
{
    $meta = akademik_tabs()[$tab];
    $kolom = implode(', ', array_merge([$meta['id']], $meta['kolom']));
    $sql = "SELECT $kolom FROM public.{$meta['tabel']} WHERE true";
    $params = [];

    if ($tab === 'matkul') {
        $sql .= ' AND id_kelas = :kelas';
        $params['kelas'] = $kelasId;
    }
    if ($id !== null) {
        $sql .= " AND {$meta['id']} = :id";
        $params['id'] = $id;
    }

    $stmt = db()->prepare($sql . " ORDER BY {$meta['nama']}, {$meta['id']}");
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Validasi kolom yang dipakai form aktif sebelum dikirim ke PostgreSQL. */
function akademik_validasi(string $tab, array $form): array
{
    $batasTeks = ['nama_dosen' => 100, 'nama_matkul' => 150, 'nama_ruangan' => 50, 'gedung' => 100];
    $hasil = [];

    foreach (akademik_tabs()[$tab]['kolom'] as $kolom) {
        $nilai = trim((string) ($form[$kolom] ?? ''));
        $label = $kolom === 'sks' ? 'SKS' : ucfirst(str_replace('_', ' ', $kolom));

        if (isset($batasTeks[$kolom])) {
            $panjang = text_length($nilai);
            if ($panjang === null || $panjang < 1 || $panjang > $batasTeks[$kolom]) {
                throw new DomainException("$label wajib diisi, maksimal {$batasTeks[$kolom]} karakter.");
            }
            $hasil[$kolom] = $nilai;
        } elseif ($kolom === 'kapasitas' && $nilai === '') {
            $hasil[$kolom] = null;
        } else {
            if (!ctype_digit($nilai) || (int) $nilai < 1 || (int) $nilai > 32767) {
                throw new DomainException("$label harus berupa bilangan bulat antara 1 dan 32767.");
            }
            $hasil[$kolom] = (int) $nilai;
        }
    }
    return $hasil;
}

/** Tambah, edit, atau hapus satu data setelah memeriksa hak admin terbaru. */
function akademik_simpan(string $tab, string $aksi, int $idUser, int $kelasId, ?int $id, array $form): void
{
    if (!in_array($aksi, ['tambah', 'edit', 'hapus'], true)) {
        throw new DomainException('Aksi tidak dikenal.');
    }
    if ($aksi !== 'tambah' && ($id === null || $id <= 0)) {
        throw new DomainException('ID data tidak valid.');
    }

    $meta = akademik_tabs()[$tab];
    $tabel = 'public.' . $meta['tabel'];
    $params = $aksi === 'hapus' ? [] : akademik_validasi($tab, $form);
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $cek = $pdo->prepare(
            "SELECT 1 FROM public.users
             WHERE id_user = :user AND id_kelas = :kelas AND role = 'admin'
             FOR SHARE"
        );
        $cek->execute(['user' => $idUser, 'kelas' => $kelasId]);
        if (!$cek->fetchColumn()) {
            throw new DomainException('Akses admin atau kelasmu sudah berubah. Muat ulang halaman.');
        }

        if ($aksi === 'tambah') {
            if ($tab === 'matkul') {
                $params['id_kelas'] = $kelasId;
            }
            $kolom = array_keys($params);
            $namaKolom = implode(', ', $kolom);
            $placeholder = ':' . implode(', :', $kolom);
            $sql = "INSERT INTO $tabel ($namaKolom) VALUES ($placeholder)";
        } else {
            if ($aksi === 'edit') {
                $set = array_map(static fn($kolom) => "$kolom = :$kolom", $meta['kolom']);
                $sql = "UPDATE $tabel SET " . implode(', ', $set);
            } else {
                $sql = "DELETE FROM $tabel";
            }

            $sql .= " WHERE {$meta['id']} = :id";
            $params['id'] = $id;

            if ($tab === 'matkul') {
                $sql .= ' AND id_kelas = :kelas';
                $params['kelas'] = $kelasId;
            }
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->rowCount() !== 1) {
            throw new DomainException('Data sudah dihapus atau tidak tersedia di kelasmu.');
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}