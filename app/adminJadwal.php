<?php
/** CRUD jadwal: selalu dibatasi kelas admin yang login. */
function jadwal_opsi(int $kelasId): array {
    return [
        'matkul' => db_all('SELECT id_matkul, nama_matkul FROM mata_kuliah WHERE id_kelas = :kelas ORDER BY nama_matkul', ['kelas'=>$kelasId]),
        'dosen' => db_all('SELECT id_dosen, nama_dosen FROM dosen ORDER BY nama_dosen'),
        'ruangan' => db_all('SELECT id_ruangan, nama_ruangan, gedung FROM ruangan ORDER BY gedung, nama_ruangan'),
    ];
}
function jadwal_admin_daftar(int $kelasId): array {
    return db_all("SELECT j.*, m.nama_matkul, d.nama_dosen, r.nama_ruangan, r.gedung,
                    to_char(j.jam_mulai,'HH24:MI') AS mulai, to_char(j.jam_selesai,'HH24:MI') AS selesai
                    FROM jadwal_kuliah j
                    JOIN mata_kuliah m ON m.id_matkul=j.id_matkul
                    JOIN dosen d ON d.id_dosen=j.id_dosen
                    JOIN ruangan r ON r.id_ruangan=j.id_ruangan
                    WHERE m.id_kelas=:kelas ORDER BY j.hari,j.jam_mulai,j.id_jadwal", ['kelas'=>$kelasId]);
}
function jadwal_admin_simpan(int $kelasId, int $id, array $data): void {
    $matkul=(int)($data['id_matkul']??0); $dosen=(int)($data['id_dosen']??0);
    $ruangan=(int)($data['id_ruangan']??0); $hari=(int)($data['hari']??0);
    $mulai=(string)($data['jam_mulai']??''); $selesai=(string)($data['jam_selesai']??'');
    $jenis=(string)($data['jenis_pertemuan']??'');
    if ($matkul<1 || $dosen<1 || $ruangan<1 || $hari<1 || $hari>7 ||
        !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$mulai) ||
        !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$selesai) ||
        $mulai >= $selesai || !in_array($jenis,['teori','praktikum'],true)) {
        throw new InvalidArgumentException('Data jadwal tidak valid. Periksa semua kolom dan rentang jam.');
    }
    $pdo=db();
    $pdo->beginTransaction();
    try {
        $valid=$pdo->prepare('SELECT 1 FROM mata_kuliah WHERE id_matkul=:matkul AND id_kelas=:kelas');
        $valid->execute(['matkul'=>$matkul,'kelas'=>$kelasId]);
        if (!$valid->fetchColumn()) throw new InvalidArgumentException('Mata kuliah bukan milik kelas ini.');
        foreach (['dosen'=>['id_dosen',$dosen], 'ruangan'=>['id_ruangan',$ruangan]] as $tabel=>$nilai) {
            $q=$pdo->prepare("SELECT 1 FROM $tabel WHERE {$nilai[0]}=:id");
            $q->execute(['id'=>$nilai[1]]);
            if (!$q->fetchColumn()) throw new InvalidArgumentException('Dosen atau ruangan tidak ditemukan.');
        }
        if ($id>0) {
            $owned=$pdo->prepare('SELECT 1 FROM jadwal_kuliah j JOIN mata_kuliah m ON m.id_matkul=j.id_matkul WHERE j.id_jadwal=:id AND m.id_kelas=:kelas FOR UPDATE OF j');
            $owned->execute(['id'=>$id,'kelas'=>$kelasId]);
            if (!$owned->fetchColumn()) throw new InvalidArgumentException('Jadwal tidak ditemukan di kelasmu.');
        }
        // Hindari bentrok dalam kelas, serta bentrok dosen dan ruangan antarkelas.
        // Advisory lock serialisasi penulisan melalui fungsi ini.
        $pdo->query('SELECT pg_advisory_xact_lock(730021)');
        $conflict=$pdo->prepare('SELECT 1 FROM jadwal_kuliah j JOIN mata_kuliah m ON m.id_matkul=j.id_matkul
            WHERE j.hari=:hari AND j.jam_mulai < CAST(:selesai AS time) AND j.jam_selesai > CAST(:mulai AS time)
            AND j.id_jadwal<>:id AND (m.id_kelas=:kelas OR j.id_dosen=:dosen OR j.id_ruangan=:ruangan) LIMIT 1');
        $conflict->execute(['hari'=>$hari,'selesai'=>$selesai,'mulai'=>$mulai,'id'=>$id,'kelas'=>$kelasId,'dosen'=>$dosen,'ruangan'=>$ruangan]);
        if ($conflict->fetchColumn()) throw new InvalidArgumentException('Jadwal bentrok dengan kelas, dosen, atau ruangan lain.');
        $params=['matkul'=>$matkul,'dosen'=>$dosen,'ruangan'=>$ruangan,'hari'=>$hari,'mulai'=>$mulai,'selesai'=>$selesai,'jenis'=>$jenis];
        if ($id>0) {
            $sql='UPDATE jadwal_kuliah SET id_matkul=:matkul,id_dosen=:dosen,id_ruangan=:ruangan,hari=:hari,jam_mulai=:mulai,jam_selesai=:selesai,jenis_pertemuan=:jenis WHERE id_jadwal=:id';
            $params['id']=$id;
        } else {
            $sql='INSERT INTO jadwal_kuliah (id_matkul,id_dosen,id_ruangan,hari,jam_mulai,jam_selesai,jenis_pertemuan) VALUES (:matkul,:dosen,:ruangan,:hari,:mulai,:selesai,:jenis)';
        }
        $pdo->prepare($sql)->execute($params);
        $pdo->commit();
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
function jadwal_admin_hapus(int $kelasId, int $id): bool {
    if ($id<1) return false;
    $q=db()->prepare('DELETE FROM jadwal_kuliah j USING mata_kuliah m WHERE j.id_matkul=m.id_matkul AND m.id_kelas=:kelas AND j.id_jadwal=:id');
    $q->execute(['kelas'=>$kelasId,'id'=>$id]);
    return $q->rowCount()===1;
}
