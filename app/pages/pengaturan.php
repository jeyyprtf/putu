<?php
$page = 'pengaturan';
$title = 'Profil & pengaturan';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../telegram-api.php';
require_once __DIR__ . '/../notification-channels.php';
require_once __DIR__ . '/../notifications.php';
require_once __DIR__ . '/../components/keluarKelas.php';
require_once __DIR__ . '/../profil.php';

$user = require_login();
$kelasError = keluar_kelas_proses($user);

// ---------------------------------------------------------------- Profil

$profilError = '';
$profilOk = ($_GET['profil'] ?? '') === 'disimpan'
    ? 'Informasi pribadi berhasil diperbarui.'
    : '';

$formProfil = [
    'nama' => $user['nama'],
    'no_hp' => '',
];

try {
    $dataProfil = profil_ambil((int) $user['id_user']);

    if ($dataProfil === null) {
        throw new RuntimeException('Pengguna tidak ditemukan.');
    }

    $formProfil['no_hp'] = $dataProfil['no_hp'] ?? '';

    if (is_post() && post_str('aksi') === 'profil_simpan') {
        $formProfil['nama'] = trim(post_str('nama'));
        $formProfil['no_hp'] = trim(post_str('no_hp'));

        $panjangNama = text_length($formProfil['nama']);

        if (!csrf_verify()) {
            $profilError = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        } elseif ($panjangNama === null || $panjangNama < 1 || $panjangNama > 80) {
            $profilError = 'Nama lengkap harus diisi dan maksimal 80 karakter.';
        } elseif (
            $formProfil['no_hp'] !== ''
            && !preg_match('/^\+?[0-9]{6,19}$/D', $formProfil['no_hp'])
        ) {
            $profilError = 'Nomor telepon harus berisi 6–19 angka dan boleh diawali tanda +.';
        } else {
            $berhasil = profil_ubah(
                (int) $user['id_user'],
                $formProfil['nama'],
                $formProfil['no_hp'] !== '' ? $formProfil['no_hp'] : null
            );

            if (!$berhasil) {
                throw new RuntimeException('Profil pengguna tidak ditemukan.');
            }

            redirect(url('pengaturan', ['profil' => 'disimpan']));
            exit;
        }
    }
} catch (Throwable $e) {
    error_log('[pengaturan/profil] ' . $e->getMessage());
    $profilError = 'Informasi pribadi belum bisa diproses. Coba lagi nanti.';
}

$gateway = notification_gateway(['action' => 'status']);
$telegram = null;
$telegramError = '';
$telegramOk = '';
$telegramReady = telegram_configured();

