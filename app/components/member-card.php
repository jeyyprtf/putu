<?php
/** @var array $m  id_user, nama, email, role, no_hp */
$nama    = (string) ($m['nama'] ?? '');
$email   = (string) ($m['email'] ?? '');
$hp      = trim((string) ($m['no_hp'] ?? ''));
$isAdmin = (($m['role'] ?? '') === 'admin');
$isSelf  = (int) ($m['id_user'] ?? -1) === (int) $idUser;
$inisial = $nama !== '' ? strtoupper(substr($nama, 0, 1)) : '?';
$sub     = $hp !== '' ? $hp : $email;
?>
<article class="panel" data-member
    data-member-role="<?= $isAdmin ? 'Admin kelas' : 'Mahasiswa' ?>"
    data-member-search="<?= e(strtolower($nama . ' ' . $email . ' ' . $hp)) ?>">
    <div class="flex items-center gap-3 px-4 py-3.5">
        <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-lime-300/15 text-sm font-semibold text-lime-200" aria-hidden="true">
            <?= e($inisial) ?>
        </span>
        <div class="min-w-0 flex-1">
            <h2 class="truncate font-semibold"><?= e($nama) ?></h2>
            <p class="truncate text-sm text-stone-400">
                <?= e($sub) ?><?= $isSelf ? ' · Kamu' : '' ?>
            </p>
        </div>
        <span class="tag shrink-0 <?= $isAdmin ? 'bg-lime-300/15 text-lime-200' : 'bg-white/5 text-stone-300' ?>">
            <?= $isAdmin ? 'Admin kelas' : 'Mahasiswa' ?>
        </span>
        <span class="shrink-0 text-stone-600" aria-hidden="true"><?= icon('chevron', 'size-4') ?></span>
    </div>
</article>
