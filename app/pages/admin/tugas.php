<?php
/**
 * Modul pengelolaan tugas dan progres mahasiswa.
 */

/**
 * Mengambil daftar tugas berdasarkan kelas dan progres user.
 */
function tugas_daftar(int $idUser, int $kelasId): array
{
    // Mengambil tugas yang berelasi dengan mata kuliah di kelas tersebut,
    // serta status progres milik user yang sedang login (jika ada).
    $sql = "
        SELECT 
            t.id_tugas AS id,
            t.judul_tugas AS title,
            t.deskripsi AS description,
            t.deadline AS deadline,
            t.jenis_tugas AS type,
            mk.nama_matkul AS course,
            COALESCE(p.status, 'todo') AS status
        FROM tugas t
        JOIN mata_kuliah mk ON t.id_matkul = mk.id_matkul
        LEFT JOIN progres_tugas p ON p.id_tugas = t.id_tugas AND p.id_user = :id_user
        WHERE mk.id_kelas = :id_kelas
        ORDER BY t.deadline ASC
    ";

    $rows = db_all($sql, [
        'id_user'  => $idUser,
        'id_kelas' => $kelasId,
    ]);

    return $rows;
}

/**
 * Menghitung ringkasan status tugas (total, aktif, mendesak, santai, selesai).
 */
function tugas_hitung(array $tasks): array
{
    $counts = [
        'all'      => count($tasks),
        'active'   => 0,
        'priority' => 0,
        'calm'     => 0,
        'todo'     => 0,
        'doing'    => 0,
        'done'     => 0,
    ];

    $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta'));

    foreach ($tasks as $task) {
        $status = $task['status'] ?? 'todo';
        if (isset($counts[$status])) {
            $counts[$status]++;
        }

        if ($status !== 'done') {
            $counts['active']++;
            
            // Hitung selisih hari dengan deadline
            if (!empty($task['deadline'])) {
                $deadlineDate = new DateTimeImmutable($task['deadline'], new DateTimeZone('Asia/Jakarta'));
                $diffDays = (int) $today->diff($deadlineDate)->format('%r%a');
                
                // Mendesak jika deadline <= 2 hari atau sudah terlewat
                if ($diffDays <= 2) {
                    $counts['priority']++;
                } else {
                    $counts['calm']++;
                }
            } else {
                $counts['calm']++;
            }
        }
    }

    return $counts;
}

/**
 * Memperbarui atau menyetel status progres tugas mahasiswa.
 */
function progres_set(int $idUser, int $idTugas, string $status): bool
{
    if (!in_array($status, ['todo', 'doing', 'done'], true)) {
        return false;
    }

    try {
        // Cek apakah baris progres sudah ada di database
        $existing = db_one(
            'SELECT id_progres FROM progres_tugas WHERE id_user = :id_user AND id_tugas = :id_tugas',
            ['id_user' => $idUser, 'id_tugas' => $idTugas]
        );

        if ($existing) {
            db_exec(
                'UPDATE progres_tugas SET status = :status WHERE id_user = :id_user AND id_tugas = :id_tugas',
                ['status' => $status, 'id_user' => $idUser, 'id_tugas' => $idTugas]
            );
        } else {
            db_exec(
                'INSERT INTO progres_tugas (id_user, id_tugas, status) VALUES (:id_user, :id_tugas, :status)',
                ['id_user' => $idUser, 'id_tugas' => $idTugas, 'status' => $status]
            );
        }
        return true;
    } catch (Throwable $e) {
        error_log('[progres_set] ' . $e->getMessage());
        return false;
    }
}