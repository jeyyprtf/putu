<dialog
    id="anggota-dialog"
    aria-labelledby="anggota-dialog-title"
    class="panel fixed inset-0 m-auto max-h-[85dvh] w-[calc(100%-2rem)] max-w-md overflow-y-auto bg-stone-900 p-5 text-stone-100 backdrop:bg-black/70"
    style="width: min(28rem, calc(100% - 2rem))"
>
    <h2 id="anggota-dialog-title" class="text-lg font-semibold">Kelola anggota</h2>
    <p id="anggota-dialog-nama" class="mt-1 break-words text-sm text-stone-400"></p>

    <form method="post" id="anggota-form" class="mt-5">
        <input type="hidden" name="token_anggota" value="<?= e($tokenAnggota) ?>">
        <input type="hidden" name="id_kelas" value="<?= $kelasId ?>">
        <input type="hidden" name="id_target" id="anggota-target">

        <div id="anggota-edit">
            <label for="anggota-role" class="mb-2 block text-sm font-medium">
                Jabatan
            </label>

            <select id="anggota-role" name="role" class="field w-full">
                <option value="mahasiswa">Mahasiswa</option>
                <option value="admin">Admin kelas</option>
            </select>

            <button
                type="submit"
                name="aksi"
                value="ubah_role"
                class="button mt-4 w-full bg-lime-300 text-stone-950"
            >
                Simpan jabatan
            </button>

            <button
                type="button"
                id="anggota-minta-keluar"
                class="button button-secondary mt-2 w-full text-red-300"
            >
                Keluarkan dari kelas
            </button>
        </div>

        <div id="anggota-konfirmasi" hidden>
            <p class="text-sm text-stone-300">
                Keluarkan <strong id="anggota-konfirmasi-nama" class="break-words"></strong>
                dari kelas ini? Akunnya tetap tersimpan.
            </p>

            <button
                type="submit"
                name="aksi"
                value="keluarkan"
                class="button mt-4 w-full bg-red-300 text-stone-950"
            >
                Ya, keluarkan
            </button>
        </div>

        <button
            type="button"
            id="anggota-batal"
            class="button button-secondary mt-2 w-full"
        >
            Batal
        </button>
    </form>
</dialog>