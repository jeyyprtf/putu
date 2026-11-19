<label class="grid gap-2 text-sm font-medium">
    Nama ruangan
    <input class="field" type="text" name="nama_ruangan" maxlength="50" required
           placeholder="Contoh: Lab Komputer 2" value="<?= e((string) $form['nama_ruangan']) ?>">
</label>
<label class="grid gap-2 text-sm font-medium">
    Gedung
    <input class="field" type="text" name="gedung" maxlength="100" required
           placeholder="Contoh: Gedung Teknik" value="<?= e((string) $form['gedung']) ?>">
</label>
<label class="grid gap-2 text-sm font-medium">
    Kapasitas (opsional)
    <input class="field" type="number" name="kapasitas" min="1" max="32767"
           value="<?= e((string) ($form['kapasitas'] ?? '')) ?>">
</label>