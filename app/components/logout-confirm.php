<?php
/**
 * Popup konfirmasi logout PUTU.
 * Dipakai bersama oleh tombol logout desktop dan mobile.
 */
?>

<dialog
    id="logout-confirm"
    aria-labelledby="logout-confirm-title"
    aria-describedby="logout-confirm-description"
    class="fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-lg
           rounded-[2rem] border border-stone-700/50
           bg-stone-900 p-0 text-stone-100 shadow-2xl
           backdrop:bg-black/75 backdrop:backdrop-blur-sm"
>
    <div class="p-6 sm:p-8">
        <div class="mb-7 flex size-16 items-center justify-center
                    rounded-2xl bg-red-950/60 text-red-400">
            <?= icon('logout', 'size-8') ?>
        </div>

        <h2 id="logout-confirm-title"
            class="text-2xl font-bold tracking-tight sm:text-3xl">
            Keluar dari PUTU?
        </h2>

        <p id="logout-confirm-description"
           class="mt-4 text-sm leading-relaxed text-stone-400 sm:text-base">
            Kamu akan kembali ke halaman masuk dan perlu login
            kembali untuk mengakses kelasmu.
        </p>

        <div class="mt-8 grid grid-cols-2 gap-3">
            <button
                type="button"
                data-logout-cancel
                class="flex min-h-12 items-center justify-center rounded-full
                       border border-stone-700 px-4 font-semibold
                       text-stone-200 transition hover:bg-stone-800"
            >
                Batal
            </button>

            <button
                type="button"
                data-logout-accept
                class="flex min-h-12 items-center justify-center rounded-full
                       border border-red-400 px-4 font-semibold
                       text-red-400 transition hover:bg-red-400/10"
            >
                Keluar
            </button>
        </div>
    </div>
</dialog>