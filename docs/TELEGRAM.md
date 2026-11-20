# Bot Telegram PUTU

Versi awal: koneksi akun, satu waktu reminder per user untuk semua tugas,
dan notifikasi pengumuman kelas. Pengaturan ada di **Profil & pengaturan →
Notifikasi Telegram**. Tidak perlu library bot atau layanan AI.

## Setup

1. Jalankan `docs/TELEGRAM.sql` **sekali** pada database PUTU yang sudah berisi
   tabel dari `docs/PUTU_PDM.sql`. SQL ini menambahkan schema `putu_bot` dan dua
   indeks, tanpa mengganti data akademik. Jangan menjalankan ulang PDM pada DB aktif.
2. Salin `app/config/telegram.local.example.php` ke
   `app/config/telegram.local.php`. Isi token BotFather, username tanpa `@`
   (`putu_pbl_bot`), dan `APP_URL` sesuai alamat web yang dipakai pengguna.
   File lokal ini diabaikan Git. Environment variable dengan nama yang sama
   juga dapat dipakai. Token hanya berada di server, tidak di JavaScript/HTML.
3. Pastikan konfigurasi DB worker dan web mengarah ke database PUTU yang sama.
   Gunakan session pooler (5432), sesuai `docs/DATABASE.md`. Schema bot hanya
   untuk backend PDO dengan role pemilik tabel; jangan expose ke Supabase Data API.
4. Cek konfigurasi tanpa mengirim pesan:

   ```bash
   php bin/telegram-worker.php --check
   ```

5. Jalankan proses bot dan biarkan terminal tetap hidup:

   ```bash
   php bin/telegram-worker.php
   ```

6. Login PUTU → Pengaturan → Hubungkan Telegram → tekan **Start** di bot.
   Kembali ke web dan muat ulang halaman untuk melihat status koneksi.
   Atur reminder, misalnya 24 jam, lalu Simpan notifikasi.

Bot menerima `/start`, `/help`, dan `/stop`; pesan lain mendapat petunjuk.
Setelah terhubung, klik **Tes notifikasi** di Pengaturan PUTU untuk mengirim
satu pesan percobaan langsung ke akunmu. Ada jeda 30 detik antartes per sesi.
Tes ini tidak memerlukan tugas mendekati deadline dan tetap bisa dipakai saat
pengingat otomatis dimatikan. Refresh setelah sukses tidak mengirim ulang pesan.
`/stop` memutus koneksi, begitu juga tombol di web. Username tidak dipakai
sebagai identitas pengguna: bot menyimpan private `chat_id` dari Telegram.
Tautan koneksi berisi token acak sekali pakai, berlaku 10 menit; jangan bagikan.

