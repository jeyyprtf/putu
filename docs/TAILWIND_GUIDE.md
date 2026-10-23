# Panduan Tailwind untuk PUTU

> Status frontend dan acuan visual terbaru: [FRONTEND.md](FRONTEND.md). Panduan di bawah adalah rancangan awal; implementasi saat ini mengikuti palet stone–lime dan font Inter sesuai landing/login/register.

Gunakan utility bawaan Tailwind CSS v4 pada markup PHP. Palet PUTU memakai **stone** untuk dasar off-white dan **emerald** untuk aksen hijau. Warna sedikit bergeser dari rancangan sebelumnya agar tim dapat memakai nama warna bawaan tanpa membuat tema warna sendiri. [Palet bawaan Tailwind](https://tailwindcss.com/docs/colors).

Snippet berikut adalah acuan tampilan. Hubungkan form ke handler, session, CSRF, dan validasi server saat mengerjakan bagian backend di [panduan development](DEVELOPMENT_GUIDE.md). Jangan menyalin data dari pengguna ke HTML tanpa escaping.

## Warna yang disepakati

Pakai pasangan class ini secara konsisten:

| Penggunaan | Terang | Gelap |
| --- | --- | --- |
| Latar halaman | `bg-stone-50` | `dark:bg-stone-950` |
| Panel, kartu, input | `bg-white` | `dark:bg-stone-900` |
| Teks utama | `text-stone-900` | `dark:text-stone-100` |
| Teks pendamping | `text-stone-600` | `dark:text-stone-400` |
| Border panel | `border-stone-200` | `dark:border-stone-800` |
| Border input | `border-stone-300` | `dark:border-stone-600` |
| Aksen lembut | `bg-emerald-50` | `dark:bg-emerald-950` |
| Teks/link aksen | `text-emerald-800` | `dark:text-emerald-300` |
| Tombol utama | `bg-emerald-900 text-white` | `dark:bg-emerald-200 dark:text-stone-950` |
| Hover tombol | `hover:bg-emerald-950` | `dark:hover:bg-emerald-300` |
| Error | `bg-red-50 text-red-700` | `dark:bg-red-950 dark:text-red-300` |
| Peringatan | `bg-amber-50 text-amber-800` | `dark:bg-amber-950 dark:text-amber-300` |
| Informasi | `bg-blue-50 text-blue-700` | `dark:bg-blue-950 dark:text-blue-300` |

Tidak perlu membuat `bg-primary`, mengganti warna bawaan `emerald-900`, atau menulis warna seperti `bg-[#234E3C]`. Saat membutuhkan peran yang sama, salin kombinasi class yang sama. Simpan markup berulang dalam partial PHP.

## Font dan ukuran teks

Pakai `font-sans antialiased` pada `<body>`. `font-sans` memakai font sistem yang tersedia di perangkat; tidak otomatis memuat Inter. Inter di Figma hanya menjadi representasi sans-serif saat mendesain. Tim tidak perlu mengunduh font untuk versi awal. [Font family Tailwind](https://tailwindcss.com/docs/font-family).

| Elemen | Class |
| --- | --- |
| Judul hero | `text-4xl font-medium leading-tight tracking-tight sm:text-5xl lg:text-6xl` |
| Judul halaman | `text-3xl font-semibold tracking-tight` |
| Heading bagian | `text-xl font-semibold` |
| Paragraf utama | `text-base leading-7` |
| Isi kartu/form | `text-sm leading-6` |
| Tombol | `text-sm font-semibold` |
| Label field | `text-xs font-semibold` |
| Metadata tambahan | `text-xs leading-4 text-stone-600 dark:text-stone-400` |

Gunakan `font-medium` atau `font-semibold` untuk hierarki. Hindari ukuran custom seperti `text-[13px]` jika `text-xs` atau `text-sm` sudah cukup. [Skala ukuran Tailwind](https://tailwindcss.com/docs/font-size).

## Spacing dan bentuk

Dengan skala default dan ukuran root 16px:

| Kebutuhan | Class | Nilai acuan |
| --- | --- | --- |
| Gap kecil | `gap-2`, `gap-3` | 8px, 12px |
| Gap form | `space-y-5` | 20px |
| Gap panel | `gap-6` | 24px |
| Padding panel | `p-5 sm:p-6` | 20–24px |
| Padding halaman | `px-6 lg:px-8` | 24–32px |
| Jarak bagian landing | `py-12 lg:py-16` | 48–64px |
| Tombol/input | `min-h-11 rounded-2xl` | tinggi minimum 44px, radius 16px |
| Panel/kartu | `rounded-3xl` | radius 24px |
| Dialog | `rounded-4xl` | radius 32px |
| Badge/pill | `rounded-full` | sudut penuh |
| Container landing | `mx-auto max-w-6xl` | maksimum 1152px |
| Container dashboard | `mx-auto max-w-7xl` | maksimum 1280px |
| Sidebar desktop | `w-60` | 240px |

Awali layout satu kolom, gunakan `md:` mulai 768px dan `lg:` mulai 1024px. `sm:` mulai 640px, dipakai untuk memindahkan akses akun dari dock ke navbar.

## Setup build dan tema gelap

Saat mulai memasang Tailwind di project PHP:

```bash
cd ~/Documents/Tugas/PBL/code
npm install -D tailwindcss @tailwindcss/cli
```

Isi minimal `resources/css/app.css`:

```css
@import "tailwindcss";
@source "../../app";
@source "../../public";
@custom-variant dark (&:where(.dark, .dark *));
```

Kemudian build dari root project:

```bash
npx @tailwindcss/cli -i ./resources/css/app.css -o ./public/assets/css/app.css --watch
```

Untuk hasil deployment, ganti `--watch` dengan `--minify`. Muat `/assets/css/app.css` melalui `<link rel="stylesheet">`. [Tailwind CLI](https://tailwindcss.com/docs/installation/tailwind-cli).

Dengan konfigurasi di atas, tambahkan `class="dark"` pada `<html>` untuk mengaktifkan tema gelap; hapus class itu untuk tema terang. Preferensi tema boleh disimpan di localStorage, sedangkan sesi login dan data akademik tetap di server. Jika toggle belum dibuat, kedua tema bisa diperiksa dengan mengubah class HTML tersebut. [Manual dark mode](https://tailwindcss.com/docs/dark-mode).

Di folder saat ini sudah ada CSS manual. Saat migrasi, hapus aturan manual lama **per komponen setelah penggantinya bekerja**, supaya aturan seperti `.login-card` atau `body { background-color: ... }` tidak menimpa utility Tailwind. Snippet CSS minimal di atas adalah target setup, bukan instruksi menghapus semua pekerjaan sekaligus.

## Snippet layout halaman

```html
<body class="min-h-dvh bg-stone-50 font-sans text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
  <main class="mx-auto max-w-6xl px-6 py-12 lg:px-8 lg:py-16">
    <h1 class="text-3xl font-semibold tracking-tight">Ringkasan</h1>
    <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-400">
      Yuk, buat hari ini sedikit lebih teratur.
    </p>

    <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <!-- Isi dengan partial kartu. -->
    </div>
  </main>
</body>
```

## Snippet tombol dan link

Tombol submit utama:

```html
<button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-semibold text-white transition-colors duration-200 hover:bg-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 motion-reduce:transition-none dark:bg-emerald-200 dark:text-stone-950 dark:hover:bg-emerald-300 dark:focus-visible:outline-emerald-300">
  Masuk ke PUTU
</button>
```

Link sekunder untuk pindah halaman:

```html
<a href="/?page=login" class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-900 hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-stone-800 dark:bg-stone-900 dark:text-stone-100 dark:hover:bg-stone-800 dark:focus-visible:outline-emerald-300">
  Sudah punya akun? Masuk
</a>
```

Saat submit diproses, gunakan `disabled` dan ganti label menjadi `Sebentar…`. Untuk kegagalan, aktifkan kembali tombol dan tampilkan error; perubahan data tetap divalidasi oleh server.

## Snippet field dan error

Field normal menggunakan label permanen:

```html
<div class="space-y-2">
  <label for="email" class="block text-xs font-semibold">Email</label>
  <input id="email" name="email" type="email" required autocomplete="email" placeholder="nama@email.com" aria-describedby="email-help" class="min-h-11 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-900 placeholder:text-stone-500 focus-visible:border-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-stone-600 dark:bg-stone-900 dark:text-stone-100 dark:placeholder:text-stone-400 dark:focus-visible:border-emerald-300 dark:focus-visible:outline-emerald-300">
  <p id="email-help" class="text-xs leading-4 text-stone-600 dark:text-stone-400">Gunakan alamat email kamu.</p>
</div>
```

Saat email tidak valid, tambahkan `aria-invalid="true"`, ubah `aria-describedby` ke `email-error`, **ganti** class border/fokus emerald dengan varian red yang setara, dan tampilkan:

```html
<p id="email-error" role="alert" class="text-xs leading-4 text-red-700 dark:text-red-300">
  Masukkan alamat email yang valid.
</p>
```

Gunakan `border-red-600 focus-visible:border-red-600 focus-visible:outline-red-600 dark:border-red-300 dark:focus-visible:border-red-300 dark:focus-visible:outline-red-300`. Jangan menyisakan dua class border/fokus yang saling bertentangan pada state error.

## Snippet kartu tugas dan badge

```html
<article class="rounded-3xl border border-stone-200 bg-white p-5 sm:p-6 dark:border-stone-800 dark:bg-stone-900">
  <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">Interaksi Manusia & Komputer</p>
  <h2 class="mt-3 text-xl font-semibold">Wireframe aplikasi</h2>
  <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-400">Buat wireframe untuk alur utama aplikasi.</p>
  <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">Dikerjakan</span>
    <span class="text-xs text-stone-600 dark:text-stone-400">Contoh tampilan</span>
  </div>
</article>
```

Untuk badge lain, pertahankan `rounded-full px-3 py-1 text-xs font-semibold` lalu gunakan:

| Status | Warna |
| --- | --- |
| Belum mulai/deadline dekat | `bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300` |
| Dikerjakan | `bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300` |
| Selesai | `bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300` |
| Gagal | `bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300` |

Warna selalu disertai teks status. Progres selesai tidak berarti berkas tugas sudah dikumpulkan.

## Snippet navbar guest dan dock mobile

Navbar untuk logo dan akses akun desktop:

```html
<header class="sticky top-0 z-30 border-b border-stone-200 bg-stone-50/95 backdrop-blur-xl dark:border-stone-800 dark:bg-stone-950/95">
  <nav aria-label="Navigasi guest" class="mx-auto flex min-h-20 max-w-6xl items-center justify-between gap-4 px-6 lg:px-8">
    <a href="/" aria-label="PUTU beranda" class="text-2xl font-bold tracking-tight text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:text-emerald-200 dark:focus-visible:outline-emerald-300">putu.</a>
    <div class="hidden items-center gap-3 sm:flex">
      <a href="/?page=login" class="inline-flex min-h-11 items-center rounded-2xl px-4 text-sm font-semibold hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:hover:bg-stone-800 dark:focus-visible:outline-emerald-300">Masuk</a>
      <a href="/?page=register" class="inline-flex min-h-11 items-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-emerald-200 dark:text-stone-950 dark:hover:bg-emerald-300 dark:focus-visible:outline-emerald-300">Daftar</a>
    </div>
  </nav>
</header>
```

Dock guest di bawah 640px:

```html
<nav aria-label="Akses akun" class="fixed inset-x-4 bottom-4 z-40 mx-auto flex max-w-sm gap-2 rounded-3xl border border-stone-200 bg-white/95 p-2 shadow-lg backdrop-blur-xl sm:hidden dark:border-stone-800 dark:bg-stone-900/95" style="bottom: calc(1rem + env(safe-area-inset-bottom));">
  <a href="/?page=login" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-2xl text-sm font-semibold hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:hover:bg-stone-800 dark:focus-visible:outline-emerald-300">Masuk</a>
  <a href="/?page=register" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-2xl bg-emerald-900 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:bg-emerald-200 dark:text-stone-950 dark:hover:bg-emerald-300 dark:focus-visible:outline-emerald-300">Daftar</a>
</nav>
```

Saat memakai dock, letakkan navbar, main, footer, dan dock di dalam wrapper berikut agar konten terakhir tetap bisa dijangkau:

```html
<div class="min-h-dvh pb-28 sm:pb-0">
  <!-- Navbar, main, footer, lalu dock. -->
</div>
```

Nilai safe area pada posisi dock adalah kebutuhan perangkat, bukan warna atau ukuran desain baru. Untuk layar dengan inset bawah besar, beri tambahan ruang pada footer mobile menggunakan `padding-bottom: env(safe-area-inset-bottom)` melalui CSS kecil atau wrapper terpisah.

## Aturan kerja tim

1. Salin class dari snippet, lalu pindahkan markup berulang ke `app/components/`. Update partial bersama untuk mengubah semua pemakainya.
2. Gunakan warna, ukuran, dan radius dari tabel. Hindari arbitrary value jika utility bawaan mencukupi.
3. Jangan mengganti nilai bawaan `--color-emerald-*`; nama class harus tetap berarti warna Tailwind yang sama untuk semua anggota tim.
4. Class di PHP harus berupa string lengkap. Pilih seluruh daftar class lewat map status; jangan membentuk `bg-<?= $warna ?>-50` karena build tidak dapat menyimpulkan nama class-nya. [Deteksi class](https://tailwindcss.com/docs/detecting-classes-in-source-files).
5. Uji terang/gelap, keyboard, dan lebar 320/390/768/1024/1440px setelah menyelesaikan satu halaman. Pertahankan label, fokus, dan penjelasan error saat merapikan class.

## Acuan Figma

[PUTU Web Design Guidelines](https://www.figma.com/design/lfbSOjtgyALIyU41ta1h9x/PUTU---Juan-Workspace?node-id=2115-395) menggunakan palet bawaan yang sama. Label swatch menampilkan utility Tailwind; Inter di canvas adalah representasi font-sans, sehingga bentuk huruf web dapat berbeda sesuai sistem operasi.
