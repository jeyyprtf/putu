PUTU — struktur frontend saat ini

```text
code/
├── index.php                 # Beranda + dukungan URL lama ?page=...
├── app/
│   ├── pages/                # Tiap file berisi HTML lengkap
│   │   ├── home.php
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── dashboard.php
│   │   ├── tugas.php
│   │   ├── jadwal.php
│   │   ├── pengumuman.php
│   │   ├── not-found.php
│   │   └── admin/tugas.php   # Belum dikerjakan
│   ├── components/           # Potongan HTML yang dipakai bersama
│   │   ├── head.php
│   │   ├── fonts.php
│   │   ├── navbar.php
│   │   ├── sidebar.php
│   │   ├── dock.php
│   │   ├── footer.php
│   │   ├── auth-visual.php
│   │   └── task-card.php
│   ├── bootstrap.php         # Memuat helpers dan data contoh
│   ├── demo.php              # Data contoh + hitungan untuk tampilan
│   ├── helpers.php           # Escape teks, URL, tanggal, ikon
│   └── config/database.php   # Belum dihubungkan
├── public/assets/
│   ├── js/app.js             # Interaksi frontend
│   └── images/               # Foto lokal + sumber lisensi
├── resources/css/app.css     # Gaya bersama
├── tests/                    # Pemeriksaan halaman dan interaksi
└── docs/FRONTEND.md           # Cara membaca/edit untuk tim
```

Baca [panduan frontend](docs/FRONTEND.md) untuk contoh `require`, menampilkan data, dan loop kartu.