try {
    $telegram = telegram_account((int) $user['id_user']);

    if (is_post() && !in_array(post_str('aksi'), ['keluar_kelas', 'profil_simpan'], true)) {
        if (!csrf_verify()) {
            $telegramError = 'Sesi form kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        } elseif (post_str('aksi') === 'telegram_connect') {
            header('Referrer-Policy: no-referrer');
            redirect(telegram_connect_url((int) $user['id_user']));
        } elseif (post_str('aksi') === 'telegram_disconnect') {
            telegram_disconnect((int) $user['id_user']);
            redirect(url('pengaturan', ['telegram' => 'disconnected']));
        } elseif (post_str('aksi') === 'telegram_save') {
            telegram_preferences(
                (int) $user['id_user'],
                post_str('reminder_hours'),
                post_str('reminders_enabled') === '1',
                post_str('news_enabled') === '1'
            );
            redirect(url('pengaturan', ['telegram' => 'saved']));
        } elseif (post_str('aksi') === 'whatsapp_connect') {
            header('Referrer-Policy: no-referrer');
            redirect(notification_whatsapp_url((int) $user['id_user'], $gateway['sender'] ?? ''));
        } elseif (in_array(post_str('aksi'), ['whatsapp_disconnect', 'web_disconnect'], true)) {
            $channel = post_str('aksi') === 'web_disconnect' ? 'web' : 'whatsapp';
            notification_disconnect((int) $user['id_user'], $channel);
            redirect(url('pengaturan', ['telegram' => 'disconnected']));
        } elseif (post_str('aksi') === 'web_connect') {
            if (empty($gateway['publicKey'])) {
                throw new InvalidArgumentException('Notifikasi browser belum siap.');
            }
            $_SESSION['notification_web'] ??= bin2hex(random_bytes(32));
            notification_web_save((int) $user['id_user'], $_SESSION['notification_web'], post_str('subscription'));
            redirect(url('pengaturan', ['telegram' => 'saved']));
        } elseif (in_array(post_str('aksi'), ['whatsapp_test', 'web_test'], true)) {
            if (time() - ($_SESSION['telegram_test_at'] ?? 0) < 30) {
                throw new InvalidArgumentException('Tunggu 30 detik sebelum mengirim pesan tes lagi.');
            }

            $channel = post_str('aksi') === 'web_test' ? 'web' : 'whatsapp';
            $target = $channel === 'web' ? ($telegram['web_subscription'] ?? null) : ($telegram['wa_chat'] ?? null);

            if ($target === null) {
                throw new InvalidArgumentException('Aktifkan kanal ini sebelum mengirim pesan tes.');
            }

            $_SESSION['telegram_test_at'] = time();

            try {
                $message = notification_message(['kind' => 'test', 'channel' => $channel]);
                notification_send_gateway($channel, $target, $message, $telegram['wa_sender'] ?? '');
            } catch (RuntimeException $error) {
                if ($channel === 'web' && in_array($error->getCode(), [400, 404, 410], true)) {
                    notification_web_expire(
                        (int) $user['id_user'],
                        $target,
                        $telegram['web_connected_at']
                    );
                    redirect(url('pengaturan', ['telegram' => 'expired']));
                }
                throw new InvalidArgumentException('Pesan tes gagal dikirim. Periksa koneksi kanal lalu coba lagi.');
            }

            redirect(url('pengaturan', ['telegram' => 'accepted']));
        } elseif (post_str('aksi') === 'telegram_test') {
            if (time() - ($_SESSION['telegram_test_at'] ?? 0) < 30) {
                throw new InvalidArgumentException('Tunggu 30 detik sebelum mengirim pesan tes lagi.');
            }

            telegram_test_notification((int) $user['id_user'], function (int $chatId, string $text): void {
                $_SESSION['telegram_test_at'] = time();
                telegram_api('sendMessage', ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML',
                    'link_preview_options' => ['is_disabled' => true]]);
            });
            redirect(url('pengaturan', ['telegram' => 'tested']));
        } else {
            $telegramError = 'Aksi tidak dikenal.';
        }
    }

    $status = $_GET['telegram'] ?? '';
    $telegramOk = match ($status) {
        'saved' => 'Pengaturan notifikasi disimpan.',
        'disconnected' => 'Koneksi notifikasi diputus.',
        'tested' => 'Pesan tes berhasil dikirim. Cek chat bot PUTU di Telegram.',
        'accepted' => 'Pesan tes diterima layanan pengiriman. Cek perangkatmu.',
        'expired' => 'Langganan browser kedaluwarsa. Aktifkan lagi pada perangkatmu.',
        default => '',
    };
} catch (InvalidArgumentException $e) {
    $telegramError = $e->getMessage();
} catch (TelegramApiException $e) {
    error_log('[telegram test] Pengiriman gagal (kode ' . $e->getCode() . ').');
    $telegramError = match ($e->getCode()) {
        403 => 'Bot tidak dapat mengirim pesan. Buka Telegram dan pastikan bot PUTU tidak diblokir.',
        429 => 'Telegram membatasi pengiriman. Coba lagi setelah ' . $e->retryAfter . ' detik.',
        default => 'Pesan tes belum bisa dikirim. Coba lagi nanti atau hubungi pengelola PUTU.',
    };
} catch (Throwable $e) {
    error_log('[telegram settings] ' . $e->getMessage());
    $telegramError = 'Pengaturan Telegram belum tersedia. Hubungi pengelola PUTU.';
}
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <?php require __DIR__ . '/../components/head.php'; ?>
    </head>
    <body class="bg-stone-950 text-stone-100 font-sans antialiased">
        <?php require __DIR__ . '/../components/sidebar.php'; ?>
        <div class="lg:ml-68">
            <?php require __DIR__ . '/../components/navbar.php'; ?>
            <main id="konten" class="workspace-content mx-auto max-w-7xl px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
                <div class="mb-7">
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Profil & pengaturan<span class="text-lime-300">.</span></h1>
                    <p class="mt-3 text-sm leading-6 text-stone-400">Ruang kecil untuk hal-hal tentang kamu.</p>
                </div>
                <div class="grid items-start gap-4 lg:grid-cols-2 lg:gap-5">
                    <section aria-labelledby="profile-title" class="panel rounded-3xl! min-w-0 overflow-hidden">
                        <div class="flex items-center gap-4 border-b border-stone-800 bg-stone-950/30 p-5 sm:p-6">
                            <span class="flex size-16 shrink-0 items-center justify-center rounded-3xl bg-lime-300/15 text-2xl font-semibold text-lime-200"><?= e($profile['initial']) ?></span>
                            <div class="min-w-0">
                                <p class="truncate text-lg font-semibold"><?= e($profile['name']) ?></p>
                                <p class="mt-1 text-xs text-stone-400"><?= e($profile['role']) ?> · <?= e($profile['kelas']) ?></p>
                            </div>
                        </div>
                        <div class="p-5 sm:p-6">
                            <h2 id="profile-title" class="text-base font-semibold">Informasi pribadi</h2>
                            <form id="profile-form" action="<?= e(url('pengaturan')) ?>" method="POST" class="mt-6 space-y-5">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="profil_simpan">

                            <div>
                                <label for="profile-name" class="mb-2 block text-xs font-semibold">
                                    Nama lengkap
                                </label>
                                <input
                                    id="profile-name"
                                    name="nama"
                                    type="text"
                                    value="<?= e($formProfil['nama']) ?>"
                                    required
                                    maxlength="80"
                                    autocomplete="name"
                                    class="field rounded-3xl!"
                                >
                            </div>

                            <div>
                                <label for="profile-phone" class="mb-2 block text-xs font-semibold">
                                    Nomor telepon
                                </label>
                                <input
                                    id="profile-phone"
                                    name="no_hp"
                                    type="tel"
                                    value="<?= e($formProfil['no_hp']) ?>"
                                    maxlength="20"
                                    autocomplete="tel"
                                    placeholder="Contoh: 081234567890"
                                    aria-describedby="profile-phone-hint"
                                    class="field rounded-3xl!"
                                >
                            </div>

                            <?php if ($profilOk !== ''): ?>
                                <p role="status" class="text-sm text-lime-200"><?= e($profilOk) ?></p>
                            <?php endif; ?>

                            <?php if ($profilError !== ''): ?>
                                <p role="alert" class="text-sm text-red-300"><?= e($profilError) ?></p>
                            <?php endif; ?>

                            <div class="flex flex-wrap justify-end gap-3 border-t border-stone-800 pt-5">
                                <button id="profile-save" type="submit" class="button rounded-3xl! w-full">
                                    <?= icon('check', 'size-4') ?> Simpan perubahan
                                </button>
                            </div>
                        </form>
                        </div>
                    </section>

                    <section aria-labelledby="class-title" class="panel rounded-3xl! min-w-0 p-5 sm:p-6 ">
                        <span class="icon-button rounded-3xl! text-lime-300"><?= icon('book') ?></span>
                        <h2 id="class-title" class="mt-4 text-base font-semibold">Kelas kamu</h2>
                        <p class="mt-2 text-xs leading-6 text-stone-400">Tempat tugas, jadwal, dan kabar kelasmu berkumpul.</p>
                        <dl class="mt-5 space-y-4 border-t border-stone-800 pt-5 text-sm">
                            <div><dt class="text-xs text-stone-400">Kelas</dt><dd class="mt-1.5 font-medium"><?= e($profile['kelas']) ?></dd></div>
                            <div><dt class="text-xs text-stone-400">Peran</dt><dd class="mt-1.5 font-medium"><?= e($profile['role']) ?></dd></div>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <div>
                                    <dt class="text-xs text-stone-400">
                                        Kode gabung (bagikan ke teman sekelas)
                                    </dt>
                                    <dd class="mt-1.5">
                                        <button
                                            type="button"
                                            id="copy-kode-gabung"
                                            data-kode="<?= e($currentUser['kode_gabung']) ?>"
                                            class="inline-flex min-h-11 items-center gap-2 rounded-xl text-left text-lime-300 transition hover:text-lime-200 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lime-300"
                                            aria-label="Salin kode gabung <?= e($currentUser['kode_gabung']) ?>"
                                        >
                                            <span class="font-mono text-base font-semibold tracking-[0.2em]">
                                                <?= e($currentUser['kode_gabung']) ?>
                                            </span>
                                            <?= icon('copy', 'size-4') ?>
                                        </button>
                                        <span id="copy-kode-status" role="status" class="ml-2 text-xs text-stone-400"></span>
                                    </dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                        <a href="<?= e(url('jadwal')) ?>" class="mt-5 flex min-h-11 items-center justify-between gap-2 border-t border-stone-800 pt-4 text-xs font-semibold text-lime-300">Lihat jadwal kelas <?= icon('arrow', 'size-4') ?></a>
                        <?php if ($kelasError !== ''): ?>
                            <p role="alert" class="mt-4 rounded-2xl bg-red-300/10 px-4 py-3 text-sm text-red-300"><?= e($kelasError) ?></p>
                        <?php endif; ?>
                        <button type="button" id="buka-keluar-kelas" class="button button-secondary mt-4 w-full text-red-300!">Keluar kelas</button>
                    </section>
                </div>

                <section aria-labelledby="telegram-title" class="panel mt-5 rounded-3xl! p-5 sm:p-6">
                    <h2 id="telegram-title" class="text-base font-semibold">Notifikasi</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-400">Pengingat pribadi untuk tugas yang belum selesai dan kabar dari kelasmu.</p>
                    <?php if ($telegramOk !== ''): ?>
                        <p role="status" class="mt-4 text-sm text-lime-200"><?= e($telegramOk) ?></p>
                    <?php endif; ?>
                    <?php if ($telegramError !== ''): ?>
                        <p role="alert" class="mt-4 text-sm text-red-300"><?= e($telegramError) ?></p>
                    <?php endif; ?>
                    <?php if (!$telegramReady): ?>
                        <p class="mt-4 text-sm text-stone-400">Bot Telegram belum diaktifkan oleh pengelola PUTU.</p>
                    <?php endif; ?>
                    <?php if ($telegram !== null): ?>
                        <p class="mt-4 text-sm font-semibold"><?= $telegram['chat_id'] !== null ? 'Telegram terhubung' : 'Telegram belum terhubung' ?></p>
                        <?php if ($telegram['chat_id'] === null && $telegramReady): ?>
                            <p id="telegram-connect-hint" class="mt-2 text-xs leading-6 text-stone-400">Buka bot, tekan Start, lalu kembali dan muat ulang halaman ini untuk melihat status koneksi. Tautan berlaku 10 menit.</p>
                            <form action="<?= e(url('pengaturan')) ?>" method="POST" target="_blank" rel="noopener noreferrer" class="mt-3">
                                <?= csrf_field() ?>
                                <button name="aksi" value="telegram_connect" class="button" aria-describedby="telegram-connect-hint">Hubungkan Telegram <?= icon('arrow', 'size-4') ?></button>
                            </form>
                        <?php endif; ?>
                        <form action="<?= e(url('pengaturan')) ?>" method="POST" class="mt-5 max-w-xl space-y-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="telegram_save">
                            <div>
                                <label for="reminder-hours" class="mb-2 block text-xs font-semibold">Ingatkan berapa jam sebelum deadline?</label>
                                <input id="reminder-hours" name="reminder_hours" type="number" min="1" max="168" step="1" required value="<?= e((int) $telegram['reminder_hours']) ?>" aria-describedby="reminder-hours-hint" class="field">
                                <p id="reminder-hours-hint" class="mt-2 text-xs leading-6 text-stone-400">1–168 jam, berlaku untuk semua tugasmu. Contoh: 24 untuk sehari sebelumnya. Satu pengingat per tugas; jika deadline berubah, pengingat dapat dikirim lagi.</p>
                            </div>
                            <label class="flex min-h-11 items-center gap-3 text-sm"><input name="reminders_enabled" type="checkbox" value="1" <?= $telegram['reminders_enabled'] ? 'checked' : '' ?> class="size-4 accent-lime-500">Aktifkan pengingat deadline</label>
                            <label class="flex min-h-11 items-center gap-3 text-sm"><input name="news_enabled" type="checkbox" value="1" <?= $telegram['news_enabled'] ? 'checked' : '' ?> class="size-4 accent-lime-500">Kirim pengumuman kelas ke kanal yang terhubung</label>
                            <button type="submit" class="button">Simpan notifikasi</button>
                        </form>
                        <?php if ($telegram['chat_id'] !== null): ?>
                            <form action="<?= e(url('pengaturan')) ?>" method="POST" class="mt-5 border-t border-stone-800 pt-5">
                                <?= csrf_field() ?>
                                <button name="aksi" value="telegram_test" class="button button-secondary disabled:cursor-not-allowed disabled:opacity-50" aria-describedby="telegram-test-hint" <?= !$telegramReady ? 'disabled' : '' ?>><?= icon('bell', 'size-4') ?>Tes notifikasi</button>
                                <p id="telegram-test-hint" class="mt-2 text-xs leading-6 text-stone-400">Kirim satu pesan percobaan ke Telegram kamu. Jeda 30 detik antartes.</p>
                            </form>
                            <form action="<?= e(url('pengaturan')) ?>" method="POST" class="mt-4">
                                <?= csrf_field() ?>
                                <button name="aksi" value="telegram_disconnect" class="button button-secondary">Putuskan koneksi Telegram</button>
                            </form>
                        <?php endif; ?>
                        <?php require __DIR__ . '/../components/notification-channels.php'; ?>
                    <?php endif; ?>
                </section>

                <section class="mt-3">
                    <form action="<?= e(url('logout')) ?>" method="POST" class="lg:hidden" data-logout-confirm>
                        <?= csrf_field() ?>
                        <button type="submit" class="button button-secondary w-full rounded-3xl! text-red-400!">
                            <?= icon('logout', 'size-4') ?>
                            Keluar dari akun
                        </button>
                    </form>
                </section>

            </main>
        </div>
        <?php keluar_kelas_dialog($user, $profile['kelas']); ?>
        <?php require __DIR__ . '/../components/dock.php'; ?>
    </body>
</html>
