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
2. Pastikan `APP_URL` dapat dibuka penerima pesan. Browser butuh HTTPS untuk
   deployment. `localhost:8000` cukup untuk uji lokal di perangkat yang sama.
3. Dari root PUTU, jalankan perintah ini di host yang sama dengan PHP web dan worker.

   ```sh
   PUPPETEER_SKIP_DOWNLOAD=true npm --prefix notification-gateway ci
   node notification-gateway/gateway.cjs --whatsapp
   ```

   Gunakan Node 22.12+ dan Chrome terpasang. `PUTU_CHROME` dapat menunjuk ke
   executable Chrome lain. Jalankan PHP dan gateway sebagai user OS yang sama.
   Gateway memakai socket privat dalam `~/.config/putu/notifications` dan
   menyimpan VAPID serta sesi WhatsApp di sana. `PUTU_NOTIFICATION_DIR` dapat
   mengganti lokasi pada kedua proses. Tetapkan `PUTU_VAPID_SUBJECT` ke kontak
   pengelola saat deploy. Simpan direktori agar kunci browser dan sesi WA tetap ada.
4. Buka QR lewat terminal lokal dengan `node notification-gateway/gateway.cjs --show-qr`.
   Scan dari HP nomor pengirim lewat **Perangkat tertaut → Tautkan perangkat**.
5. Jalankan `php bin/telegram-worker.php --check`, lalu jalankan
   `php bin/telegram-worker.php` sebagai proses latar yang tetap hidup. Nama file
   worker dipertahankan untuk setup Telegram lama. Setelah memperbarui kode,
   restart worker dan gateway agar keduanya memakai versi terbaru.
6. Login sebagai user, buka Pengaturan PUTU, hubungkan kanal pilihan dan jalankan
   tes masing-masing. Link WhatsApp membuka pesan kode sekali pakai. User harus
   mengirim pesan itu sendiri. User dapat mengirim `STOP` untuk berhenti.

WhatsApp memakai klien tidak resmi. Perubahan WhatsApp Web dapat memutus sesi
atau merusak integrasi. Akun pengirim juga berisiko terkena pembatasan.
[Dokumentasi whatsapp-web.js](https://github.com/wwebjs/whatsapp-web.js).
