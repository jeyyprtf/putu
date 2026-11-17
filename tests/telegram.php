<?php
/** Integration test: hanya database khusus tes; tidak menghubungi Telegram. */
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/telegram.php';
require __DIR__ . '/../app/telegram-api.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

check(str_ends_with(env('DB_NAME', ''), '_test'), 'DB_NAME harus berakhiran _test.');
check((int) db_one('SELECT COUNT(*) AS n FROM public.users')['n'] === 0, 'Gunakan DB tes kosong.');
putenv('TELEGRAM_BOT_TOKEN=123:test');
putenv('TELEGRAM_BOT_USERNAME=putu_pbl_bot');
putenv('APP_URL=https://putu.example.test');
db()->beginTransaction();
try {
    $classA = (int) db_one("INSERT INTO kelas (nama_kelas, kode_gabung) VALUES ('A', 'TEST-A') RETURNING id_kelas")['id_kelas'];
    $classB = (int) db_one("INSERT INTO kelas (nama_kelas, kode_gabung) VALUES ('B', 'TEST-B') RETURNING id_kelas")['id_kelas'];
    $users = [];
    foreach (['Alice' => $classA, 'Bob' => $classA, 'Carol' => $classB] as $name => $class) {
        $users[$name] = (int) db_one(
            "INSERT INTO users (nama, email, password_hash, id_kelas) VALUES (:name, :email, 'unused', :class) RETURNING id_user",
            ['name' => $name, 'email' => strtolower($name) . '@example.test', 'class' => $class]
        )['id_user'];
    }
    $alice = $users['Alice'];
    $bob = $users['Bob'];
    $carol = $users['Carol'];
    $testMessages = [];
    $testSend = function (int $chat, string $text) use (&$testMessages): void { $testMessages[] = [$chat, $text]; };
    try {
        telegram_test_notification($alice, $testSend);
        throw new LogicException('Tes notifikasi harus menolak user yang belum terhubung.');
    } catch (InvalidArgumentException $expected) {
        check($testMessages === [], 'Tidak boleh mengirim tes sebelum akun terhubung.');
    }
    check(telegram_account($alice)['reminder_hours'] === 24, 'Default reminder harus 24 jam.');
    telegram_preferences($alice, '24', true, true);
    telegram_preferences($bob, '1', true, false);
    telegram_preferences($carol, '24', true, true);
    foreach (['0', '169', '-1', '1.5', '1e2', '', '99999999999'] as $bad) {
        try {
            telegram_preferences($alice, $bad, true, true);
            throw new LogicException('Preferensi tidak valid diterima: ' . $bad);
        } catch (InvalidArgumentException $expected) {
        }
    }
    check((int) telegram_account($alice)['reminder_hours'] === 24, 'Validasi gagal mengubah preferensi.');
    $messages = [];
    $send = function (int $chat, string $text) use (&$messages): void { $messages[] = [$chat, $text]; };
    $update = function (int $chat, string $text): array {
        return ['message' => ['chat' => ['type' => 'private', 'id' => $chat], 'from' => ['id' => $chat], 'text' => $text]];
    };
    $tokenFromUrl = function (string $url): string {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        return $query['start'];
    };
    $oldToken = $tokenFromUrl(telegram_connect_url($alice));
    $token = $tokenFromUrl(telegram_connect_url($alice));
    check(strlen($token) === 48 && $token !== $oldToken, 'Token harus acak dan sesuai batas deep link.');
    check(telegram_account($alice)['connect_hash'] === hash('sha256', $token), 'Hanya hash token yang disimpan.');
    telegram_handle_update($update(101, '/start ' . $oldToken), $send);
    check(telegram_account($alice)['chat_id'] === null, 'Tautan lama harus dibatalkan saat membuat tautan baru.');
    $group = $update(101, '/start ' . $token);
    $group['message']['chat']['type'] = 'group';
    telegram_handle_update($group, $send);
    $forged = $update(101, '/start ' . $token);
    $forged['message']['from']['id'] = 999;
    telegram_handle_update($forged, $send);
    check(telegram_account($alice)['chat_id'] === null, 'Group/pengirim yang berbeda tidak boleh menghubungkan akun.');
    telegram_handle_update($update(101, '/start ' . $token), $send);
    check((int) telegram_account($alice)['chat_id'] === 101, 'Token sah harus menghubungkan akun.');
    telegram_handle_update($update(999, '/start ' . $token), $send);
    check((int) telegram_account($alice)['chat_id'] === 101, 'Token sekali pakai tidak boleh diambil alih.');
    check(telegram_account($alice)['connect_hash'] === null, 'Token harus habis setelah berhasil.');
    telegram_test_notification($alice, $testSend);
    check(count($testMessages) === 1 && $testMessages[0][0] === 101, 'Pesan tes harus dikirim sekali ke Telegram milik user.');
    check(str_contains($testMessages[0][1], 'Tes notifikasi PUTU') && str_contains($testMessages[0][1], 'WIB'), 'Pesan tes harus jelas dan memiliki waktu WIB.');

    $bobToken = $tokenFromUrl(telegram_connect_url($bob));
    telegram_handle_update($update(101, '/start ' . $bobToken), $send);
    check(telegram_account($bob)['chat_id'] === null, 'Satu Telegram tidak boleh terhubung ke dua akun.');
    db_exec("UPDATE putu_bot.accounts SET connect_expires = CURRENT_TIMESTAMP - INTERVAL '1 minute' WHERE id_user = :id", ['id' => $bob]);
    telegram_handle_update($update(102, '/start ' . $bobToken), $send);
    check(telegram_account($bob)['chat_id'] === null, 'Token kedaluwarsa harus ditolak.');
    telegram_handle_update($update(102, '/start ' . $tokenFromUrl(telegram_connect_url($bob))), $send);
    telegram_handle_update($update(103, '/start ' . $tokenFromUrl(telegram_connect_url($carol))), $send);

    $courseA = (int) db_one("INSERT INTO mata_kuliah (id_kelas, nama_matkul, sks, semester) VALUES (:class, 'Basis Data', 3, 3) RETURNING id_matkul", ['class' => $classA])['id_matkul'];
    $courseB = (int) db_one("INSERT INTO mata_kuliah (id_kelas, nama_matkul, sks, semester) VALUES (:class, 'Jaringan', 3, 3) RETURNING id_matkul", ['class' => $classB])['id_matkul'];
    $task = function (int $course, string $title, string $interval) use ($alice): int {
        return (int) db_one(
            "INSERT INTO tugas (id_matkul, dibuat_oleh, judul_tugas, deskripsi, deadline)
             VALUES (:course, :user, :title, 'test', CURRENT_TIMESTAMP + CAST(:interval AS interval)) RETURNING id_tugas",
            ['course' => $course, 'user' => $alice, 'title' => $title, 'interval' => $interval]
        )['id_tugas'];
    };
    $due = $task($courseA, 'Tugas dekat', '2 hours');
    $done = $task($courseA, 'Sudah selesai', '2 hours');
    $expired = $task($courseA, 'Sudah lewat', '-1 minute');
    $future = $task($courseA, 'Masih jauh', '48 hours');
    $otherClass = $task($courseB, 'Tugas kelas B', '2 hours');
    db_exec("INSERT INTO progres_tugas (id_user, id_tugas, status, selesai_at) VALUES (:user, :task, 'done', CURRENT_TIMESTAMP)", ['user' => $alice, 'task' => $done]);
    $pending = telegram_pending();
    check(count($pending) === 2, 'Harus ada dua reminder: Alice dan Carol; Bob memakai 1 jam.');
    check((int) $pending[0]['id_user'] === $alice && (int) $pending[0]['source_id'] === $due, 'Reminder Alice harus hanya tugas aktif kelas A.');
    check((int) $pending[1]['id_user'] === $carol && (int) $pending[1]['source_id'] === $otherClass, 'Carol hanya boleh mendapat tugas kelas B.');
    $preview = telegram_notification_text($pending[0]);
    check(str_contains($preview, 'Alice') && str_contains($preview, 'WIB') && str_contains($preview, 'https://putu.example.test/'), 'Pesan harus personal, WIB, dan punya tautan PUTU.');
    $expectedDeadline = (new DateTimeImmutable($pending[0]['deadline']))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d-m-Y H:i') . ' WIB';
    foreach ([$pending[0]['title'], $pending[0]['course'], $expectedDeadline, 'https://putu.example.test/app/pages/tugas.php'] as $detail) {
        check(str_contains($preview, $detail), 'Reminder harus memuat judul, mata kuliah, deadline lengkap, dan tautan PUTU.');
    }
    check(telegram_deliver($send) === 2, 'Kirim dua reminder yang memenuhi syarat.');
    check(telegram_deliver($send) === 0, 'Pemeriksaan berikutnya tidak boleh mengirim duplikat.');
    telegram_preferences($alice, '168', true, true);
    $pending = telegram_pending();
    check(count($pending) === 1 && (int) $pending[0]['source_id'] === $future, 'Perubahan preferensi hanya menambahkan tugas yang belum pernah diingatkan.');
    telegram_preferences($alice, '24', false, true);
    db_exec("UPDATE tugas SET deadline = deadline + INTERVAL '1 minute' WHERE id_tugas = :id", ['id' => $due]);
    check(telegram_pending() === [], 'Reminder nonaktif harus dilewati meskipun deadline berubah.');
    telegram_preferences($alice, '24', true, true);
    check(count(telegram_pending()) === 1, 'Deadline berubah harus menghasilkan satu reminder baru.');
    $failedSend = function (): void { throw new RuntimeException('Simulated network failure'); };
    check(telegram_deliver($failedSend) === 0 && count(telegram_pending()) === 1, 'Pesan gagal harus tetap dapat dicoba ulang.');
    check(telegram_deliver($send) === 1, 'Retry pesan gagal harus berhasil.');

    // Pengumuman lama tidak dikirim, termasuk yang baru dibuat sebelum user menghubungkan akun.
    db_exec("UPDATE putu_bot.accounts SET connected_at = CURRENT_TIMESTAMP - INTERVAL '1 minute'");
    $news = function (int $class, string $age) use ($alice): int {
        return (int) db_one(
            "INSERT INTO pengumuman (id_kelas, dibuat_oleh, judul, isi_pengumuman, created_at)
             VALUES (:class, :user, 'Kabar <b>kelas</b>', :body, CURRENT_TIMESTAMP + CAST(:age AS interval)) RETURNING id_pengumuman",
            ['class' => $class, 'user' => $alice, 'body' => str_repeat('🔥', 5000), 'age' => $age]
        )['id_pengumuman'];
    };
    $news($classA, '-1 day');
    $newA = $news($classA, '0 seconds');
    $newB = $news($classB, '0 seconds');
    $pending = telegram_pending();
    check(count($pending) === 2, 'Hanya pengumuman baru sekelas; Bob mematikan pengumuman.');
    check((int) $pending[0]['source_id'] === $newA && (int) $pending[1]['source_id'] === $newB, 'Pengumuman harus terisolasi per kelas.');
    $text = telegram_notification_text($pending[0]);
    check(preg_match_all('/./su', $text) < 4096 && preg_match('//u', $text) === 1 && str_contains($text, '…'), 'Pesan panjang/emoji harus tetap UTF-8 dan di bawah batas Telegram.');
    check(telegram_deliver($send) === 2 && telegram_deliver($send) === 0, 'Pengumuman tidak boleh terkirim ulang.');

    $task($courseA, 'Akan dibatalkan setelah fetch', '30 minutes');
    $cancelled = false;
    $cancelSend = function (int $chat, string $text) use (&$cancelled, $alice, $bob): void {
        // Setelah kiriman pertama, batalkan penerima berikutnya sebelum gilirannya.
        telegram_disconnect($bob);
        $cancelled = true;
    };
    check(telegram_deliver($cancelSend) === 1 && $cancelled, 'Disconnect setelah fetch harus membatalkan kiriman penerima berikutnya.');
    telegram_handle_update($update(101, '/stop'), $send);
    check(telegram_account($alice)['chat_id'] === null, '/stop harus memutus akun.');
    telegram_handle_update($update(101, '/stop'), $send);
    $carolNewTask = $task($courseB, 'Bot diblokir', '30 minutes');
    $blocked = function (): void { throw new RuntimeException('Blocked', 403); };
    check(telegram_deliver($blocked) === 0 && telegram_account($carol)['chat_id'] === null, '403 harus memutus bot yang diblokir.');
    $pendingToken = $tokenFromUrl(telegram_connect_url($alice));
    telegram_disconnect($alice);
    telegram_handle_update($update(101, '/start ' . $pendingToken), $send);
    check(telegram_account($alice)['chat_id'] === null, 'Disconnect harus membatalkan tautan yang belum digunakan.');

    check((int) db_one("SELECT COUNT(*) AS n FROM pg_tables WHERE schemaname = 'putu_bot' AND rowsecurity")['n'] === 3, 'Semua tabel bot harus mengaktifkan RLS.');
    $offset = db_one('SELECT update_offset FROM putu_bot.state WHERE singleton');
    check((int) $offset['update_offset'] === 0, 'Offset polling harus tersimpan di DB.');
    $cooldown = new TelegramApiException(429, 123);
    check($cooldown->retryAfter === 123 && $cooldown->getCode() === 429, 'Retry-after tidak boleh dipotong.');
    echo "PASS: koneksi aman, token, preferensi, isolasi kelas, progres, deadline, pengumuman, retry, deduplikasi, pembatalan, /stop, dan RLS.\n";
} finally {
    db()->rollBack();
}
