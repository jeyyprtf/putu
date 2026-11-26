<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../telegram.php';
require_once __DIR__ . '/../telegram-api.php';
require_once __DIR__ . '/../notification-channels.php';
require_once __DIR__ . '/../notification-developer.php';
require_once __DIR__ . '/../notifications.php';

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('Cross-Origin-Resource-Policy: same-origin');
header('X-Content-Type-Options: nosniff');
$nonce = base64_encode(random_bytes(18));
header("Content-Security-Policy: default-src 'none'; script-src 'nonce-$nonce'; style-src 'unsafe-inline'; img-src data:; connect-src 'self'; worker-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

$codeHash = env('PUTU_DEVELOPER_CODE_HASH', '');
$https = notification_developer_https();
if (!preg_match('/^[a-f0-9]{64}$/D', $codeHash) || (!$https && !notification_developer_local())) {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}

session_cache_limiter('');
session_boot('putu_developer', $https);
$proof = hash_hmac('sha256', 'putu-developer', $codeHash);
$access = ($_SESSION['developer_until'] ?? 0) > time()
    && is_string($_SESSION['developer_proof'] ?? null)
    && hash_equals($proof, $_SESSION['developer_proof']);
$url = url('developer');
$action = post_str('aksi');
$feedback = $_SESSION['developer_feedback'] ?? '';
unset($_SESSION['developer_feedback']);

if (is_post()) {
    if (!csrf_verify()) {
        http_response_code(403);
        $feedback = 'Sesi formulir kedaluwarsa. Muat ulang halaman.';
    } elseif (!$access) {
        $code = post_str('kode');
        if ($action === 'login' && preg_match('/^[a-f0-9]{32}$/D', $code)
            && hash_equals($codeHash, hash('sha256', $code)) && session_regenerate_id(true)) {
            $_SESSION = ['developer_proof' => $proof, 'developer_until' => time() + 1800];
            redirect($url);
        }
        http_response_code(403);
        $feedback = 'Kode akses salah atau sesi sudah berakhir.';
    } elseif ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        $cookie = session_get_cookie_params();
        setcookie('putu_developer', '', ['expires' => 1, 'path' => $cookie['path'],
            'secure' => $cookie['secure'], 'httponly' => true, 'samesite' => 'Lax']);
        redirect($url);
    } else {
        try {
            $feedback = match ($action) {
                'telegram_save' => 'Token disimpan untuk @' . notification_save_telegram(post_str('bot_token')) . '.',
                'telegram_check' => 'Bot @' . notification_check_telegram(telegram_config('TELEGRAM_BOT_TOKEN'))
                    . ' valid dan siap memakai polling.',
                'wa_reconnect' => match (notification_gateway(['action' => 'wa_reconnect'])['code']) {
                    202 => 'WhatsApp sedang menyambungkan.',
                    409 => 'WhatsApp sedang diproses atau sudah terhubung.',
                    default => 'Gateway gagal menyambungkan WhatsApp. Periksa proses gateway dan Chrome.',
                },
                'web_test' => '',
                default => 'Aksi tidak tersedia.',
            };
            if ($action === 'web_test') {
                if (time() - ($_SESSION['developer_push_at'] ?? 0) < 15) {
                    throw new RuntimeException('Tunggu 15 detik sebelum mengulang tes browser.');
                }
                $subscription = post_str('subscription');
                if (strlen($subscription) > 4096 || !is_array(json_decode($subscription, true))) {
                    throw new InvalidArgumentException('Langganan browser tidak valid.');
                }
                $_SESSION['developer_push_at'] = time();
                notification_send_gateway('web', $subscription, notification_message(['kind' => 'test', 'channel' => 'web']));
                $feedback = 'Layanan push menerima tes. Cek notifikasi di browser ini.';
            }
        } catch (TelegramApiException $error) {
            $feedback = match ($error->getCode()) {
                401, 404 => 'Token ditolak Telegram. Periksa token dari BotFather.',
                429 => 'Telegram sedang membatasi permintaan. Coba lagi nanti.',
                default => 'Telegram API tidak dapat dihubungi atau merespons dengan kesalahan.',
            };
        } catch (NotificationGatewayException $error) {
            $feedback = match ($error->getCode()) {
                404, 410 => 'Langganan browser kedaluwarsa. Hubungkan ulang dari Pengaturan PUTU.',
                400 => 'Langganan browser tidak didukung.',
                default => 'Gateway gagal mengirim tes browser.',
            };
        } catch (RuntimeException | InvalidArgumentException $error) {
            $feedback = $error->getMessage();
        }
        $_SESSION['developer_feedback'] = $feedback;
        redirect($url);
    }
}

