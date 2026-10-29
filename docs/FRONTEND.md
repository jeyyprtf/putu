# Frontend PUTU

Struktur frontend diperbarui pada 26 Oktober 2026. Implementasi memakai PHP native, Tailwind Browser v4 yang sudah dipakai project, dan JavaScript biasa. Jalankan dari root repositori:

```bash
php -S 127.0.0.1:8000 -t .
```

Buka `http://127.0.0.1:8000/`. Tiap halaman dapat dibuka langsung, misalnya `http://127.0.0.1:8000/app/pages/dashboard.php`. URL lama `index.php?page=dashboard` juga tetap bekerja. Halaman tidak dikenal melalui index menghasilkan 404; nama file dibatasi dengan daftar halaman tetap.

## File yang diedit tim

| Kebutuhan | Buka file |
| --- | --- |
| Landing | `app/pages/home.php` |
| Login | `app/pages/login.php` |
| Register | `app/pages/register.php` |
| Dashboard | `app/pages/dashboard.php` |
| Tugas | `app/pages/tugas.php` |
| Jadwal | `app/pages/jadwal.php` |
| Pengumuman | `app/pages/pengumuman.php` |
| Profil & pengaturan | `app/pages/pengaturan.php` |
| Navbar atas / sidebar / dock | `app/components/navbar.php`, `sidebar.php`, `dock.php` |
| Kartu tugas berulang | `app/components/task-card.php` |
| Foto kiri login/register | `app/components/auth-visual.php` |
| Warna dan gaya kontrol | `resources/css/app.css` |
| Interaksi browser | `public/assets/js/app.js` |
| Data contoh dan hitungan | `app/demo.php` |

Tiap file halaman berisi dokumen HTML lengkap: `<html>`, `<head>`, `<body>`, dan isi halaman. Tidak ada folder `layouts` yang membungkus halaman secara tersembunyi. Dashboard berisi progres singkat, ringkasan tugas aktif (mendesak ≤2 hari atau terlambat, sisanya santai), jumlah jadwal hari ini, agenda, dan maksimal tiga deadline terdekat. Semua tetap berupa blok HTML biasa agar mudah diedit.

## PHP yang perlu dipahami saat mengedit tampilan

Bagian atas halaman hanya menentukan nama halaman, judul tab, dan memuat persiapan bersama:

```php
<?php
$page = 'dashboard';
$title = 'Ringkasan';
require_once __DIR__ . '/../bootstrap.php';
?>
```

Setelah itu, tulis HTML seperti biasa. Tiga bentuk PHP yang sering terlihat:

```php
<?php require __DIR__ . '/../components/navbar.php'; ?>
```

`require` menyisipkan isi file komponen. `__DIR__` adalah folder file yang sedang dibaca; `../` naik satu folder. Navbar cukup diedit di satu file untuk semua halaman.

```php
<h2><?= e($task['title']) ?></h2>
```

`<?= ... ?>` menampilkan nilai ke HTML. `$task['title']` mirip `task.title` di JavaScript. `e()` memastikan isi teks tidak dianggap sebagai kode HTML; pertahankan saat menampilkan data.

```php
<?php foreach ($tasks as $task): ?>
    <?php require __DIR__ . '/../components/task-card.php'; ?>
<?php endforeach; ?>
```

`foreach` mengulang satu kartu untuk setiap tugas, seperti loop pada array JavaScript. Data dan hitungan sudah disiapkan di `app/demo.php`, sehingga tidak perlu menulis perhitungan di tengah HTML.

`url('tugas')` membuat alamat halaman dan `asset('resources/css/app.css')` membuat alamat aset. Keduanya menjaga link tetap benar saat dibuka langsung atau lewat index, termasuk ketika project berada di subfolder.

## Acuan desain

Landing, login, dan register menjadi acuan terbaru, menggantikan palet emerald pada panduan awal: dasar **stone-950**, panel **stone-900**, border **stone-800**, teks **stone-100/400**, aksi utama **lime-500**, highlight **lime-300**. Font **Inter**. Tema gelap menjadi fokus tahap ini.

Utility Tailwind tetap ditulis di markup. Gaya kontrol bersama (`.field`, `.button`, `.panel`, `.filter-tab`, `.nav-link`) ada di `resources/css/app.css` dan memakai variabel palet Tailwind. Komponen bersama berada di `app/components/`. Metadata, font, CSS, dan script bersama dimuat melalui `components/head.php`; setiap halaman tetap memiliki struktur dokumennya sendiri.

Login/register mengikuti komposisi dua kolom referensi Figma node `20:19`: foto kiri, form kanan, menjadi satu kolom di mobile. Foto placeholder disimpan lokal; kredit dan sumber ada di `public/assets/images/SOURCES.md`.

## Batas pratinjau

- Data contoh bersama berada di `app/demo.php`. Deadline mengikuti tanggal server Asia/Jakarta agar contoh tetap relevan.
- Status tugas memakai `sessionStorage` khusus pratinjau per tab dan tanggal; tidak tersimpan ke akun. Dashboard dan halaman tugas membaca status yang sama.
- Form login/register memvalidasi format lewat browser, tetapi belum melakukan autentikasi atau menyimpan data. Tombol pratinjau terpisah tersedia untuk melihat halaman mahasiswa.
- Profil & pengaturan dapat dibuka dari avatar navbar atau sidebar. Data profil contoh berada di `$profile` pada `app/demo.php`, dipakai navbar, dashboard, dan form. Form memakai POST dengan field `name`/`email`; Batal memakai reset bawaan browser. Simpan dinonaktifkan sampai handler backend, autentikasi, validasi server, dan CSRF tersedia. Tidak ada penyimpanan profil di browser.
- Pembuatan/gabung kelas, perubahan password, admin, autentikasi, dan database adalah tahap berikutnya.
- Tailwind CDN dan Google Fonts memerlukan koneksi internet. Sebelum deployment produksi, siapkan build CSS lokal dan document root publik sesuai panduan backend; server PHP bawaan ini hanya untuk pengembangan.

## Pemeriksaan

Dengan server PHP berjalan:

```bash
php tests/paths.php
python3 tests/smoke.py
```

Tes interaksi memakai Node 22+ dan Chrome melalui protokol DevTools, tanpa dependensi npm. Jalankan Chrome dengan profil sementara dan port debug hanya untuk pengujian lokal:

```bash
google-chrome --headless --remote-debugging-port=9334 --user-data-dir=/tmp/putu-frontend-check about:blank
node tests/frontend.mjs
```

`BASE_URL` dan `CDP_URL` dapat diatur jika port berbeda. Tes memeriksa filter, urutan, detail, sinkronisasi progres antara halaman langsung dan URL lama, jadwal, kontrol auth, dan lebar layar 320/390/768/1024/1440 px. Skrip membuka dan menutup tab tes sendiri.

Checkpoint sebelum perubahan: commit `bdf668b` pada `origin/master`. Perubahan frontend dikerjakan pada branch `codex/frontend-refresh` agar checkpoint tetap mudah diakses.

Checkpoint sebelum penyederhanaan struktur: `f09df12`, termasuk warna indikator dan angka mobile 48 px.
