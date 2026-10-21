# Panduan pembangunan PUTU dengan PHP

Bangun ulang PUTU di `~/Documents/Tugas/PBL/code` dengan PHP native, MySQL melalui PDO, Tailwind CSS, dan JavaScript seperlunya. Selesaikan satu bagian beserta pengujiannya sebelum pindah ke bagian berikutnya. Aplikasi React sebelumnya menjadi referensi perilaku dan tampilan, bukan dependensi aplikasi PHP.

Design guidelines tersedia di [Figma PUTU Web](https://www.figma.com/design/lfbSOjtgyALIyU41ta1h9x/PUTU---Juan-Workspace?node-id=2115-395), pada page **PUTU Web • Design Guidelines**. Tiga lembar acuannya: **Foundations**, **UI Patterns**, dan **Layout & Handoff**. Acuan implementasi tim adalah [panduan snippet Tailwind](TAILWIND_GUIDE.md): palet bawaan stone + emerald, `font-sans`, dan ukuran utility standar. Token Figma memetakan palet tersebut dalam Light/Dark; Inter di canvas menjadi representasi font sistem.

## Kondisi awal

Saat diperiksa pada 20 Oktober 2026, folder sudah memiliki `app/`, `public/assets/`, `resources/css/`, rancangan struktur di `wireframe.md`, dan contoh UI login. Sebagian besar file PHP dan CSS masih kosong. `index.php` di root masih halaman contoh; `public/index.php` belum ada. PHP CLI yang tersedia adalah 8.5.4. Koneksi MySQL dan kelengkapan ekstensi belum diverifikasi.

Panduan ini tidak mengganti file aplikasi yang sedang kamu kerjakan. Perintah pemasangan dan perubahan struktur di bawah dijalankan saat mengerjakan bagian terkait.

## Ruang lingkup versi pertama

Guest melihat landing page dan bisa membuka login atau registrasi. Mahasiswa dapat membuat atau bergabung ke satu kelas, melihat dashboard, tugas, jadwal, pengumuman, dan memperbarui profil. Admin mengelola data akademik untuk kelas yang diizinkan. Progres tugas bersifat pribadi; menandai tugas selesai bukan mengumpulkan berkas tugas.

Gunakan render HTML dari PHP dan form POST biasa. Setelah POST berhasil, redirect ke halaman GET agar refresh tidak mengulang perubahan. Tidak perlu membuat REST API atau SPA untuk versi ini.

## Bagian 1 Menyiapkan struktur dan cara menjalankan

**Hasil:** satu pintu masuk aplikasi, aset CSS bisa dimuat, dan halaman yang tidak dikenal menghasilkan 404.

Pertahankan struktur yang sudah dibuat, lalu lengkapi bertahap:

```text
code/
├── public/
│   ├── index.php                  # Router halaman dan action yang diizinkan
│   └── assets/
│       ├── css/app.css            # CSS hasil build
│       ├── js/app.js              # Interaksi kecil, jika diperlukan
│       └── images/
├── app/
│   ├── bootstrap.php             # Config, session, koneksi, helpers
│   ├── config/database.php
│   ├── helpers.php               # Escape HTML, redirect, auth, CSRF
│   ├── layouts/                  # guest.php, auth.php, dashboard.php
│   ├── components/               # navbar, sidebar, dock, task-card
│   ├── pages/                    # Tampilan HTML
│   └── actions/                  # Pemrosesan POST, diakses melalui router
├── resources/css/app.css         # Sumber CSS
├── database/                     # schema.sql dan data contoh saat Bagian 4
├── tests/                        # Pemeriksaan perilaku penting
├── docs/                         # DEVELOPMENT_GUIDE.md dan TAILWIND_GUIDE.md
└── package.json                  # Hanya untuk build Tailwind
```

Jadikan `public/` document root. Pindahkan isi halaman contoh root ke page yang sesuai saat mulai implementasi, sehingga tidak ada dua router aktif. Config, SQL, dan file `app/` tidak boleh dilayani sebagai berkas publik.

Periksa alat, tanpa menganggap semuanya sudah terpasang:

```bash
cd ~/Documents/Tugas/PBL/code
php -v
php -m
mysql --version
node --version
npm --version
```

Pastikan `PDO`, `pdo_mysql`, dan `session` tersedia. Setelah `public/index.php` siap, jalankan server lokal:

```bash
php -S 127.0.0.1:8000 -t public
```

Server bawaan PHP digunakan untuk pengembangan lokal; deployment memakai server yang menjalankan PHP dengan document root `public/`. [Dokumentasi server PHP](https://www.php.net/manual/en/features.commandline.webserver.php).

**Cek selesai:** halaman utama merespons, CSS dimuat, route tidak dikenal 404, dan permintaan `/app/config/database.php` tidak membuka config.

## Bagian 2 Menetapkan utility Tailwind dan komponen dasar

**Hasil:** layout guest, auth, dan dashboard memakai snippet Tailwind yang sama untuk warna, font, spacing, serta komponen berulang.

Acuan tim adalah [TAILWIND_GUIDE.md](TAILWIND_GUIDE.md). Buka panduan itu untuk menyalin snippet layout, tombol, field/error, kartu tugas, badge, navbar, dan dock. Pakai palet bawaan **stone + emerald** agar nuansa off-white dan hijau PUTU tetap dekat tanpa membuat warna custom. Desain monokrom iOS di Figma tetap menjadi eksplorasi terpisah.

| Penggunaan | Class utama | Class gelap |
| --- | --- | --- |
| Halaman | `bg-stone-50 text-stone-900` | `dark:bg-stone-950 dark:text-stone-100` |
| Panel | `bg-white border-stone-200` | `dark:bg-stone-900 dark:border-stone-800` |
| Teks pendamping | `text-stone-600` | `dark:text-stone-400` |
| Tombol utama | `bg-emerald-900 text-white` | `dark:bg-emerald-200 dark:text-stone-950` |
| Hover tombol | `hover:bg-emerald-950` | `dark:hover:bg-emerald-300` |

Gunakan `font-sans antialiased` pada body; font mengikuti sistem perangkat dan tidak membutuhkan file font tambahan. Gunakan `text-4xl sm:text-5xl lg:text-6xl` untuk hero, `text-3xl` untuk judul halaman, `text-xl` untuk heading, `text-sm leading-6` untuk isi kartu/form, dan `text-xs` untuk label/metadata. Pakai `font-medium` atau `font-semibold` sesuai hierarki. [Font family](https://tailwindcss.com/docs/font-family), [ukuran teks](https://tailwindcss.com/docs/font-size).

Spacing memakai `gap-2`, `gap-3`, `gap-6`, `p-5 sm:p-6`, dan `py-12 lg:py-16`. Radius memakai `rounded-2xl` untuk kontrol, `rounded-3xl` untuk panel, `rounded-4xl` untuk dialog, serta `rounded-full` untuk pill. Tinggi kontrol minimal `min-h-11`. Hindari hex, ukuran arbitrary, dan penggantian nilai warna bawaan ketika utility standar sudah mencukupi.

Mulai dari partial tombol/link, field beserta error, badge status, panel, navbar guest, sidebar, dan dock. Gunakan link untuk berpindah halaman dan button untuk mengirim form atau menjalankan aksi. Label form tetap terlihat; placeholder bukan pengganti label.

Untuk Tailwind v4, saat siap menyiapkan build:

```bash
npm install -D tailwindcss @tailwindcss/cli
```

Isi awal `resources/css/app.css`:

```css
@import "tailwindcss";
@source "../../app";
@source "../../public";
@custom-variant dark (&:where(.dark, .dark *));
```

Jalankan dari root project:

```bash
npx @tailwindcss/cli -i ./resources/css/app.css -o ./public/assets/css/app.css --watch
```

Untuk hasil akhir, ganti `--watch` dengan `--minify`. Muat `/assets/css/app.css` di layout. Pilih satu lokasi output ini; `resources/css/output.css` tidak perlu menjadi output kedua. Hindari menyusun nama class secara dinamis seperti `bg-<?= $warna ?>-50`; gunakan pemetaan class utuh agar terdeteksi build. Play CDN cukup untuk percobaan, bukan hasil deployment. [Tailwind CLI](https://tailwindcss.com/docs/installation/tailwind-cli), [deteksi class](https://tailwindcss.com/docs/detecting-classes-in-source-files), [Play CDN](https://tailwindcss.com/docs/installation/play-cdn).

Tema gelap diaktifkan dengan `class="dark"` pada `<html>`; markup menggunakan pasangan `dark:*`. Preferensi tema boleh disimpan di localStorage; sesi dan data akademik tetap di server. Migrasikan CSS manual lama per komponen setelah utility penggantinya bekerja, agar aturan lama tidak menimpa Tailwind. [Dark mode](https://tailwindcss.com/docs/dark-mode).

**Cek selesai:** partial bersama memakai kombinasi class yang disepakati; tema terang/gelap terbaca; fokus keyboard terlihat; tidak ada overflow pada lebar 320, 390, 768, dan 1440px.

## Bagian 3 Membangun halaman guest dan kerangka navigasi

**Hasil:** alur landing → login/register → kembali ke landing bekerja sebelum database dihubungkan.

Buat landing page dengan logo PUTU, penjelasan singkat, contoh tampilan yang diberi label sebagai ilustrasi, fitur tugas/jadwal/pengumuman, dan CTA daftar. Login/register memakai layout auth bersama. Selesaikan HTML dan navigasinya dahulu; submit autentikasi mulai berfungsi pada Bagian 5.

Untuk router awal, gunakan query string supaya tidak bergantung pada rewrite Apache/Nginx:

| Halaman | URL awal | Akses setelah auth terpasang |
| --- | --- | --- |
| Landing | `/` | Guest; pengguna login diarahkan ke dashboard sesuai peran |
| Login | `/?page=login` | Guest |
| Registrasi | `/?page=register` | Guest |
| Kelas awal | `/?page=kelas` | Login tanpa kelas |
| Dashboard | `/?page=dashboard` | Mahasiswa dengan kelas |
| Tugas | `/?page=tugas` | Anggota kelas |
| Jadwal | `/?page=jadwal` | Anggota kelas |
| Pengumuman | `/?page=pengumuman` | Anggota kelas |
| Pengaturan | `/?page=pengaturan` | Login |
| Kelola akademik | `/?page=admin` | Admin pada kelas yang diizinkan |

Gunakan whitelist halaman dan action. Jangan memasukkan nilai `$_GET['page']` langsung ke `include`. Form memakai POST ke action yang diizinkan, misalnya `/?action=login`; router kemudian memanggil file tetap di `app/actions/`.

Navbar guest menampilkan Masuk/Daftar pada desktop. Pada layar di bawah 640px, pindahkan akses akun ke dock bawah. Navbar dan dock dashboard menampilkan navigasi sesuai peran setelah login; active state dibaca dari route PHP, bukan URL buatan JavaScript.

**Cek selesai:** semua link menuju page yang benar, kembali ke beranda bekerja, dock tidak menutupi konten terakhir, dan route asing tidak memasukkan file dari luar daftar.

## Bagian 4 Menyiapkan data dan koneksi PDO

**Hasil:** database dapat diisi data contoh dan seluruh query menggunakan satu koneksi PDO.

Gunakan MySQL dan `utf8mb4`. Simpan kredensial di konfigurasi lokal yang diabaikan Git atau environment server; jangan memasukkan password ke repositori. File `.env` tidak otomatis dibaca oleh PHP native: gunakan environment yang benar-benar disediakan server atau file config lokal di luar `public/`.

Model awal berikut menjaga satu kelas aktif per pengguna:

| Tabel | Isi utama dan batas penting |
| --- | --- |
| `users` | Nama, email unik, password hash, role, class_id nullable |
| `classes` | Nama, program, kode undangan unik |
| `courses` | Identitas mata kuliah, class_id |
| `tasks` | Mata kuliah, judul, deskripsi, deadline, tipe, class_id |
| `schedules` | Mata kuliah, hari, jam mulai/selesai, ruang, dosen, class_id |
| `announcements` | Judul, isi, kategori, penanda penting, penulis, class_id |
| `task_progress` | user_id, task_id, status; pasangan user_id/task_id unik |

Tetapkan foreign key, indeks email/kode, dan indeks class_id pada data akademik. Simpan hash pada kolom yang cukup panjang, misalnya 255 karakter. Buat data contoh dua kelas, mahasiswa dari masing-masing kelas, dan admin supaya isolasi akses bisa diuji. Jangan memakai akun demo React sebagai autentikasi produksi.

Semua input query dikirim sebagai parameter prepared statement; nama tabel/kolom dinamis harus berasal dari whitelist, bukan input mentah. [PDO::prepare](https://www.php.net/manual/en/pdo.prepare.php).

**Cek selesai:** data contoh bisa dibaca, koneksi gagal menghasilkan pesan ramah tanpa membocorkan password, email/kode ganda ditolak, dan tidak ada SQL dari input yang digabung langsung ke query.

## Bagian 5 Membuat autentikasi dan pembatasan akses

**Hasil:** akun benar-benar disimpan di MySQL dan session PHP menentukan akses.

Kerjakan registrasi → login → guard → logout. Registrasi hanya membuat mahasiswa; role admin tidak boleh dipilih dari form publik. Validasi nama/email/password di server. Gunakan `password_hash()` saat menyimpan dan `password_verify()` saat login, lalu regenerasi session ID setelah login berhasil. Jangan menyimpan password ke session, log, atau localStorage. [Password hash](https://www.php.net/manual/en/function.password-hash.php), [verifikasi password](https://www.php.net/manual/en/function.password-verify.php), [regenerasi session](https://www.php.net/manual/en/function.session-regenerate-id.php).

Sebelum menangani data aplikasi, tetapkan cookie session HttpOnly dan SameSite, Secure pada HTTPS, serta proteksi CSRF pada POST. Logout juga POST. Escape konten pengguna saat dirender ke HTML. Terapkan pembatasan percobaan login sebelum aplikasi dibuka untuk umum.

Guard dijalankan untuk halaman dan action: guest ke login, mahasiswa tidak bisa memanggil action admin, dan role selalu dibaca dari identitas server. Redirect setelah login harus ditentukan server atau divalidasi sebagai URL internal.

**Cek selesai:** email ganda/kata sandi salah ditolak, session berubah saat login, refresh mempertahankan login, logout mengakhiri sesi, POST tanpa CSRF ditolak, dan akses URL admin oleh mahasiswa menghasilkan penolakan.

## Bagian 6 Membuat alur kelas

**Hasil:** mahasiswa baru mulai kosong, lalu membuat atau bergabung ke kelas dengan kode undangan.

Pengguna tanpa kelas masuk ke page kelas, bukan melihat data kelas demo. Validasi kode di server. Buat kode acak yang unik dan simpan keanggotaan setelah operasi berhasil. Pembuatan kelas dan pemasangan class_id ke pengguna dilakukan dalam satu transaksi. Menjadi pembuat kelas tidak otomatis memberi hak admin; hak kelola akademik tetap diberikan melalui peran yang diatur server.

Semua pembacaan dan perubahan data memeriksa class_id dari sesi pengguna. ID record dari URL/form bukan bukti kepemilikan; periksa kelas record dan kelas mata kuliah saat membuat relasi. Terapkan aturan yang sama pada admin sesuai kelas yang diizinkan.

**Cek selesai:** akun baru melihat keadaan kosong; kode salah tidak mengubah akun; mahasiswa kelas A tidak dapat membaca/mengubah tugas kelas B dengan mengganti ID; kegagalan transaksi tidak meninggalkan kelas atau keanggotaan setengah jadi.

## Bagian 7 Membangun fitur mahasiswa

**Hasil:** satu alur tugas berfungsi lengkap sebelum menambah layar lain.

Urutannya: daftar/detail tugas → ubah progres → dashboard → jadwal → pengumuman → pengaturan profil/tema. Dashboard mengambil ringkasan dari data yang sama dengan daftar tugas. Status progres hanya `todo`, `doing`, atau `done`, divalidasi di server dan tersimpan per pengguna.

Tambahkan filter tugas berdasarkan kata kunci/status dan urutan deadline. Jadwal diurutkan berdasarkan hari dan jam. Pengumuman mempunyai daftar, pencarian, serta detail. Profil hanya mengizinkan field yang memang boleh diubah mahasiswa. Gunakan zona Asia/Jakarta untuk tampilan tanggal/jam dan tetapkan konvensi penyimpanan timestamp secara konsisten.

Setiap layar memiliki state kosong, error, dan sukses. Setelah status berubah, redirect kembali ke daftar yang relevan dengan pesan hasil aksi. Validasi tidak boleh hilang ketika JavaScript dimatikan.

**Cek selesai:** progres pengguna A tidak mengubah progres pengguna B; ringkasan dashboard cocok dengan daftar; jadwal tersortir; data kosong terbaca; teks seperti `<script>` tampil sebagai teks dan tidak berjalan.

## Bagian 8 Membangun panel admin

**Hasil:** admin mengelola tugas, jadwal, dan pengumuman pada kelasnya dengan validasi dan konfirmasi hapus.

Pakai layout, field, badge, serta panel yang sama. Mulai CRUD tugas, kemudian jadwal dan pengumuman. Validasi judul/deskripsi, deadline yang dapat diparsing, mata kuliah sekelas, dan jam selesai setelah jam mulai. Pemeriksaan admin, keanggotaan kelas, dan CSRF terjadi pada setiap action, bukan hanya saat menampilkan tombol.

Sebelum hapus tampilkan nama record dan akibatnya. Tetapkan perilaku progres saat tugas dihapus, melalui foreign key atau transaksi yang konsisten. Tangani kegagalan tanpa menampilkan pesan berhasil palsu.

**Cek selesai:** mahasiswa yang mengirim POST langsung tetap ditolak; record kelas lain tidak bisa dimutasi; perubahan admin muncul di tampilan mahasiswa; membatalkan hapus mempertahankan data; penghapusan tidak meninggalkan progres yatim.

## Bagian 9 Menguji dan menyiapkan deployment

**Hasil:** alur penting lulus dan aplikasi bisa dipasang dengan instruksi yang dapat diulang.

Buat tes kecil untuk auth, CSRF, role, isolasi kelas, dan progres per pengguna. Jalankan PHP syntax check untuk seluruh file PHP, build CSS minified, lalu uji manual alur berikut:

1. Guest → landing → daftar → kelas kosong → gabung kelas.
2. Mahasiswa → tugas → ubah progres → refresh → logout → login lagi.
3. Admin → tambah/edit/hapus tugas → hasilnya terlihat pada mahasiswa.
4. Akun kelas B tidak melihat atau mengubah data kelas A melalui URL maupun POST.
5. Keyboard dapat menjangkau link/form/dialog; fokus terlihat dan error form terhubung ke field.
6. Lebar 320, 390, 768, 1024, dan 1440px terbaca dalam tema terang/gelap; dock memperhitungkan safe area.

Siapkan README berisi versi yang dipakai, setup config lokal, pembuatan database, cara memuat data contoh, build CSS, dan cara menjalankan. Deployment membutuhkan PHP dan MySQL, HTTPS, document root `public/`, error detail dimatikan di layar, log server, dan backup database. Netlify static hosting dari frontend React tidak menjalankan PHP. Uji restore backup sebelum menyebut data dapat dipulihkan.

## Cara mencicil pekerjaan

Kerjakan per bagian, bukan semua layar sekaligus. Buat commit kecil setelah hasil dan pengujian bagian tersebut selesai. Untuk sesi pertama, cukup tuntaskan Bagian 1 sampai Bagian 3: router aman, utility/partial Tailwind, layout, landing, dan halaman auth yang bisa dinavigasi.

Contoh instruksi sesi development:

> Kerjakan Bagian 1 pada panduan ini di ~/Documents/Tugas/PBL/code. Pertahankan hasil kerja yang ada. Buat satu front controller di public/index.php dengan whitelist halaman/action, gunakan public sebagai document root, dan cek halaman utama, 404, serta penolakan akses config. Jangan lanjut ke database dulu.

Upload tugas, notifikasi email/push, reset password melalui email, realtime, multi-kelas per pengguna, dan instalasi PWA berada setelah versi inti selesai. Tautan untuk fitur yang belum tersedia harus menjelaskan kondisinya, bukan menjadi tombol mati.
