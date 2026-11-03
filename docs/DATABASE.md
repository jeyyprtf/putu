# Koneksi Database (Supabase PostgreSQL)

Panduan paling singkat untuk menghubungkan project PHP native ini ke Supabase.

## Gambaran alur

```
Halaman PHP  ->  db()  ->  PDO  ->  Supabase (PostgreSQL)
                  ^
                  +-- baca kredensial dari database.local.php / env var
```

File yang terlibat:

| File | Fungsi |
|---|---|
| `app/config/database.php` | Helper `db()` + `db_all()`, `db_one()`, `db_exec()` |
| `app/config/database.local.php` | Kredensial asli kamu (**tidak di-commit**) |
| `app/config/database.local.example.php` | Contoh format kredensial |
| `.env.example` | Contoh nilai env var |
| `tests/db.php` | Script cek koneksi |

## Langkah setup

### 1. Ambil connection string dari Supabase

Buka **Supabase Dashboard -> Project Settings -> Database -> Connection string**,
pilih tab **URI**, lalu salin bagian host/user/port.

Supabase punya 2 jenis koneksi:

| Mode | Port | Kapan dipakai |
|---|---|---|
| **Session pooler** | `5432` | **Disarankan untuk project ini.** Satu request PHP = satu sesi utuh; prepared statement & `SET` berfungsi |
| Transaction pooler | `6543` | Untuk serverless/edge function yang membuka banyak koneksi super pendek |
| Direct connection | `5432` | Untuk migrasi/pg_dump. Butuh IPv6 atau IPv4 add-on |

Untuk web PHP biasa (apache/nginx + php-fpm): **pakai session pooler (5432)**.

> Catatan: dengan pooler, user berbentuk `postgres.<project-ref>`, bukan `postgres`.

### Kenapa session, bukan transaction?

Transaction pooler mengembalikan koneksi ke pool **setiap kali satu transaksi selesai**,
sehingga state sesi hilang. Yang jadi masalah:

- **Prepared statement tidak didukung** — padahal `db_all()` memakai `prepare()`
  dengan `ATTR_EMULATE_PREPARES = false`.
- **`SET statement_timeout` hilang** — jadi tidak berefek.

Session pooler mempertahankan sesi selama request berjalan, cocok untuk PHP-FPM
yang prosesnya hidup terus.


### 2. Isi kredensial

```bash
cp app/config/database.local.example.php app/config/database.local.php
```

Lalu edit `app/config/database.local.php`:

```php
return [
    'DB_HOST' => 'aws-0-ap-southeast-1.pooler.supabase.com',
    'DB_PORT' => '5432',
    'DB_NAME' => 'postgres',
    'DB_USER' => 'postgres.abcdefghijklmnop',
    'DB_PASS' => 'password-database-kamu',
];
```

Password ini **bukan** password login Supabase — ambil di **Project Settings -> Database -> Reset database password**.

### 3. Tes koneksi

```bash
php tests/db.php
```

Harusnya muncul `OK - Terhubung ke database` + versi PostgreSQL dan jumlah tabel.

### 4. Pakai di halaman

`bootstrap.php` sudah otomatis memuat `database.php`, jadi cukup panggil helper-nya:

```php
<?php
require_once __DIR__ . '/../bootstrap.php';

// SELECT banyak baris
$kelas = db_all('SELECT id, nama, prodi FROM kelas ORDER BY nama');

// SELECT satu baris
$user = db_one('SELECT * FROM users WHERE id = :id', ['id' => $userId]);

// INSERT / UPDATE / DELETE
db_exec(
    'INSERT INTO kelas (nama, prodi) VALUES (:nama, :prodi)',
    ['nama' => $nama, 'prodi' => $prodi]
);
```

Di HTML, escape selalu dengan `e()`:

```php
<?php foreach ($kelas as $k): ?>
    <option value="<?= e($k['id']) ?>"><?= e($k['nama']) ?></option>
<?php endforeach; ?>
```

## Aturan penting (best practice)

1. **Selalu pakai placeholder** (`:nama`, `?`), **jangan** gabung string ke SQL.
   Ini mencegah SQL injection. Contoh salah:
   `db()->query("SELECT * FROM users WHERE email = '$email'")`
2. **Jangan commit kredensial.** `database.local.php` dan `.env` sudah ada di `.gitignore`.
4. **Pakai pooler, bukan direct connection `db.<ref>.supabase.co`.** Web PHP membuka
   koneksi tiap request; pooler yang mengatur antreannya. Direct connection cepat
   kehabisan slot. Untuk project ini pakai **session pooler (`5432`)**.
4. **Jangan pakai `PDO::ATTR_PERSISTENT`** dengan pooler — bisa bikin koneksi nyangkut.
5. **Tangkap error dengan rapi.** `db()` melempar `RuntimeException`; jangan tampilkan
   pesan mentah ke user:
   ```php
   try {
       $rows = db_all('SELECT ...');
   } catch (Throwable $e) {
       error_log($e->getMessage());
       // tampilkan pesan ramah ke user
   }
   ```
6. **`sslmode=require` wajib** untuk Supabase — sudah diset di DSN.
7. **Enable RLS atau pakai service key dengan hati-hati.** Untuk PHP native (bukan
   supabase-js), kamu terhubung langsung sebagai Postgres user, jadi RLS tidak berlaku.
   Artinya **otorisasi harus dicek di kode PHP**, bukan mengandalkan RLS Supabase.

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| `could not find driver` | Ekstensi `pdo_pgsql` belum aktif. Di Ubuntu: `sudo apt install php-pgsql` lalu restart PHP. Cek dengan `php -m \| grep pgsql` |
| `password authentication failed` | Password salah, atau user pooler harus `postgres.<project-ref>` |
| `Tenant or user not found` | Format `DB_USER` salah (kurang `.<project-ref>`) atau salah host pooler |
| `Connection timed out` | Host/port salah, atau IP kamu diblokir. Pastikan port `5432` dan host dari Connect dialog |
| `prepared statement "S_1" does not exist` / `bind message supplies 0 parameters` | Kamu memakai transaction pooler (`6543`). Ganti port ke `5432` |
| `SSL required` | Jangan hapus `sslmode=require` dari DSN |
| `too many connections` | Terlalu banyak koneksi terbuka. Pastikan pakai pooler (bukan direct `db.<ref>.supabase.co`), dan koneksi hanya dibuat sekali per request |
| Error saat `php tests/db.php`: `Konfigurasi database belum lengkap` | `database.local.php` belum dibuat / belum terisi |

## Kalau nanti pindah framework

Langkah yang sama tetap dipakai: satu file konfigurasi koneksi, PDO untuk query,
kredensial dari env. Yang berubah hanya lokasi file konfigurasinya (misal
`config/database.php` di Laravel, atau `.env` + Doctrine di Symfony).
