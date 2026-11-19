<?php
/** @var array $m */
$nama = (string) ($m['nama'] ?? '');
$email = (string) ($m['email'] ?? '');
$hp = trim((string) ($m['no_hp'] ?? ''));
$idAnggota = (int) $m['id_user'];

$isAdmin = ($m['role'] ?? '') === 'admin';
$isSelf = $idAnggota === $idUser;
$bisaKelola = $isAdminKelas && !$isSelf;

$labelRole = $isAdmin ? 'Admin kelas' : 'Mahasiswa';
$sub = $hp !== '' ? $hp : $email;

preg_match('/^\X/u', trim($nama), $huruf);
$inisial = strtoupper($huruf[0] ?? '?');
?>

<article
    class="panel relative min-w-0"
    data-member
    data-member-role="<?= e($labelRole) ?>"
    data-member-search="<?= e(strtolower($nama . ' ' . $email . ' ' . $hp)) ?>"
>
    <div class="flex min-w-0 items-center gap-3 px-4 py-3.5">
        <span
            aria-hidden="true"
            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-lime-300/15 text-xs md:text-sm font-semibold text-lime-200"
        >
            <?= e($inisial) ?>
        </span>

        <div class="flex min-w-0 flex-1 flex-col items-start gap-5 sm:flex-row sm:items-center sm:gap-3">
            <div class="min-w-0 max-w-full sm:flex-1 ">
                <h2 class="font-semibold [overflow-wrap:anywhere]"><?= e($nama) ?></h2>
                <p class="text-xs md:text-sm text-stone-400 [overflow-wrap:anywhere]">
                    <?= e($sub) ?><?= $isSelf ? ' · Kamu' : '' ?>
                </p>
            </div>

            <span class="tag shrink-0 <?= $isAdmin ? 'bg-lime-300/15 text-lime-200' : 'bg-white/5 text-stone-300' ?>">
                <?= e($labelRole) ?>
            </span>
        </div>

        <?php if ($bisaKelola): ?>
            <span class="shrink-0 text-stone-500" aria-hidden="true">
                <?= icon('chevron', 'size-4') ?>
            </span>

            <button
                type="button"
                class="absolute inset-0 w-full cursor-pointer rounded-[inherit] focus-visible:outline-2 focus-visible:outline-lime-300"
                data-kelola-anggota
                data-id="<?= $idAnggota ?>"
                data-nama="<?= e($nama) ?>"
                data-role="<?= $isAdmin ? 'admin' : 'mahasiswa' ?>"
                aria-label="<?= e('Kelola ' . $nama) ?>"
                aria-haspopup="dialog"
                aria-controls="anggota-dialog"
            ></button>
        <?php endif; ?>
    </div>
</article>