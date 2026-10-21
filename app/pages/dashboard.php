
<?php
// Data demo mahasiswa
$nama = "Juan";
$kelas = "SIB - 2A";

$tugas = [
    [
        "judul" => "Normalisasi database minimarket",
        "matkul" => "Basis Data",
        "status" => "Terlambat",
        "deadline" => "16 Okt"
    ],
    [
        "judul" => "Program inheritance Java",
        "matkul" => "Pemrograman Berorientasi Objek",
        "status" => "Dikerjakan",
        "deadline" => "22 Okt"
    ],
    [
        "judul" => "Analisis kebutuhan sistem",
        "matkul" => "Analisis Sistem Informasi",
        "status" => "Belum dikerjakan",
        "deadline" => "24 Okt"
    ]
];
?>

<section class="welcome-section">
    <p class="eyebrow">YOUR DAY, AT A GLANCE</p>

    <h1>Halo, <?= htmlspecialchars($nama) ?> 👋</h1>

    <p class="text-muted">
        Yuk, buat hari ini sedikit lebih teratur.
    </p>
</section>

<section class="stats-grid">

    <article class="stat-card">
        <h3>Tugas aktif</h3>
        <span class="stat-number">05</span>
        <p class="stat-description">
            Sedikit demi sedikit, pasti bisa.
        </p>
    </article>

    <article class="stat-card">
        <h3>Perlu diprioritaskan</h3>
        <span class="stat-number">05</span>
        <p class="stat-description">
            Deadline kurang dari 3 hari.
        </p>
    </article>

    <article class="stat-card">
        <h3>Sedang dikerjakan</h3>
        <span class="stat-number">01</span>
        <p class="stat-description">
            Lanjutkan langkah baikmu.
        </p>
    </article>

    <article class="stat-card">
        <h3>Sudah selesai</h3>
        <span class="stat-number">01</span>
        <p class="stat-description">
            Satu langkah lebih dekat.
        </p>
    </article>

</section>

<section class="dashboard-grid">

    <article class="content-card">
        <div class="card-heading">
            <div>
                <h2>Mulai dari yang terdekat</h2>
                <p class="text-muted">
                    Prioritas kecil, kemajuan besar.
                </p>
            </div>

            <a href="index.php?page=tugas">
                Lihat semua ↗
            </a>
        </div>

        <?php foreach ($tugas as $item): ?>
            <div class="task-item">

                <div>
                    <h3>
                        <?= htmlspecialchars($item["judul"]) ?>
                    </h3>

                    <p>
                        <?= htmlspecialchars($item["matkul"]) ?>
                        · <?= htmlspecialchars($item["deadline"]) ?>
                    </p>
                </div>

                <span class="badge <?=
                    $item["status"] === "Terlambat"
                    ? "badge-danger"
                    : ""
                ?>">
                    <?= htmlspecialchars($item["status"]) ?>
                </span>

            </div>
        <?php endforeach; ?>
    </article>

    <article class="content-card">
        <div class="card-heading">
            <div>
                <h2>Agenda kuliah</h2>
                <p class="text-muted">
                    Jadwal hari ini, siap dijalani.
                </p>
            </div>

            <a href="index.php?page=jadwal">
                Jadwal lengkap ↗
            </a>
        </div>

        <div class="task-item">
            <div>
                <h3>Basis Data</h3>
                <p>08.00–09.40 · Ruang TI</p>
            </div>
            <span class="badge">Sen</span>
        </div>

        <div class="task-item">
            <div>
                <h3>Pemrograman Berorientasi Objek</h3>
                <p>10.00–11.40 · Laboratorium</p>
            </div>
            <span class="badge">Sen</span>
        </div>

    </article>

</section>
