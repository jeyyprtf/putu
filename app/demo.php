<?php

// shortcut: profil contoh; ambil pengguna aktif dari backend saat autentikasi tersedia.
$profile = ['name' => 'Juan', 'email' => 'juan@example.com', 'initial' => 'J'];

// shortcut: data pratinjau mengikuti hari ini; ganti sumber ini saat database terhubung.
$today = new DateTimeImmutable('today');
$tasks = [
    [
        'id' => 'erd',
        'title' => 'Rancangan ERD perpustakaan',
        'course' => 'Basis Data',
        'code' => 'BD',
        'type' => 'Tugas',
        'description' => 'Buat ERD sistem perpustakaan beserta kardinalitas dan kamus data. Kumpulkan melalui LMS kampus.',
        'deadline' => $today->modify('-1 day')->format('Y-m-d'),
        'status' => 'done'
    ],
    [
        'id' => 'normalisasi',
        'title' => 'Normalisasi database minimarket',
        'course' => 'Basis Data',
        'code' => 'BD',
        'type' => 'Tugas',
        'description' => 'Normalisasikan tabel transaksi hingga bentuk 3NF. Sertakan ERD, dependensi fungsional, dan contoh query SQL. Kumpulkan melalui LMS dalam format PDF.',
        'deadline' => $today->modify('+1 day')->format('Y-m-d'),
        'status' => 'todo'
    ],
    [
        'id' => 'stack',
        'title' => 'Implementasi stack & queue',
        'course' => 'Algoritma & Struktur Data',
        'code' => 'ASD',
        'type' => 'Praktikum',
        'description' => 'Implementasikan stack dan circular queue. Jelaskan kompleksitas waktu dan sertakan hasil pengujian setiap operasi.',
        'deadline' => $today->modify('+2 days')->format('Y-m-d'),
        'status' => 'doing'
    ],
    [
        'id' => 'checkout',
        'title' => 'Analisis alur checkout e-commerce',
        'course' => 'Analisis Proses Bisnis',
        'code' => 'APB',
        'type' => 'Proyek',
        'description' => 'Gambarkan alur checkout sebuah platform e-commerce dalam diagram BPMN. Jelaskan aktor dan titik keputusan utamanya.',
        'deadline' => $today->modify('+4 days')->format('Y-m-d'),
        'status' => 'todo'
    ],
    [
        'id' => 'matriks',
        'title' => 'Latihan matriks & transformasi linear',
        'course' => 'Matematika Diskrit',
        'code' => 'MD',
        'type' => 'Tugas',
        'description' => 'Kerjakan latihan modul 4, halaman 28–32. Tuliskan langkah perhitungan dan kesimpulan setiap soal.',
        'deadline' => $today->modify('+6 days')->format('Y-m-d'),
        'status' => 'todo'
    ],
    [
        'id' => 'round-robin',
        'title' => 'Simulasi penjadwalan Round Robin',
        'course' => 'Sistem Operasi',
        'code' => 'SO',
        'type' => 'Praktikum',
        'description' => 'Simulasikan penjadwalan CPU dengan kuantum 4 ms. Bandingkan average waiting time dengan algoritma FCFS.',
        'deadline' => $today->modify('+7 days')->format('Y-m-d'),
        'status' => 'todo'
    ],
];
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$todayIndex = (int) $today->format('N') - 1;
$schedules = [
    [
        'day' => 0,
        'start' => '07:00',
        'end' => '09:30',
        'course' => 'Algoritma & Struktur Data',
        'code' => 'ASD',
        'room' => 'LPR3 · Gedung A',
        'lecturer' => 'Dosen Algoritma & Struktur Data',
        'type' => 'Praktikum'
    ],
    [
        'day' => 0,
        'start' => '10:30',
        'end' => '13:00',
        'course' => 'Basis Data',
        'code' => 'BD',
        'room' => 'LPR7 · Gedung B',
        'lecturer' => 'Dosen Basis Data',
        'type' => 'Teori'
    ],
    [
        'day' => 1,
        'start' => '13:00',
        'end' => '15:30',
        'course' => 'Analisis Proses Bisnis',
        'code' => 'APB',
        'room' => 'R.204 · Gedung B',
        'lecturer' => 'Dosen Analisis Proses Bisnis',
        'type' => 'Teori'
    ],
    [
        'day' => 2,
        'start' => '09:00',
        'end' => '11:30',
        'course' => 'Sistem Operasi',
        'code' => 'SO',
        'room' => 'R.108 · Gedung A',
        'lecturer' => 'Dosen Sistem Operasi',
        'type' => 'Teori'
    ],
    [
        'day' => 3,
        'start' => '10:30',
        'end' => '14:30',
        'course' => 'Basis Data',
        'code' => 'BD',
        'room' => 'LPR7 · Gedung B',
        'lecturer' => 'Dosen Basis Data',
        'type' => 'Praktikum'
    ],
    [
        'day' => 4,
        'start' => '07:00',
        'end' => '10:30',
        'course' => 'Sistem Operasi',
        'code' => 'SO',
        'room' => 'LERP · Lantai 7',
        'lecturer' => 'Dosen Sistem Operasi',
        'type' => 'Praktikum'
    ],
    [
        'day' => 4,
        'start' => '13:00',
        'end' => '15:30',
        'course' => 'Matematika Diskrit',
        'code' => 'MD',
        'room' => 'R.201 · Gedung A',
        'lecturer' => 'Dosen Matematika Diskrit',
        'type' => 'Teori'
    ],
];
$announcements = [
    [
        'id' => 'ruang',
        'title' => 'Perubahan ruang kelas Basis Data',
        'category' => 'Akademik',
        'important' => true,
        'date' => $today->format('Y-m-d'),
        'author' => 'Admin Akademik',
        'summary' => 'Kelas Basis Data hari Kamis pindah ke LPR7, Gedung B. Jam perkuliahan tetap.',
        'body' => 'Perkuliahan Basis Data hari Kamis dipindahkan ke LPR7, Gedung B. Jam perkuliahan tetap mengikuti jadwal. Pastikan kamu mengecek ruang sebelum kelas dimulai dan sampaikan informasi ini kepada teman satu kelompok.'
    ],
    [
        'id' => 'sertifikasi',
        'title' => 'Saatnya upgrade skill: sertifikasi database',
        'category' => 'Kesempatan',
        'important' => false,
        'date' => $today->modify('-1 day')->format('Y-m-d'),
        'author' => 'Admin Kemahasiswaan',
        'summary' => 'Pendaftaran sertifikasi Database Administrator dibuka untuk mahasiswa SIB.',
        'body' => 'Pendaftaran sertifikasi Database Administrator dibuka untuk mahasiswa Sistem Informasi Bisnis. Informasi persyaratan, jadwal, dan pendaftaran tersedia di administrasi jurusan. Siapkan kartu mahasiswa aktif dan transkrip sementara. Kuota peserta terbatas.'
    ],
    [
        'id' => 'uts',
        'title' => 'Persiapan pekan UTS',
        'category' => 'Agenda',
        'important' => false,
        'date' => $today->modify('-3 days')->format('Y-m-d'),
        'author' => 'Admin Akademik',
        'summary' => 'Cek jadwal ujian dan persiapkan kebutuhanmu sebelum pekan UTS.',
        'body' => 'UTS dimulai pekan depan. Cek jadwal ujian dan hadir 15 menit sebelum ujian dimulai. Bawa kartu mahasiswa dan alat tulis. Hubungi dosen pengampu jika ada benturan jadwal.'
    ],
];

