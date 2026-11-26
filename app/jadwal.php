<?php
/** Ambil jadwal satu kelas, urut berdasarkan hari dan jam mulai. */
function jadwal_daftar(int $kelasId): array
{
    $baris = db_all(
        "SELECT j.id_jadwal, j.hari, j.jenis_pertemuan,
                to_char(j.jam_mulai, 'HH24:MI') AS jam_mulai,
                to_char(j.jam_selesai, 'HH24:MI') AS jam_selesai,
                m.nama_matkul, d.nama_dosen, r.nama_ruangan, r.gedung
         FROM jadwal_kuliah AS j
         JOIN mata_kuliah AS m ON m.id_matkul = j.id_matkul AND m.id_kelas = j.id_kelas
         JOIN dosen AS d ON d.id_dosen = j.id_dosen AND d.id_kelas = j.id_kelas
         JOIN ruangan AS r ON r.id_ruangan = j.id_ruangan AND r.id_kelas = j.id_kelas
         WHERE j.id_kelas = :kelas
         ORDER BY j.hari, j.jam_mulai, j.id_jadwal",
        ['kelas' => $kelasId]
    );

    $labelJenis = ['teori' => 'Teori', 'praktikum' => 'Praktikum'];
    return array_map(static fn(array $item): array => [
        'id'       => (int) $item['id_jadwal'],
        'day'      => (int) $item['hari'] - 1, // Database 1–7, tampilan 0–6.
        'start'    => $item['jam_mulai'],
        'end'      => $item['jam_selesai'],
        'course'   => $item['nama_matkul'],
        'code'     => '',
        'room'     => $item['nama_ruangan'] . ' · ' . $item['gedung'],
        'lecturer' => $item['nama_dosen'],
        'type'     => $labelJenis[$item['jenis_pertemuan']] ?? $item['jenis_pertemuan'],
    ], $baris);
}

/** Kelompokkan jadwal dari indeks 0 (Senin) sampai 6 (Minggu). */
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

function jadwal_opsi(int $kelasId): array
{
    return [
        'matkul' => db_all('SELECT id_matkul, nama_matkul FROM mata_kuliah WHERE id_kelas = :kelas ORDER BY nama_matkul', ['kelas' => $kelasId]),
        'dosen' => db_all('SELECT id_dosen, nama_dosen FROM dosen WHERE id_kelas = :kelas ORDER BY nama_dosen', ['kelas' => $kelasId]),
        'ruangan' => db_all('SELECT id_ruangan, gedung, nama_ruangan FROM ruangan WHERE id_kelas = :kelas ORDER BY gedung, nama_ruangan', ['kelas' => $kelasId]),
    ];
}

/** Ambil nilai asli untuk form edit, termasuk ID pada setiap dropdown. */
function jadwal_ambil(int $kelasId, int $id): ?array
{
    $baris = db_all(
        "SELECT j.id_matkul, j.id_dosen, j.id_ruangan, j.hari, j.jenis_pertemuan,
                to_char(j.jam_mulai, 'HH24:MI') AS jam_mulai,
                to_char(j.jam_selesai, 'HH24:MI') AS jam_selesai
         FROM jadwal_kuliah AS j
         WHERE j.id_kelas = :kelas AND j.id_jadwal = :id",
        ['kelas' => $kelasId, 'id' => $id]
    );
    return $baris[0] ?? null;
}

