# Frontend PUTU

Implementasi frontend per 23 Oktober 2026 memakai PHP native, Tailwind Browser v4 yang sudah dipakai project, dan JavaScript biasa. Jalankan dari root repositori:

```bash
php -S 127.0.0.1:8000 -t .
```

Buka `http://127.0.0.1:8000/`. Semua halaman memakai `index.php?page=...`: `login`, `register`, `dashboard`, `tugas`, `jadwal`, dan `pengumuman`. URL langsung halaman lama diarahkan ke router; halaman tidak dikenal menghasilkan 404. Router memakai daftar halaman tetap.

## Acuan desain

Landing, login, dan register menjadi acuan terbaru, menggantikan palet emerald pada panduan awal: dasar **stone-950**, panel **stone-900**, border **stone-800**, teks **stone-100/400**, aksi utama **lime-500**, highlight **lime-300**. Font **Inter**. Tema gelap menjadi fokus tahap ini.

Utility Tailwind tetap ditulis di markup. Gaya kontrol bersama (`.field`, `.button`, `.panel`, `.filter-tab`, `.nav-link`) ada di `resources/css/app.css` dan memakai variabel palet Tailwind. Navigasi bersama berada di `app/components/`; wrapper dokumen berada di `app/layouts/`. Halaman konten tidak memiliki `<html>` atau navbar sendiri.

Login/register mengikuti komposisi dua kolom referensi Figma node `20:19`: foto kiri, form kanan, menjadi satu kolom di mobile. Foto placeholder disimpan lokal; kredit dan sumber ada di `public/assets/images/SOURCES.md`.

## Batas pratinjau

- Data contoh bersama berada di `app/demo.php`. Deadline mengikuti tanggal server Asia/Jakarta agar contoh tetap relevan.
- Status tugas memakai `sessionStorage` khusus pratinjau per tab dan tanggal; tidak tersimpan ke akun. Dashboard dan halaman tugas membaca status yang sama.
- Form login/register memvalidasi format lewat browser, tetapi belum melakukan autentikasi atau menyimpan data. Tombol pratinjau terpisah tersedia untuk melihat halaman mahasiswa.
- Pembuatan/gabung kelas, profil, admin, autentikasi, dan database adalah tahap berikutnya.
- Tailwind CDN dan Google Fonts memerlukan koneksi internet. Sebelum deployment produksi, siapkan build CSS lokal dan document root publik sesuai panduan backend; server PHP bawaan ini hanya untuk pengembangan.

## Pemeriksaan

Dengan server PHP berjalan:

```bash
python3 tests/smoke.py
```

Tes interaksi memakai Node 22+ dan Chrome melalui protokol DevTools, tanpa dependensi npm. Jalankan Chrome dengan profil sementara dan port debug hanya untuk pengujian lokal:

```bash
google-chrome --headless --remote-debugging-port=9334 --user-data-dir=/tmp/putu-frontend-check about:blank
node tests/frontend.mjs
```

`BASE_URL` dan `CDP_URL` dapat diatur jika port berbeda. Tes memeriksa filter, urutan, detail, sinkronisasi progres pratinjau, jadwal, kontrol auth, dan lebar layar 320/390/768/1024/1440 px. Skrip membuka dan menutup tab tes sendiri.

Checkpoint sebelum perubahan: commit `bdf668b` pada `origin/master`. Perubahan frontend dikerjakan pada branch `codex/frontend-refresh` agar checkpoint tetap mudah diakses.
