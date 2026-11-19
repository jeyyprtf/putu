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

/**
 * Admin mengubah jabatan atau mengeluarkan anggota lain dari kelasnya.
 * $kelasId berasal dari pengguna yang login dan diperiksa ulang ke database.
 */
function anggota_kelola(
    int $idUser,
    int $kelasId,
    int $idTarget,
    string $aksi,
    string $roleBaru = ''
): void {
    if ($kelasId <= 0 || $idTarget <= 0 || $idTarget === $idUser) {
        throw new DomainException('Pilih anggota lain di kelasmu.');
    }

    if (!in_array($aksi, ['ubah_role', 'keluarkan'], true)) {
        throw new DomainException('Aksi anggota tidak valid.');
    }

    if (
        $aksi === 'ubah_role'
        && !in_array($roleBaru, ['admin', 'mahasiswa'], true)
    ) {
        throw new DomainException('Jabatan yang dipilih tidak valid.');
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        // Kunci kedua akun agar hak admin dan kelas tidak berubah saat diproses.
        $stmt = $pdo->prepare(
            'SELECT id_user, id_kelas, role
               FROM users
              WHERE id_user IN (:pelaku, :target)
              ORDER BY id_user
              FOR UPDATE'
        );
        $stmt->execute([
            'pelaku' => $idUser,
            'target' => $idTarget,
        ]);

        $akun = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), null, 'id_user');
        $pelaku = $akun[$idUser] ?? null;
        $target = $akun[$idTarget] ?? null;

        if (
            !$pelaku
            || $pelaku['role'] !== 'admin'
            || (int) $pelaku['id_kelas'] !== $kelasId
        ) {
            throw new DomainException('Hanya admin kelas yang dapat mengelola anggota.');
        }

        if (!$target || (int) $target['id_kelas'] !== $kelasId) {
            throw new DomainException('Anggota tersebut sudah tidak berada di kelasmu.');
        }

        if ($aksi === 'keluarkan') {
            $stmt = $pdo->prepare(
                "UPDATE users
                    SET id_kelas = NULL, role = 'mahasiswa'
                  WHERE id_user = :target"
            );
            $stmt->execute(['target' => $idTarget]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users SET role = :role WHERE id_user = :target'
            );
            $stmt->execute([
                'role'   => $roleBaru,
                'target' => $idTarget,
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}