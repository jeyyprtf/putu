-- PUTU PDM PostgreSQL: jalankan satu kali pada database development kosong.

-- 9 tabel, 11 FK, 1 view, 1 materialized view; tanpa function/procedure/trigger buatan sendiri.

-- Tidak ada DROP. Ini acuan PDM, bukan migrasi untuk database yang sudah berisi data.

BEGIN;

SET LOCAL TIME ZONE 'UTC';

CREATE TABLE public.kelas (
    id_kelas BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    nama_kelas VARCHAR(100) NOT NULL,
    kode_gabung VARCHAR(16) NOT NULL,
    CONSTRAINT pk_kelas PRIMARY KEY (id_kelas),
    CONSTRAINT uq_kelas_kode_gabung UNIQUE (kode_gabung)
);

CREATE TABLE public.users (
    id_user BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'mahasiswa',
    id_kelas BIGINT NULL,
    no_hp VARCHAR(20) NULL,
    CONSTRAINT pk_users PRIMARY KEY (id_user),
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT ck_users_role CHECK (role IN ('mahasiswa', 'admin'))
);

CREATE TABLE public.dosen (
    id_dosen BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    kode_dosen VARCHAR(20) NOT NULL,
    nama_dosen VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NULL,
    CONSTRAINT pk_dosen PRIMARY KEY (id_dosen),
    CONSTRAINT uq_dosen_kode_dosen UNIQUE (kode_dosen)
);

CREATE TABLE public.ruangan (
    id_ruangan BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    nama_ruangan VARCHAR(50) NOT NULL,
    gedung VARCHAR(100) NOT NULL,
    kapasitas SMALLINT NULL,
    CONSTRAINT pk_ruangan PRIMARY KEY (id_ruangan),
    CONSTRAINT uq_ruangan_gedung_nama_ruangan UNIQUE (gedung, nama_ruangan),
    CONSTRAINT ck_ruangan_kapasitas CHECK (kapasitas IS NULL OR kapasitas > 0)
);

CREATE TABLE public.mata_kuliah (
    id_matkul BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    id_kelas BIGINT NOT NULL,
    nama_matkul VARCHAR(150) NOT NULL,
    sks SMALLINT NOT NULL,
    semester SMALLINT NOT NULL,
    CONSTRAINT pk_mata_kuliah PRIMARY KEY (id_matkul),
    CONSTRAINT ck_mata_kuliah_sks CHECK (sks > 0),
    CONSTRAINT ck_mata_kuliah_semester CHECK (semester > 0)
);

CREATE TABLE public.tugas (
    id_tugas BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    id_matkul BIGINT NOT NULL,
    dibuat_oleh BIGINT NOT NULL,
    judul_tugas VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    deadline TIMESTAMPTZ NOT NULL,
    jenis_tugas VARCHAR(20) NOT NULL DEFAULT 'tugas',
    CONSTRAINT pk_tugas PRIMARY KEY (id_tugas),
    CONSTRAINT ck_tugas_jenis CHECK (jenis_tugas IN ('tugas', 'praktikum', 'proyek'))
);

CREATE TABLE public.jadwal_kuliah (
    id_jadwal BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    id_matkul BIGINT NOT NULL,
    id_dosen BIGINT NOT NULL,
    id_ruangan BIGINT NOT NULL,
    hari SMALLINT NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    jenis_pertemuan VARCHAR(20) NOT NULL,
    CONSTRAINT pk_jadwal_kuliah PRIMARY KEY (id_jadwal),
    CONSTRAINT ck_jadwal_kuliah_hari CHECK (hari BETWEEN 1 AND 7),
    CONSTRAINT ck_jadwal_kuliah_jam CHECK (jam_selesai > jam_mulai),
    CONSTRAINT ck_jadwal_kuliah_jenis CHECK (jenis_pertemuan IN ('teori', 'praktikum'))
);

CREATE TABLE public.pengumuman (
    id_pengumuman BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    id_kelas BIGINT NOT NULL,
    dibuat_oleh BIGINT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    isi_pengumuman TEXT NOT NULL,
    is_penting BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_pengumuman PRIMARY KEY (id_pengumuman)
);

CREATE TABLE public.progres_tugas (
    id_progres BIGINT GENERATED ALWAYS AS IDENTITY NOT NULL,
    id_user BIGINT NOT NULL,
    id_tugas BIGINT NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'todo',
    selesai_at TIMESTAMPTZ NULL,
    CONSTRAINT pk_progres_tugas PRIMARY KEY (id_progres),
    CONSTRAINT uq_progres_tugas_id_user_id_tugas UNIQUE (id_user, id_tugas),
    CONSTRAINT ck_progres_tugas_status CHECK (status IN ('todo', 'doing', 'done')),
    CONSTRAINT ck_progres_tugas_selesai CHECK ((status = 'done' AND selesai_at IS NOT NULL) OR (status <> 'done' AND selesai_at IS NULL))
);

