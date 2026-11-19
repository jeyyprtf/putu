<label class="grid gap-2 text-sm font-medium">
    Nama mata kuliah
    <input class="field" type="text" name="nama_matkul" maxlength="150" required
           placeholder="Masukkan nama mata kuliah" value="<?= e((string) $form['nama_matkul']) ?>">
</label>
<label class="grid gap-2 text-sm font-medium">
    SKS
    <input class="field" type="number" name="sks" min="1" max="32767" required
           value="<?= e((string) $form['sks']) ?>">
</label>
<label class="grid gap-2 text-sm font-medium">
    Semester
    <input class="field" type="number" name="semester" min="1" max="32767" required
           value="<?= e((string) $form['semester']) ?>">
</label>