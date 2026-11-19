<?php
/**
 * @var string $addUrl   URL halaman tambah.
 * @var string $addLabel Label tombol, misalnya "Tambah pengumuman".
 */
?>
<a href="<?= e($addUrl) ?>" class="button page-add" aria-label="<?= e($addLabel) ?>">
    <?= icon('plus', 'page-add-icon') ?>
    <span class="page-add-label"><?= e($addLabel) ?></span>
</a>