if (($_GET['status'] ?? '') === '1') {
    if (!$access || is_post()) {
        http_response_code(403);
        exit;
    }
    session_write_close();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(notification_developer_status(), JSON_THROW_ON_ERROR);
    exit;
}

if ($access) {
    $wa = notification_developer_status();
    $gateway = notification_gateway(['action' => 'status']);
    $telegram = telegram_configured() ? telegram_config('TELEGRAM_BOT_USERNAME') : null;
    $workerSeen = @filemtime(notification_state_dir() . '/worker.heartbeat');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Developer · PUTU</title>
    <style>
        :root { color-scheme: dark; font: 16px/1.5 system-ui, sans-serif; color: #fafaf9; background: #0c0a09; }
        * { box-sizing: border-box; }
        main { max-width: 46rem; margin: 3rem auto; padding: 0 1rem; }
        header, .actions { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
        section, .notice { margin: 1rem 0; border: 1px solid #44403c; border-radius: 1rem; padding: 1.25rem; background: #1c1917; }
        h1, h2 { margin: 0; line-height: 1.25; }
        p { margin: .65rem 0; }
        label { display: block; margin: .75rem 0 .3rem; }
        input, button { padding: .65rem .8rem; border: 1px solid #78716c; border-radius: .6rem; font: inherit; }
        input { width: 100%; margin-bottom: .75rem; }
        button { background: #bef264; color: #1c1917; border-color: #bef264; font-weight: 600; cursor: pointer; }
        button.secondary { background: transparent; border-color: #78716c; color: #fafaf9; }
        button:disabled { opacity: .55; cursor: default; }
        img { display: block; max-width: 100%; height: auto; background: white; border-radius: .4rem; }
        [hidden] { display: none; }
    </style>
</head>
<body>
<main>
    <header>
        <h1><?= $access ? 'Notifikasi PUTU' : 'Masuk Developer' ?></h1>
        <?php if ($access): ?>
            <form method="post" action="<?= e($url) ?>">
                <?= csrf_field() ?>
                <button class="secondary" name="aksi" value="logout">Keluar</button>
            </form>
        <?php endif; ?>
    </header>
    <?php if ($feedback !== ''): ?>
        <p class="notice" role="status"><?= e($feedback) ?></p>
    <?php endif; ?>
    <?php if (!$access): ?>
        <section>
            <form method="post" action="<?= e($url) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="login">
                <label for="developer-code">Kode akses</label>
                <input type="password" id="developer-code" name="kode" maxlength="32" minlength="32" required autocomplete="current-password">
                <button type="submit">Masuk</button>
            </form>
        </section>
    <?php else: ?>
        <p>Worker pengingat <?= $workerSeen !== false && time() - $workerSeen < 90 ? 'terdeteksi berjalan.' : 'belum terdeteksi. Periksa proses pengingat di server.' ?></p>
        <section aria-labelledby="telegram-title">
            <h2 id="telegram-title">Telegram</h2>
            <p><?= $telegram !== null ? 'Bot terpasang: @' . e($telegram) : 'Bot belum dikonfigurasi.' ?></p>
            <form method="post" action="<?= e($url) ?>">
                <?= csrf_field() ?>
                <label for="bot-token">Token bot dari BotFather</label>
                <input type="password" id="bot-token" name="bot_token" maxlength="128" autocomplete="off" spellcheck="false">
                <div class="actions">
                    <button name="aksi" value="telegram_save">Verifikasi dan simpan</button>
                    <button class="secondary" name="aksi" value="telegram_check" <?= $telegram === null ? 'disabled' : '' ?>>Cek koneksi bot</button>
                </div>
            </form>
        </section>
        <section aria-labelledby="whatsapp-title">
            <h2 id="whatsapp-title">WhatsApp</h2>
            <p id="wa-state" role="status"><?= e($wa['label']) ?></p>
            <p id="wa-sender" <?= $wa['sender'] !== '' ? '' : 'hidden' ?>>Nomor pengirim <span><?= e($wa['sender']) ?></span></p>
            <form method="post" action="<?= e($url) ?>">
                <?= csrf_field() ?>
                <button id="wa-reconnect" name="aksi" value="wa_reconnect" <?= $wa['reconnect'] ? '' : 'disabled' ?>>Hubungkan WhatsApp</button>
            </form>
            <div id="wa-pair" <?= $wa['image'] ? '' : 'hidden' ?>>
                <p>Di HP nomor pengirim, buka <strong>WhatsApp → Perangkat tertaut → Tautkan perangkat</strong>, lalu scan QR.</p>
                <img id="wa-qr" width="280" height="280" alt="QR pairing WhatsApp PUTU" <?php if ($wa['image']): ?>src="<?= e($wa['image']) ?>"<?php endif; ?>>
            </div>
        </section>
        <section aria-labelledby="push-title">
            <h2 id="push-title">Web Push</h2>
            <?php if (!empty($gateway['publicKey'])): ?>
                <p>Gateway siap. Tes akan muncul di browser ini.</p>
                <form id="web-push-form" method="post" action="<?= e($url) ?>" data-test="1"
                      data-key="<?= e($gateway['publicKey']) ?>" data-worker="<?= e(asset('notification-sw.js')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="web_test">
                    <input type="hidden" name="subscription">
                    <button type="button" disabled>Tes notifikasi browser</button>
                    <p role="status"></p>
                </form>
                <script nonce="<?= e($nonce) ?>" src="<?= e(asset('public/assets/js/web-push.js') . '?mode=developer') ?>" defer></script>
            <?php else: ?>
                <p>Gateway Web Push belum dapat dihubungi.</p>
            <?php endif; ?>
        </section>
        <script nonce="<?= e($nonce) ?>">
            const state = document.getElementById('wa-state');
            const sender = document.getElementById('wa-sender');
            const connect = document.getElementById('wa-reconnect');
            const pair = document.getElementById('wa-pair');
            const qr = document.getElementById('wa-qr');
            async function refresh() {
                let status;
                try {
                    const response = await fetch('?status=1', { cache: 'no-store' });
                    if (response.status === 403 || response.status === 404) {
                        state.textContent = 'Sesi Developer berakhir. Muat ulang halaman.';
                        connect.disabled = true;
                        qr.removeAttribute('src');
                        pair.hidden = true;
                        sender.hidden = true;
                        return;
                    }
                    if (response.ok) status = await response.json();
                } catch {
                    // Coba lagi pada polling berikutnya.
                }
                state.textContent = status?.label || 'Status belum bisa dimuat.';
                sender.hidden = !status?.sender;
                sender.querySelector('span').textContent = status?.sender || '';
                connect.disabled = !status?.reconnect;
                const image = status?.image;
                if (image && qr.getAttribute('src') !== image) qr.src = image;
                if (!image) qr.removeAttribute('src');
                pair.hidden = !image;
                setTimeout(refresh, 4000);
            }
            setTimeout(refresh, 4000);
        </script>
    <?php endif; ?>
</main>
</body>
</html>
