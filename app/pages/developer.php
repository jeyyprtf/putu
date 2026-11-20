<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../notification-channels.php';

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('Cross-Origin-Resource-Policy: same-origin');
header('X-Content-Type-Options: nosniff');
$nonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'none'; script-src 'nonce-$nonce'; style-src 'unsafe-inline'; img-src data:; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
$user = current_user();
if (!notification_developer_access($user)) {
    if ($user === null) {
        redirect(url('login'));
    }
    http_response_code(403);
    exit('Panel Developer hanya tersedia untuk admin dari localhost.');
}

if (is_post()) {
    if (!csrf_verify() || post_str('aksi') !== 'wa_reconnect') {
        http_response_code(403);
        exit('Permintaan tidak valid. Buka ulang panel Developer.');
    }
    $result = notification_gateway(['action' => 'wa_reconnect']);
    redirect(url('developer', ['reconnect' => $result['code']]));
}

$feedback = match ($_GET['reconnect'] ?? '') {
    '202' => 'WhatsApp sedang disambungkan ulang.',
    '409' => 'WhatsApp sudah tersambung atau masih memproses koneksi.',
    '503' => 'Gagal menyambungkan ulang. Periksa layanan gateway lalu coba lagi.',
    default => '',
};

$wa = notification_gateway(['action' => 'wa_debug']);
$status = [
    'label' => match ($wa['state'] ?? '') {
        'ready' => 'Terhubung',
        'starting' => 'Sedang menyambungkan…',
        'pairing' => 'Menunggu scan QR',
        'offline' => 'Koneksi terputus',
        'disabled' => 'Mode WhatsApp dimatikan di gateway',
        default => 'Gateway tidak dapat dihubungi',
    },
    'reconnect' => ($wa['reconnect'] ?? false) === true,
    'image' => $wa['image'] ?? null,
];
if (($_GET['status'] ?? '') === '1') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($status, JSON_THROW_ON_ERROR);
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Developer WhatsApp · PUTU</title>
    <style>
        :root { color-scheme: dark; font: 16px/1.5 system-ui, sans-serif; color: #fafaf9; background: #0c0a09; }
        main { max-width: 40rem; margin: 3rem auto; padding: 0 1rem; }
        section { border: 1px solid #44403c; border-radius: 1rem; padding: 1.25rem; background: #1c1917; }
        a { color: #bef264; }
        h1 { line-height: 1.2; }
        #wa-state { font-weight: 600; }
        button { padding: .75rem 1rem; border: 0; border-radius: .6rem; background: #bef264; color: #1c1917; font: inherit; font-weight: 600; cursor: pointer; }
        button:disabled { opacity: .55; cursor: default; }
        img { display: block; max-width: 100%; height: auto; background: white; }
        [hidden] { display: none; }
    </style>
</head>
<body>
<main>
    <a href="<?= e(url('pengaturan')) ?>">← Kembali ke Pengaturan</a>
    <h1>Developer WhatsApp</h1>
    <section aria-label="Koneksi WhatsApp pengirim">
        <p id="wa-state" role="status"><?= e($status['label']) ?></p>
        <?php if ($feedback !== ''): ?><p><?= e($feedback) ?></p><?php endif; ?>
        <form method="post" action="<?= e(url('developer')) ?>">
            <?= csrf_field() ?>
            <button id="wa-reconnect" name="aksi" value="wa_reconnect" <?= $status['reconnect'] ? '' : 'disabled' ?>>Sambungkan ulang</button>
        </form>
        <div id="wa-pair" <?= $status['image'] ? '' : 'hidden' ?>>
            <p>Di HP nomor pengirim, buka <strong>WhatsApp → Perangkat tertaut → Tautkan perangkat</strong>, lalu scan QR ini.</p>
            <img id="wa-qr" width="320" height="320" alt="QR untuk menautkan WhatsApp pengirim PUTU" <?php if ($status['image']): ?>src="<?= e($status['image']) ?>"<?php endif; ?>>
        </div>
        <p><a href="<?= e(url('developer')) ?>">Muat ulang status</a></p>
    </section>
</main>
<script nonce="<?= e($nonce) ?>">
    const state = document.getElementById('wa-state');
    const reconnect = document.getElementById('wa-reconnect');
    const pair = document.getElementById('wa-pair');
    const qr = document.getElementById('wa-qr');
    const statusUrl = new URL(location.href);
    statusUrl.search = 'status=1';
    async function refresh() {
        try {
            const response = await fetch(statusUrl, { cache: 'no-store' });
            if (!response.ok || response.redirected) {
                state.textContent = 'Akses berakhir. Buka ulang panel Developer.';
                reconnect.disabled = true;
                qr.removeAttribute('src');
                pair.hidden = true;
                return;
            }
            const status = await response.json();
            if (state.textContent !== status.label) state.textContent = status.label;
            reconnect.disabled = !status.reconnect;
            if (status.image && qr.getAttribute('src') !== status.image) qr.src = status.image;
            if (!status.image) qr.removeAttribute('src');
            pair.hidden = !status.image;
        } catch {
            state.textContent = 'Status belum bisa dimuat. Akan dicoba lagi.';
            reconnect.disabled = true;
            qr.removeAttribute('src');
            pair.hidden = true;
        }
        setTimeout(refresh, 4000);
    }
    setTimeout(refresh, 4000);
</script>
</body>
</html>
