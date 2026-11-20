<div class="mt-5 border-t border-stone-800 pt-5 space-y-3">
    <h3 class="text-sm font-semibold">WhatsApp</h3>
    <?php if (!empty($telegram['wa_chat'])): ?>
        <p class="text-sm text-stone-400"><?= ($gateway['sender'] ?? '') === $telegram['wa_sender'] ? 'Terhubung' : 'Pengirim sedang tidak tersedia' ?></p>
        <form action="<?= e(url('pengaturan')) ?>" method="POST" class="flex flex-wrap gap-2">
            <?= csrf_field() ?>
            <?php if (($gateway['sender'] ?? '') === $telegram['wa_sender']): ?>
                <button name="aksi" value="whatsapp_test" class="button button-secondary">Tes WhatsApp</button>
            <?php endif; ?>
            <button name="aksi" value="whatsapp_disconnect" class="button button-secondary">Putuskan WhatsApp</button>
        </form>
    <?php elseif (!empty($gateway['sender'])): ?>
        <form action="<?= e(url('pengaturan')) ?>" method="POST" target="_blank" rel="noopener noreferrer">
            <?= csrf_field() ?>
            <button name="aksi" value="whatsapp_connect" class="button button-secondary">Hubungkan WhatsApp</button>
        </form>
        <p class="text-xs text-stone-400">Kirim pesan yang sudah terisi di WhatsApp, lalu muat ulang Pengaturan.</p>
    <?php else: ?>
        <p class="text-sm text-stone-400">Nomor pengirim PUTU belum terhubung.</p>
    <?php endif; ?>
</div>
<div class="mt-5 border-t border-stone-800 pt-5 space-y-3">
    <h3 class="text-sm font-semibold">Browser</h3>
    <?php if (!empty($telegram['web_subscription'])): ?>
        <p class="text-sm text-stone-400"><?= ($telegram['web_session'] ?? null) === ($_SESSION['notification_web'] ?? null) ? 'Aktif di browser ini' : 'Aktif di browser lain' ?></p>
        <form action="<?= e(url('pengaturan')) ?>" method="POST" class="flex flex-wrap gap-2">
            <?= csrf_field() ?>
            <button name="aksi" value="web_test" class="button button-secondary">Tes browser</button>
            <button name="aksi" value="web_disconnect" class="button button-secondary">Nonaktifkan browser</button>
        </form>
    <?php endif; ?>
    <?php if (!empty($gateway['publicKey']) && (empty($telegram['web_subscription']) || ($telegram['web_session'] ?? null) !== ($_SESSION['notification_web'] ?? null))): ?>
        <form id="web-push-form" action="<?= e(url('pengaturan')) ?>" method="POST" data-key="<?= e($gateway['publicKey']) ?>" data-worker="<?= e(asset('notification-sw.js')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="web_connect">
            <input type="hidden" name="subscription">
            <button type="button" class="button button-secondary" disabled>Aktifkan di browser ini</button>
            <p class="mt-2 text-xs text-stone-400" role="status">Satu browser aktif per akun. Mengaktifkan browser ini menggantikan browser sebelumnya.</p>
        </form>
        <script defer src="<?= e(asset('public/assets/js/web-push.js')) ?>"></script>
    <?php elseif (empty($gateway['publicKey'])): ?>
        <p class="text-sm text-stone-400">Layanan Web Push belum siap.</p>
    <?php endif; ?>
</div>
