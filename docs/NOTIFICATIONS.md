# Notifikasi PUTU

PHP memilih reminder dan pengumuman dari PostgreSQL. Setiap akun punya satu waktu
reminder dan boleh menghubungkan Telegram, satu browser, serta WhatsApp. Kanal
terhubung adalah kanal yang dipilih. Pengguna bisa mengetes atau memutuskan tiap
kanal dari Pengaturan. Logout melepas browser yang ditautkan melalui sesi itu.

```text
PostgreSQL → worker PHP → Telegram Bot API
                       → gateway Node → Web Push → service worker
                                      → WhatsApp Web (LocalAuth)
```

Worker memeriksa kelas aktif, status tugas, tenggat, dan pilihan kanal sebelum
kirim. Riwayat unik berdasarkan user, kejadian, deadline, dan kanal. Retry setelah
respons hilang masih bisa menggandakan pesan. Tugas yang lewat tenggat dilewati.
Pengumuman baru dikirim ke kanal yang sudah terhubung saat diumumkan.

Setiap kanal punya batch dan jeda retry sendiri di memori worker. Web Push
menahan pengumuman hingga satu jam dan pengingat hingga satu jam atau sampai
deadline. Penyedia push dapat memilih durasi yang lebih pendek.

## Setup

1. Jalankan [TELEGRAM.sql](TELEGRAM.sql) pada database baru, lalu jalankan
   [NOTIFICATIONS.sql](NOTIFICATIONS.sql) sekali. Pada database yang sudah pakai
   Telegram, langsung jalankan NOTIFICATIONS.sql. Riwayat lama tetap milik Telegram.
   Jika database dibuat dari PDM lama, pastikan kolom status tugas sudah ada.

   ```sql
   ALTER TABLE public.tugas ADD COLUMN IF NOT EXISTS aktif BOOLEAN NOT NULL DEFAULT TRUE;
   ```
2. Di server, set `APP_URL=https://putu.juan.web.id` di `app/config/telegram.local.php`
   atau di environment proses PHP web dan worker. Telegram dan WhatsApp memakai
   konfigurasi ini, dengan `https://putu.juan.web.id` sebagai nilai default.
   Jika konfigurasi lama masih menunjuk ke `localhost`, ganti nilainya dan restart
   worker. Untuk uji lokal di perangkat yang sama, `APP_URL` boleh menunjuk ke
   `http://127.0.0.1:8000`. Notifikasi browser membuka tautan di origin tempat
   pengguna mengaktifkannya. Aktifkan dari domain production via HTTPS.
3. Dari root PUTU, jalankan perintah ini di host yang sama dengan PHP web dan worker.

   ```sh
   PUPPETEER_SKIP_DOWNLOAD=true npm --prefix notification-gateway ci
   node notification-gateway/gateway.cjs
   ```

   Gunakan Node 22.12+ dan Chrome terpasang. `PUTU_CHROME` dapat menunjuk ke
   executable Chrome lain. Jalankan PHP dan gateway sebagai user OS yang sama.
   Gateway memakai socket privat dalam `~/.config/putu/notifications` dan
   menyimpan VAPID serta sesi WhatsApp di sana. `PUTU_NOTIFICATION_DIR` dapat
   mengganti lokasi pada PHP web, worker, dan gateway. Tetapkan `PUTU_VAPID_SUBJECT` ke kontak
   pengelola saat deploy. Simpan direktori agar kunci browser dan sesi WA tetap ada.
4. Buat kode Developer di terminal privat.

   ```sh
   php -r '$code = bin2hex(random_bytes(16)); echo "Kode: ", $code, PHP_EOL, "PUTU_DEVELOPER_CODE_HASH=", hash("sha256", $code), PHP_EOL;'
   ```

   Simpan kode aslinya di pengelola password. Pasang hanya hash di environment PHP
   atau sebagai key `PUTU_DEVELOPER_CODE_HASH` di `app/config/database.local.php`.
   Buka `/app/pages/developer.php` lewat HTTPS yang dikenali PHP atau localhost,
   misalnya lewat SSH port forwarding. Sesi Developer terpisah dan berlaku 30 menit.
   Mengganti hash mengakhiri sesi aktif.
   Jika Cloudflare Tunnel meneruskan ke PHP lewat HTTP loopback, isi
   `PUTU_DEVELOPER_TUNNEL_HOST` dengan hostname publiknya, tanpa skema atau port.

   - **Telegram** — tempel token BotFather, lalu pilih **Verifikasi dan simpan**.
     Panel mengambil username sendiri dan memeriksa konflik webhook. Token yang
     tersimpan di direktori notifikasi privat dipakai oleh PHP web dan worker.
     Penggantian token untuk bot yang sama didukung. Migrasi ke bot berbeda
     memerlukan penanganan tautan pengguna dan offset worker tersendiri.
   - **WhatsApp** — pilih **Hubungkan WhatsApp** lalu scan QR di HP pengirim melalui
     **Perangkat tertaut → Tautkan perangkat**. Nomor muncul setelah pairing.
     Untuk ganti perangkat pengirim, lepas sesi PUTU dari HP lama lebih dulu.
   - **Web Push** — pilih **Tes notifikasi browser** di browser target.
     Untuk notifikasi tugas dan pengumuman, sambungkan browser dari Pengaturan akun.
5. Jalankan `php bin/telegram-worker.php` sebagai proses latar yang tetap hidup.
   `php bin/telegram-worker.php --check` memeriksa database, bot Telegram, dan gateway
   setelah bot dikonfigurasi. Nama file
   worker dipertahankan untuk setup Telegram lama. Setelah memperbarui kode,
   restart worker dan gateway agar keduanya memakai versi terbaru.
6. Login sebagai user, buka Pengaturan PUTU, hubungkan kanal pilihan dan jalankan
   tes masing-masing. Link WhatsApp membuka pesan kode sekali pakai. User harus
   mengirim pesan itu sendiri. User dapat mengirim `STOP` untuk berhenti.

WhatsApp memakai klien tidak resmi. Perubahan WhatsApp Web dapat memutus sesi
atau merusak integrasi. Akun pengirim juga berisiko terkena pembatasan.
[Dokumentasi whatsapp-web.js](https://github.com/wwebjs/whatsapp-web.js).