ALTER TABLE public.users
    ADD CONSTRAINT fk_users_id_kelas
    FOREIGN KEY (id_kelas) REFERENCES public.kelas (id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.mata_kuliah
    ADD CONSTRAINT fk_mata_kuliah_id_kelas
    FOREIGN KEY (id_kelas) REFERENCES public.kelas (id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.tugas
    ADD CONSTRAINT fk_tugas_id_matkul
    FOREIGN KEY (id_matkul) REFERENCES public.mata_kuliah (id_matkul)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.tugas
    ADD CONSTRAINT fk_tugas_dibuat_oleh
    FOREIGN KEY (dibuat_oleh) REFERENCES public.users (id_user)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.pengumuman
    ADD CONSTRAINT fk_pengumuman_id_kelas
    FOREIGN KEY (id_kelas) REFERENCES public.kelas (id_kelas)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.pengumuman
    ADD CONSTRAINT fk_pengumuman_dibuat_oleh
    FOREIGN KEY (dibuat_oleh) REFERENCES public.users (id_user)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.jadwal_kuliah
    ADD CONSTRAINT fk_jadwal_kuliah_id_matkul
    FOREIGN KEY (id_matkul) REFERENCES public.mata_kuliah (id_matkul)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.jadwal_kuliah
    ADD CONSTRAINT fk_jadwal_kuliah_id_dosen
    FOREIGN KEY (id_dosen) REFERENCES public.dosen (id_dosen)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.jadwal_kuliah
    ADD CONSTRAINT fk_jadwal_kuliah_id_ruangan
    FOREIGN KEY (id_ruangan) REFERENCES public.ruangan (id_ruangan)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.progres_tugas
    ADD CONSTRAINT fk_progres_tugas_id_user
    FOREIGN KEY (id_user) REFERENCES public.users (id_user)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE public.progres_tugas
    ADD CONSTRAINT fk_progres_tugas_id_tugas
    FOREIGN KEY (id_tugas) REFERENCES public.tugas (id_tugas)
    ON DELETE CASCADE ON UPDATE RESTRICT;

CREATE INDEX idx_users_id_kelas ON public.users (id_kelas);

CREATE INDEX idx_mata_kuliah_id_kelas ON public.mata_kuliah (id_kelas);

CREATE INDEX idx_tugas_id_matkul ON public.tugas (id_matkul);

CREATE INDEX idx_tugas_dibuat_oleh ON public.tugas (dibuat_oleh);

CREATE INDEX idx_pengumuman_id_kelas ON public.pengumuman (id_kelas);

CREATE INDEX idx_pengumuman_dibuat_oleh ON public.pengumuman (dibuat_oleh);

CREATE INDEX idx_jadwal_kuliah_id_matkul ON public.jadwal_kuliah (id_matkul);

CREATE INDEX idx_jadwal_kuliah_id_dosen ON public.jadwal_kuliah (id_dosen);

CREATE INDEX idx_jadwal_kuliah_id_ruangan ON public.jadwal_kuliah (id_ruangan);

CREATE INDEX idx_progres_tugas_id_tugas ON public.progres_tugas (id_tugas);

CREATE VIEW public.v_tugas_mahasiswa AS
SELECT
    u.id_user, u.id_kelas, m.id_matkul, m.nama_matkul,
    t.id_tugas, t.judul_tugas, t.deskripsi, t.deadline, t.jenis_tugas,
    COALESCE(p.status, 'todo')::VARCHAR(10) AS status,
    p.selesai_at
FROM public.users AS u
JOIN public.mata_kuliah AS m ON m.id_kelas = u.id_kelas
JOIN public.tugas AS t ON t.id_matkul = m.id_matkul
LEFT JOIN public.progres_tugas AS p
    ON p.id_user = u.id_user AND p.id_tugas = t.id_tugas
WHERE u.role = 'mahasiswa';

CREATE MATERIALIZED VIEW public.mv_rekap_kelas AS
WITH jumlah_tugas AS (
    SELECT m.id_kelas, COUNT(t.id_tugas) AS jumlah_tugas
    FROM public.mata_kuliah AS m
    LEFT JOIN public.tugas AS t ON t.id_matkul = m.id_matkul
    GROUP BY m.id_kelas
), jumlah_mahasiswa AS (
    SELECT id_kelas, COUNT(*) AS jumlah_mahasiswa
    FROM public.users
    WHERE role = 'mahasiswa' AND id_kelas IS NOT NULL
    GROUP BY id_kelas
), progres AS (
    SELECT id_kelas, COUNT(*) AS total_penugasan,
           COUNT(*) FILTER (WHERE status = 'done') AS jumlah_selesai
    FROM public.v_tugas_mahasiswa
    GROUP BY id_kelas
)
SELECT
    k.id_kelas, k.nama_kelas,
    COALESCE(t.jumlah_tugas, 0)::BIGINT AS jumlah_tugas,
    COALESCE(m.jumlah_mahasiswa, 0)::BIGINT AS jumlah_mahasiswa,
    COALESCE(p.total_penugasan, 0)::BIGINT AS total_penugasan,
    COALESCE(p.jumlah_selesai, 0)::BIGINT AS jumlah_selesai,
    COALESCE(ROUND(100.0 * p.jumlah_selesai /
                   NULLIF(p.total_penugasan, 0), 2), 0)::NUMERIC(5,2) AS persen_selesai,
    CURRENT_TIMESTAMP AS diperbarui_at
FROM public.kelas AS k
LEFT JOIN jumlah_tugas AS t ON t.id_kelas = k.id_kelas
LEFT JOIN jumlah_mahasiswa AS m ON m.id_kelas = k.id_kelas
LEFT JOIN progres AS p ON p.id_kelas = k.id_kelas
WITH DATA;

CREATE UNIQUE INDEX uq_mv_rekap_kelas_id_kelas
    ON public.mv_rekap_kelas (id_kelas);

COMMIT;

-- Sesudah perubahan data di-COMMIT, jalankan refresh dari aksi admin bila rekap perlu diperbarui:

-- REFRESH MATERIALIZED VIEW CONCURRENTLY public.mv_rekap_kelas;
