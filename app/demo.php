<?php

// shortcut: data pratinjau mengikuti hari ini; ganti sumber ini saat database terhubung.
$today = new DateTimeImmutable('today');
$tasks = [
    ['id' => 'erd', 'title' => 'Rancangan ERD perpustakaan', 'course' => 'Basis Data', 'code' => 'BD', 'type' => 'Tugas', 'description' => 'Buat ERD sistem perpustakaan beserta kardinalitas dan kamus data. Kumpulkan melalui LMS kampus.', 'deadline' => $today->modify('-1 day')->format('Y-m-d'), 'status' => 'done'],
    ['id' => 'normalisasi', 'title' => 'Normalisasi database minimarket', 'course' => 'Basis Data', 'code' => 'BD', 'type' => 'Tugas', 'description' => 'Normalisasikan tabel transaksi hingga bentuk 3NF. Sertakan ERD, dependensi fungsional, dan contoh query SQL. Kumpulkan melalui LMS dalam format PDF.', 'deadline' => $today->modify('+1 day')->format('Y-m-d'), 'status' => 'todo'],
    ['id' => 'stack', 'title' => 'Implementasi stack & queue', 'course' => 'Algoritma & Struktur Data', 'code' => 'ASD', 'type' => 'Praktikum', 'description' => 'Implementasikan stack dan circular queue. Jelaskan kompleksitas waktu dan sertakan hasil pengujian setiap operasi.', 'deadline' => $today->modify('+2 days')->format('Y-m-d'), 'status' => 'doing'],
    ['id' => 'checkout', 'title' => 'Analisis alur checkout e-commerce', 'course' => 'Analisis Proses Bisnis', 'code' => 'APB', 'type' => 'Proyek', 'description' => 'Gambarkan alur checkout sebuah platform e-commerce dalam diagram BPMN. Jelaskan aktor dan titik keputusan utamanya.', 'deadline' => $today->modify('+4 days')->format('Y-m-d'), 'status' => 'todo'],
    ['id' => 'matriks', 'title' => 'Latihan matriks & transformasi linear', 'course' => 'Matematika Diskrit', 'code' => 'MD', 'type' => 'Tugas', 'description' => 'Kerjakan latihan modul 4, halaman 28–32. Tuliskan langkah perhitungan dan kesimpulan setiap soal.', 'deadline' => $today->modify('+6 days')->format('Y-m-d'), 'status' => 'todo'],
    ['id' => 'round-robin', 'title' => 'Simulasi penjadwalan Round Robin', 'course' => 'Sistem Operasi', 'code' => 'SO', 'type' => 'Praktikum', 'description' => 'Simulasikan penjadwalan CPU dengan kuantum 4 ms. Bandingkan average waiting time dengan algoritma FCFS.', 'deadline' => $today->modify('+7 days')->format('Y-m-d'), 'status' => 'todo'],
];
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$todayIndex = (int) $today->format('N') - 1;
$schedules = [
    ['day' => 0, 'start' => '07:00', 'end' => '09:30', 'course' => 'Algoritma & Struktur Data', 'code' => 'ASD', 'room' => 'LPR3 · Gedung A', 'lecturer' => 'Dosen Algoritma & Struktur Data', 'type' => 'Praktikum'],
    ['day' => 0, 'start' => '10:30', 'end' => '13:00', 'course' => 'Basis Data', 'code' => 'BD', 'room' => 'LPR7 · Gedung B', 'lecturer' => 'Dosen Basis Data', 'type' => 'Teori'],
    ['day' => 1, 'start' => '13:00', 'end' => '15:30', 'course' => 'Analisis Proses Bisnis', 'code' => 'APB', 'room' => 'R.204 · Gedung B', 'lecturer' => 'Dosen Analisis Proses Bisnis', 'type' => 'Teori'],
    ['day' => 2, 'start' => '09:00', 'end' => '11:30', 'course' => 'Sistem Operasi', 'code' => 'SO', 'room' => 'R.108 · Gedung A', 'lecturer' => 'Dosen Sistem Operasi', 'type' => 'Teori'],
    ['day' => 3, 'start' => '10:30', 'end' => '14:30', 'course' => 'Basis Data', 'code' => 'BD', 'room' => 'LPR7 · Gedung B', 'lecturer' => 'Dosen Basis Data', 'type' => 'Praktikum'],
    ['day' => 4, 'start' => '07:00', 'end' => '10:30', 'course' => 'Sistem Operasi', 'code' => 'SO', 'room' => 'LERP · Lantai 7', 'lecturer' => 'Dosen Sistem Operasi', 'type' => 'Praktikum'],
    ['day' => 4, 'start' => '13:00', 'end' => '15:30', 'course' => 'Matematika Diskrit', 'code' => 'MD', 'room' => 'R.201 · Gedung A', 'lecturer' => 'Dosen Matematika Diskrit', 'type' => 'Teori'],
];
$announcements = [
    ['id' => 'ruang', 'title' => 'Perubahan ruang kelas Basis Data', 'category' => 'Akademik', 'important' => true, 'date' => $today->format('Y-m-d'), 'author' => 'Admin Akademik', 'summary' => 'Kelas Basis Data hari Kamis pindah ke LPR7, Gedung B. Jam perkuliahan tetap.', 'body' => 'Perkuliahan Basis Data hari Kamis dipindahkan ke LPR7, Gedung B. Jam perkuliahan tetap mengikuti jadwal. Pastikan kamu mengecek ruang sebelum kelas dimulai dan sampaikan informasi ini kepada teman satu kelompok.'],
    ['id' => 'sertifikasi', 'title' => 'Saatnya upgrade skill: sertifikasi database', 'category' => 'Kesempatan', 'important' => false, 'date' => $today->modify('-1 day')->format('Y-m-d'), 'author' => 'Admin Kemahasiswaan', 'summary' => 'Pendaftaran sertifikasi Database Administrator dibuka untuk mahasiswa SIB.', 'body' => 'Pendaftaran sertifikasi Database Administrator dibuka untuk mahasiswa Sistem Informasi Bisnis. Informasi persyaratan, jadwal, dan pendaftaran tersedia di administrasi jurusan. Siapkan kartu mahasiswa aktif dan transkrip sementara. Kuota peserta terbatas.'],
    ['id' => 'uts', 'title' => 'Persiapan pekan UTS', 'category' => 'Agenda', 'important' => false, 'date' => $today->modify('-3 days')->format('Y-m-d'), 'author' => 'Admin Akademik', 'summary' => 'Cek jadwal ujian dan persiapkan kebutuhanmu sebelum pekan UTS.', 'body' => 'UTS dimulai pekan depan. Cek jadwal ujian dan hadir 15 menit sebelum ujian dimulai. Bawa kartu mahasiswa dan alat tulis. Hubungi dosen pengampu jika ada benturan jadwal.'],
];
$menu = [
    'dashboard' => ['label' => 'Ringkasan', 'short' => 'Ringkasan', 'icon' => 'grid'],
    'tugas' => ['label' => 'Tugas saya', 'short' => 'Tugas saya', 'icon' => 'tasks'],
    'jadwal' => ['label' => 'Jadwal kuliah', 'short' => 'Jadwal', 'icon' => 'calendar'],
    'pengumuman' => ['label' => 'Pengumuman', 'short' => 'Pengumuman', 'icon' => 'bell'],
];
