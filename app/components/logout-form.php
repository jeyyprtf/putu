<?php
require_once __DIR__ . '/../auth.php';

if (auth_user_id() !== null):
?>
    <form action="<?= e(url('logout')) ?>" method="POST">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="icon-button" aria-label="Keluar dari PUTU" title="Keluar">
            <?= icon('logout') ?>
        </button>
    </form>
<?php endif; ?>
