<?php
$tugas = [
    [
        'judul' => 'Normalisasi database minimarket',
        'matkul' => 'Basis Data',
        'deadline' => '16 Oktober 2026',
        'status' => 'Terlambat'
    ],
    [
        'judul' => 'Program inheritance Java',
        'matkul' => 'Pemrograman Berorientasi Objek',
        'deadline' => '22 Oktober 2026',
        'status' => 'Dikerjakan'
    ],
    [
        'judul' => 'Analisis kebutuhan sistem',
        'matkul' => 'Analisis Sistem Informasi',
        'deadline' => '24 Oktober 2026',
        'status' => 'Belum dikerjakan'
    ]
];
?>

<section class="mb-8">
    <p class="mb-2 text-sm text-lime-400">AKADEMIK MAHASISWA</p>
    <h1 class="text-3xl font-bold">Daftar Tugas</h1>
    <p class="mt-2 text-sm text-[#a3b0a7]">
        Pantau tugas dan deadline kuliahmu di sini.
    </p>
</section>

<section class="space-y-4">
    <?php foreach ($tugas as $item): ?>
        <article class="rounded-2xl border border-white/10 bg-[#1c2922] p-5">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <p class="mb-2 text-sm text-lime-400">
                        <?= htmlspecialchars($item['matkul']) ?>
                    </p>

                    <h2 class="text-lg font-semibold">
                        <?= htmlspecialchars($item['judul']) ?>
                    </h2>

                    <p class="mt-2 text-sm text-[#a3b0a7]">
                        Deadline: <?= htmlspecialchars($item['deadline']) ?>
                    </p>
                </div>

                <span class="w-fit rounded-full bg-[#214333] px-3 py-2 text-sm">
                    <?= htmlspecialchars($item['status']) ?>
                </span>
            </div>
        </article>
    <?php endforeach; ?>
</section>
```