/** ID 0 untuk tambah. ID positif untuk edit jadwal milik kelas. */
function jadwal_simpan(int $kelasId, int $id, array $data): void
{
    $angka = ['options' => ['min_range' => 1]];
    $matkul = filter_var($data['id_matkul'] ?? null, FILTER_VALIDATE_INT, $angka);
    $dosen = filter_var($data['id_dosen'] ?? null, FILTER_VALIDATE_INT, $angka);
    $ruangan = filter_var($data['id_ruangan'] ?? null, FILTER_VALIDATE_INT, $angka);
    $hari = filter_var($data['hari'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 7]]);
    $jenis = $data['jenis_pertemuan'] ?? '';
    $mulai = $data['jam_mulai'] ?? '';
    $selesai = $data['jam_selesai'] ?? '';
    $formatJam = '/^(?:[01]\d|2[0-3]):[0-5]\d$/D';

    if ($kelasId < 1 || $id < 0 || !$matkul || !$dosen || !$ruangan || !$hari || !in_array($jenis, ['teori', 'praktikum'], true)) {
        throw new InvalidArgumentException('Periksa pilihan mata kuliah, dosen, ruangan, hari, dan jenis kelas.');
    }
    if (!is_string($mulai) || !is_string($selesai) || !preg_match($formatJam, $mulai) || !preg_match($formatJam, $selesai)) {
        throw new InvalidArgumentException('Jam mulai dan selesai harus menggunakan format HH:MM.');
    }
    if ($mulai >= $selesai) {
        throw new InvalidArgumentException('Jam selesai harus setelah jam mulai.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Pakai lock yang sama dengan implementasi sebelumnya agar dua penyimpanan
        // tidak sama-sama lolos pemeriksaan bentrok pada waktu yang bersamaan.
        $pdo->query('SELECT pg_advisory_xact_lock(730021)');

        $cekMatkul = $pdo->prepare('SELECT 1 FROM mata_kuliah WHERE id_matkul = :matkul AND id_kelas = :kelas FOR SHARE');
        $cekMatkul->execute(['matkul' => $matkul, 'kelas' => $kelasId]);
        if (!$cekMatkul->fetchColumn()) {
            throw new InvalidArgumentException('Mata kuliah tidak tersedia untuk kelasmu.');
        }

        $cekPilihan = $pdo->prepare(
            'SELECT 1 FROM dosen AS d JOIN ruangan AS r USING (id_kelas)
             WHERE d.id_kelas = :kelas AND d.id_dosen = :dosen AND r.id_ruangan = :ruangan'
        );
        $cekPilihan->execute(['kelas' => $kelasId, 'dosen' => $dosen, 'ruangan' => $ruangan]);
        if (!$cekPilihan->fetchColumn()) {
            throw new InvalidArgumentException('Dosen atau ruangan tidak tersedia untuk kelasmu.');
        }

        $cekBentrok = $pdo->prepare(
            'SELECT 1 FROM jadwal_kuliah AS j
             WHERE j.hari = :hari AND j.id_jadwal <> :id
               AND j.jam_mulai < CAST(:selesai AS time) AND j.jam_selesai > CAST(:mulai AS time)
               AND j.id_kelas = :kelas
             LIMIT 1'
        );
        $cekBentrok->execute([
            'hari' => $hari, 'id' => $id, 'mulai' => $mulai, 'selesai' => $selesai,
            'kelas' => $kelasId,
        ]);
        if ($cekBentrok->fetchColumn()) {
            throw new InvalidArgumentException('Jam tersebut bentrok dengan jadwal kelasmu.');
        }

        $params = ['kelas' => $kelasId, 'matkul' => $matkul, 'dosen' => $dosen, 'ruangan' => $ruangan, 'hari' => $hari,
                   'mulai' => $mulai, 'selesai' => $selesai, 'jenis' => $jenis];
        if ($id === 0) {
            $sql = 'INSERT INTO jadwal_kuliah (id_kelas, id_matkul, id_dosen, id_ruangan, hari, jam_mulai, jam_selesai, jenis_pertemuan)
                    VALUES (:kelas, :matkul, :dosen, :ruangan, :hari, :mulai, :selesai, :jenis)';
        } else {
            $sql = 'UPDATE jadwal_kuliah AS j
                    SET id_matkul = :matkul, id_dosen = :dosen, id_ruangan = :ruangan,
                        hari = :hari, jam_mulai = :mulai, jam_selesai = :selesai, jenis_pertemuan = :jenis
                    WHERE j.id_kelas = :kelas AND j.id_jadwal = :id';
            $params['id'] = $id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($id > 0 && $stmt->rowCount() !== 1) {
            throw new InvalidArgumentException('Jadwal tidak ditemukan di kelasmu.');
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function jadwal_hapus(int $kelasId, int $id): bool
{
    return db_exec(
        'DELETE FROM jadwal_kuliah WHERE id_kelas = :kelas AND id_jadwal = :id',
        ['kelas' => $kelasId, 'id' => $id]
    ) === 1;
}