// Hitungan disiapkan di sini supaya file halaman fokus pada HTML.
$taskCounts = ['all' => count($tasks), 'active' => 0, 'priority' => 0, 'todo' => 0, 'doing' => 0, 'done' => 0];
$priorityDeadline = $today->modify('+2 days')->format('Y-m-d');
foreach ($tasks as $task) {
    $taskCounts[$task['status']]++;
    if ($task['status'] !== 'done') {
        $taskCounts['active']++;
        if ($task['deadline'] <= $priorityDeadline) {
            $taskCounts['priority']++;
        }
    }
}
$taskCounts['calm'] = $taskCounts['active'] - $taskCounts['priority'];
$percent = $taskCounts['all'] > 0 ? (int) round($taskCounts['done'] / $taskCounts['all'] * 100) : 0;
$courseNames = array_unique(array_column($tasks, 'course'));
$deadlineTasks = $tasks;
usort($deadlineTasks, fn ($a, $b) => strcmp($a['deadline'], $b['deadline']));
$nearestTaskIds = array_column(array_slice(array_filter($deadlineTasks, fn ($task) => $task['status'] !== 'done'), 0, 3), 'id');

$classesByDay = array_fill(0, 7, []);
foreach ($schedules as $class) {
    $classesByDay[$class['day']][] = $class;
}
$todayClasses = $classesByDay[$todayIndex];
$scheduleCourseCount = count(array_unique(array_column($schedules, 'course')));
$scheduleDayCount = count(array_unique(array_column($schedules, 'day')));
