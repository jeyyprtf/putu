<?php
/**
 * Komponen keluar kelas.
 *
 * Pemakaian:
 *
 *   require_once __DIR__ . '/../components/keluarKelas.php';
 *
 *   $kelasError = keluar_kelas_proses($user);
 *
 *   // Di bagian HTML:
 *   keluar_kelas_dialog($user, $profile['kelas']);
 *
 * Pastikan keluar_kelas_proses() dipanggil sebelum ada output HTML.
 */

/**
 * Proses permintaan pengguna untuk keluar dari kelas.
 *
 * Mengembalikan pesan kesalahan jika proses gagal.
 * Jika berhasil, pengguna diarahkan ke halaman gabung kelas.
 */
function keluar_kelas_proses(array $user): string
{
    if (!is_post() || post_str('aksi') !== 'keluar_kelas') {
        return '';
    }

    if (!csrf_verify()) {
        return 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    }

    $pdo = null;

    try {
        $kelasForm = filter_input(INPUT_POST, 'id_kelas', FILTER_VALIDATE_INT);
        $kelasUser = (int) ($user['id_kelas'] ?? 0);
        $idUser = (int) ($user['id_user'] ?? 0);

        if (!$kelasForm || $kelasForm !== $kelasUser || $idUser < 1) {
            throw new DomainException('Kelas sudah berubah. Muat ulang halaman.');
        }

        $pdo = db();
        $pdo->beginTransaction();

        // Kunci anggota kelas secara berurutan selama proses keluar.
        $stmt = $pdo->prepare(
            'SELECT id_user, role
             FROM users
             WHERE id_kelas = :kelas
             ORDER BY id_user
             FOR UPDATE'
        );

        $stmt->execute(['kelas' => $kelasForm]);
        $anggotaKelas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $akun = null;
        $jumlahAdmin = 0;

        foreach ($anggotaKelas as $anggota) {
            if ((int) $anggota['id_user'] === $idUser) {
                $akun = $anggota;
            }

            if ($anggota['role'] === 'admin') {
                $jumlahAdmin++;
            }
        }

        if ($akun === null) {
            throw new DomainException('Kamu sudah tidak tergabung dalam kelas ini.');
        }

        if ($akun['role'] === 'admin' && $jumlahAdmin <= 1) {
            throw new DomainException(
                'Kamu adalah satu-satunya admin kelas. Jadikan anggota lain sebagai admin sebelum keluar.'
            );
        }

        $stmt = $pdo->prepare(
            "UPDATE users
             SET id_kelas = NULL, role = 'mahasiswa'
             WHERE id_user = :id AND id_kelas = :kelas"
        );

        $stmt->execute([
            'id' => $idUser,
            'kelas' => $kelasForm,
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new DomainException(
                'Keanggotaan kelas sudah berubah. Muat ulang halaman.'
            );
        }

        $pdo->commit();

        // Akun tetap login; pengguna dapat bergabung atau membuat kelas baru.
        redirect(url('gabung'));
    } catch (DomainException $e) {
        if ($pdo !== null && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return $e->getMessage();
    } catch (Throwable $e) {
        if ($pdo !== null && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('[keluar-kelas] ' . $e->getMessage());

        return 'Gagal keluar dari kelas. Silakan coba lagi.';
    }
}

/**
 * Tampilkan dialog konfirmasi dan JavaScript untuk membuka/menutup dialog.
 *
 * Tombol pembuka harus memiliki ID: buka-keluar-kelas.
 */
function keluar_kelas_dialog(array $user, string $namaKelas): void
{
    $kelasId = (int) ($user['id_kelas'] ?? 0);

    if ($kelasId < 1) {
        return;
    }
    ?>

    <dialog
        id="dialog-keluar-kelas"
        aria-labelledby="judul-keluar-kelas"
        aria-describedby="deskripsi-keluar-kelas"
        class="panel fixed inset-0 m-auto max-h-[85dvh] w-[calc(100%-2rem)] max-w-md overflow-y-auto rounded-3xl! bg-stone-900 p-6 text-stone-100 backdrop:bg-black/70"
        style="width: min(28rem, calc(100% - 2rem))"
    >
        <h2
            id="judul-keluar-kelas"
            class="text-2xl font-semibold tracking-tight"
        >
            Keluar dari kelas?
        </h2>

        <p
            id="deskripsi-keluar-kelas"
            class="mt-3 text-sm leading-6 text-stone-400"
        >
            Kamu tidak lagi dapat mengakses tugas, jadwal, dan pengumuman dari kelas ini.
        </p>

        <div class="mt-5 rounded-2xl border border-stone-800 bg-stone-950 p-4">
            <p class="text-lg font-semibold">
                <?= e($namaKelas) ?>
            </p>
        </div>

        <div class="mt-5 flex items-center gap-3 rounded-2xl bg-lime-300/10 px-4 py-4 text-sm leading-6 text-lime-200">
            <span class="shrink-0">
                <?= icon('key', 'size-5') ?>
            </span>
            <div class="flex gap-2">
                <p class="font-medium">Akun PUTU tetap aktif. </p>
                <p>Ini bukan keluar akun.</p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button
                type="button"
                id="batal-keluar-kelas"
                class="button button-secondary w-full"
            >
                Tetap di kelas
            </button>

            <form action="<?= e(url('pengaturan')) ?>" method="POST">
                <?= csrf_field() ?>

                <input type="hidden" name="aksi" value="keluar_kelas">
                <input type="hidden" name="id_kelas" value="<?= $kelasId ?>">

                <button
                    type="submit"
                    class="button w-full border border-red-400 bg-transparent! text-red-300! hover:bg-red-400/10"
                >
                    Keluar kelas
                </button>
            </form>
        </div>
    </dialog>

    <script>
        (() => {
            const dialog = document.getElementById('dialog-keluar-kelas');
            const buka = document.getElementById('buka-keluar-kelas');
            const batal = document.getElementById('batal-keluar-kelas');

            if (!dialog || !buka || !batal) return;

            buka.addEventListener('click', () => {
                dialog.showModal();
            });

            batal.addEventListener('click', () => {
                dialog.close();
            });
        })();
    </script>

    <?php
}