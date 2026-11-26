BEGIN;
SET LOCAL lock_timeout = '5s';

-- Jalankan saat PUTU dalam pemeliharaan dan deploy kode PHP baru sebelum dibuka kembali.
LOCK TABLE public.mata_kuliah, public.dosen, public.ruangan, public.jadwal_kuliah
    IN ACCESS EXCLUSIVE MODE;

ALTER TABLE public.dosen ADD COLUMN id_kelas bigint;
ALTER TABLE public.ruangan
    ADD COLUMN id_kelas bigint,
    DROP CONSTRAINT uq_ruangan_gedung_nama_ruangan;
ALTER TABLE public.jadwal_kuliah ADD COLUMN id_kelas bigint;

UPDATE public.jadwal_kuliah AS j
SET id_kelas = m.id_kelas
FROM public.mata_kuliah AS m
WHERE m.id_matkul = j.id_matkul;

-- Pertahankan ID lama pada kelas pertama dan salin katalog untuk kelas lainnya.
-- Data tanpa jadwal tetap tersimpan dengan id_kelas NULL sampai pemiliknya ditentukan.
DO $$
DECLARE
    r record;
    id_baru bigint;
BEGIN
    FOR r IN
        SELECT DISTINCT j.id_dosen, j.id_kelas
        FROM public.jadwal_kuliah AS j
        ORDER BY j.id_dosen, j.id_kelas
    LOOP
        UPDATE public.dosen
        SET id_kelas = r.id_kelas
        WHERE id_dosen = r.id_dosen AND id_kelas IS NULL;

        IF NOT FOUND THEN
            INSERT INTO public.dosen (id_kelas, nama_dosen)
            SELECT r.id_kelas, d.nama_dosen
            FROM public.dosen AS d
            WHERE d.id_dosen = r.id_dosen
            RETURNING id_dosen INTO id_baru;

            UPDATE public.jadwal_kuliah
            SET id_dosen = id_baru
            WHERE id_dosen = r.id_dosen AND id_kelas = r.id_kelas;
        END IF;
    END LOOP;

    FOR r IN
        SELECT DISTINCT j.id_ruangan, j.id_kelas
        FROM public.jadwal_kuliah AS j
        ORDER BY j.id_ruangan, j.id_kelas
    LOOP
        UPDATE public.ruangan
        SET id_kelas = r.id_kelas
        WHERE id_ruangan = r.id_ruangan AND id_kelas IS NULL;

        IF NOT FOUND THEN
            INSERT INTO public.ruangan (id_kelas, nama_ruangan, gedung, kapasitas)
            SELECT r.id_kelas, a.nama_ruangan, a.gedung, a.kapasitas
            FROM public.ruangan AS a
            WHERE a.id_ruangan = r.id_ruangan
            RETURNING id_ruangan INTO id_baru;

            UPDATE public.jadwal_kuliah
            SET id_ruangan = id_baru
            WHERE id_ruangan = r.id_ruangan AND id_kelas = r.id_kelas;
        END IF;
    END LOOP;
END;
$$;

ALTER TABLE public.mata_kuliah
    ADD CONSTRAINT uq_mata_kuliah_kelas_matkul UNIQUE (id_kelas, id_matkul);

ALTER TABLE public.dosen
    ADD CONSTRAINT fk_dosen_kelas FOREIGN KEY (id_kelas) REFERENCES public.kelas (id_kelas),
    ADD CONSTRAINT uq_dosen_kelas_dosen UNIQUE (id_kelas, id_dosen),
    ADD CONSTRAINT ck_dosen_wajib_kelas CHECK (id_kelas IS NOT NULL) NOT VALID;

ALTER TABLE public.ruangan
    ADD CONSTRAINT fk_ruangan_kelas FOREIGN KEY (id_kelas) REFERENCES public.kelas (id_kelas),
    ADD CONSTRAINT uq_ruangan_kelas_ruangan UNIQUE (id_kelas, id_ruangan),
    ADD CONSTRAINT uq_ruangan_kelas_gedung_nama UNIQUE (id_kelas, gedung, nama_ruangan),
    ADD CONSTRAINT ck_ruangan_wajib_kelas CHECK (id_kelas IS NOT NULL) NOT VALID;

ALTER TABLE public.jadwal_kuliah
    ALTER COLUMN id_kelas SET NOT NULL,
    DROP CONSTRAINT fk_jadwal_kuliah_id_matkul,
    DROP CONSTRAINT fk_jadwal_kuliah_id_dosen,
    DROP CONSTRAINT fk_jadwal_kuliah_id_ruangan,
    ADD CONSTRAINT fk_jadwal_kuliah_kelas_matkul
        FOREIGN KEY (id_kelas, id_matkul) REFERENCES public.mata_kuliah (id_kelas, id_matkul)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    ADD CONSTRAINT fk_jadwal_kuliah_kelas_dosen
        FOREIGN KEY (id_kelas, id_dosen) REFERENCES public.dosen (id_kelas, id_dosen)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    ADD CONSTRAINT fk_jadwal_kuliah_kelas_ruangan
        FOREIGN KEY (id_kelas, id_ruangan) REFERENCES public.ruangan (id_kelas, id_ruangan)
        ON UPDATE RESTRICT ON DELETE RESTRICT;

CREATE INDEX idx_jadwal_kelas_hari_mulai ON public.jadwal_kuliah (id_kelas, hari, jam_mulai);

-- Katalog dan rekap diakses melalui backend PHP yang memeriksa keanggotaan kelas.
ALTER VIEW public.v_tugas_mahasiswa SET (security_invoker = true);
REVOKE ALL ON public.mata_kuliah, public.dosen, public.ruangan, public.jadwal_kuliah,
    public.v_tugas_mahasiswa, public.mv_rekap_kelas FROM PUBLIC, anon, authenticated;

NOTIFY pgrst, 'reload schema';
COMMIT;