Bot menggunakan **long polling**, sehingga lokal tidak membutuhkan domain
HTTPS/webhook. Telegram tetap diakses lewat HTTPS. Bot yang sudah memakai
webhook akan ditolak saat startup; jangan menjalankan polling dan webhook
bersamaan. [Telegram Bot API](https://core.telegram.org/bots/api#getupdates).
Koneksi akun menggunakan [deep link Telegram](https://core.telegram.org/bots/features#deep-linking).

## Perilaku pengiriman

- Default 24 jam sebelum deadline, dapat diisi bilangan bulat 1–168 jam.
  Deadline asli tugas tidak berubah. Waktu diperiksa dari `TIMESTAMPTZ` DB,
  ditampilkan dalam WIB pada pesan.
- Reminder berlaku untuk semua tugas kelas user, baik mahasiswa maupun admin,
  dengan progres `todo`/`doing` atau belum memiliki progres. Progres `done`
  tidak dikirim. Tugas yang sudah lewat deadline tidak dikirim.
- Satu reminder per user/tugas/deadline. Mengubah preferensi tidak mengirim
  ulang reminder yang sudah terkirim. Perubahan deadline memungkinkan satu
  reminder baru pada waktu yang sesuai.
- Jika user baru menghubungkan bot atau worker pulih ketika sudah memasuki
  rentang reminder, pesan dikirim pada pemeriksaan berikutnya selama belum
  melewati deadline. Tidak ada pengiriman tepat waktu ketika worker mati.
- Hanya pengumuman yang dibuat sejak akun Telegram dihubungkan yang dikirim.
  Menonaktifkan notifikasi menjeda pengiriman; saat dinyalakan lagi, pengumuman
  yang belum terkirim sejak koneksi tersebut dapat menyusul. Pengumuman yang
  dihapus sebelum dikirim dilewati. Teks panjang dipotong dengan tautan ke web.
- Pemeriksaan biasanya setiap sekitar 25 detik saat tidak ada pesan masuk.
  Pengiriman dalam jumlah besar, cooldown Telegram, atau gangguan jaringan
  dapat memperlambatnya. Pesan gagal dicoba lagi; bot yang diblokir (403)
  diputus koneksinya. Rate limit (429) mengikuti `retry_after` Telegram.
- Worker tunggal dijaga advisory lock pada koneksi DB. Riwayat pengiriman dan
  offset update disimpan di DB sehingga restart tidak mengirim ulang pesan
  normal. Jika proses mati setelah Telegram menerima pesan tetapi sebelum
  pencatatan DB, atau respons jaringan hilang, **duplikat masih mungkin**.
  Pengiriman tepat sekali tidak dijamin oleh Telegram `sendMessage`.
- Munculnya banner/suara di HP juga mengikuti pengaturan notifikasi Telegram
  dan perangkat pengguna.

## Menjalankan terus di server Linux

Gunakan supervisor seperti systemd agar worker hidup kembali setelah restart.
Contoh `/etc/systemd/system/putu-telegram.service` (sesuaikan path, user, dan PHP):

```ini
[Unit]
Description=PUTU Telegram bot
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/putu
ExecStart=/usr/bin/php /var/www/putu/bin/telegram-worker.php
Restart=on-failure
RestartSec=10
NoNewPrivileges=true
PrivateTmp=true

[Install]
WantedBy=multi-user.target
```

Pastikan user service bisa membaca konfigurasi lokal, lalu:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now putu-telegram
journalctl -u putu-telegram -f
```

Shared hosting yang tidak mengizinkan proses PHP terus hidup memerlukan
worker di VPS atau penyesuaian ke webhook dan scheduler hosting.
Konfigurasi systemd di atas merupakan contoh, belum dipasang otomatis.

## Verifikasi tanpa Telegram asli

Jalankan pada database lokal **khusus tes**, bukan database PUTU aktif:

```bash
createdb putu_telegram_test
psql -X -v ON_ERROR_STOP=1 -d putu_telegram_test -f docs/PUTU_PDM.sql -f docs/TELEGRAM.sql
psql -X -v ON_ERROR_STOP=1 -d putu_telegram_test -f docs/NOTIFICATIONS.sql
DB_HOST=/var/run/postgresql DB_NAME=putu_telegram_test DB_USER="$USER" php tests/telegram.php
dropdb putu_telegram_test
```

Tes menggunakan PostgreSQL asli dan callback pengiriman palsu, tanpa mengakses
Telegram atau mengirim pesan. Fixture di-rollback setelah pengujian.
Tes transport terpisah: `php tests/telegram-api.php`, memakai stream HTTPS palsu
untuk memeriksa bentuk request, error API/jaringan, dan cooldown.
Tes HTTP terpisah ada di `tests/telegram-http.py`; jalankan server dari checkout
ini memakai DB khusus tes yang sama dan `TELEGRAM_BOT_TOKEN=123:test`
`TELEGRAM_BOT_USERNAME=putu_pbl_bot`, lalu jalankan `python3 tests/telegram-http.py URL`.

Setup Browser dan WhatsApp ada di [NOTIFICATIONS.md](NOTIFICATIONS.md).
