(() => {
    const one = (selector) => document.querySelector(selector);
    const all = (selector) => [...document.querySelectorAll(selector)];

    const authFeedback = (message) => {
        const feedback = one('#auth-feedback');
        if (feedback) {
            feedback.textContent = message;
            feedback.hidden = false;
        }
    };
    one('[data-password-toggle]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const input = one('#password');
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
    });
    one('[data-forgot-password]')?.addEventListener('click', () => {
        authFeedback('Pemulihan password belum tersedia. Hubungi admin kelas kamu.');
    });

    // Filter dan urutan daftar tugas. Sumber kebenaran ada di atribut kartu
    // (data-status, data-deadline, data-course) yang dirender PHP dari database.
    // Tidak ada penyimpanan di browser: status diubah lewat form POST.
    const taskList = one('#task-list');
    if (taskList) {
        const today = taskList.dataset.today;
        const daysLeft = (card) => Math.round((Date.parse(`${card.dataset.deadline}T00:00:00Z`) - Date.parse(`${today}T00:00:00Z`)) / 86400000);
        const isDone = (card) => card.dataset.status === 'done';
        const deadlineLabel = (card) => {
            const days = daysLeft(card);
            if (isDone(card)) return 'Selesai';
            if (days < 0) return 'Terlambat';
            if (days === 0) return 'Hari ini';
            if (days === 1) return 'Besok';
            return `${days} hari lagi`;
        };

        const statusModes = ['all', 'unfinished', 'done'];
        const statusLabels = { all: 'Semua', unfinished: 'Belum', done: 'Selesai' };
        const statusNames = { all: 'Semua', unfinished: 'Belum selesai', done: 'Selesai' };
        let statusFilter = new URLSearchParams(location.search).get('status');
        if (statusFilter === 'todo' || statusFilter === 'doing') statusFilter = 'unfinished';
        if (!statusModes.includes(statusFilter)) statusFilter = 'all';
        let courseFilter = '';
        let latestFirst = false;
        const cards = all('[data-task-card]');

        const applyFilters = () => {
            let visible = 0;
            for (const card of cards) {
                card.hidden = (statusFilter === 'unfinished' && isDone(card))
                    || (statusFilter === 'done' && !isDone(card))
                    || (courseFilter !== '' && card.dataset.course !== courseFilter);
                if (!card.hidden) visible++;
            }

            const button = one('#status-filter');
            if (button) {
                button.dataset.status = statusFilter;
                button.querySelector('[data-status-label]').textContent = statusLabels[statusFilter];
                const next = statusModes[(statusModes.indexOf(statusFilter) + 1) % statusModes.length];
                const label = `Status: ${statusNames[statusFilter]}. Ganti ke ${statusNames[next]}.`;
                button.setAttribute('aria-label', label);
                button.title = label;
            }

            for (const option of all('[data-course-filter]')) option.setAttribute('aria-pressed', String(option.dataset.courseFilter === courseFilter));

            const coursePicker = one('#course-picker');
            if (coursePicker) {
                coursePicker.querySelector('[data-course-label]').textContent = courseFilter || 'Matkul';
                coursePicker.dataset.active = String(courseFilter !== '');
                const label = `Mata kuliah: ${courseFilter || 'Semua'}. Pilih mata kuliah.`;
                coursePicker.setAttribute('aria-label', label);
                coursePicker.title = label;
            }

            const count = one('#task-result [data-task-count]');
            if (count) count.textContent = String(visible);
            if (one('#tasks-empty')) {
                one('#tasks-empty').hidden = cards.length === 0 || visible !== 0;
            }
        };

        // Label badge dihitung ulang di browser supaya tetap akurat walau
        // halaman dibuka lama. Data aslinya tetap dari PHP.
        for (const card of cards) {
            const badge = card.querySelector('[data-deadline-badge]');
            if (!badge) continue;
            badge.textContent = deadlineLabel(card);
            badge.dataset.urgent = String(daysLeft(card) >= 0 && daysLeft(card) <= 2);
            badge.dataset.late = String(daysLeft(card) < 0);
            badge.dataset.done = String(isDone(card));
        }

        one('#status-filter')?.addEventListener('click', () => {
            statusFilter = statusModes[(statusModes.indexOf(statusFilter) + 1) % statusModes.length];
            applyFilters();
        });

        for (const button of all('[data-course-filter]')) button.addEventListener('click', () => {
            courseFilter = button.dataset.courseFilter;
            applyFilters();
            one('#course-drawer').close();
        });

        const courseDrawer = one('#course-drawer');
        one('#course-picker')?.addEventListener('click', () => {
            courseDrawer.showModal();
            one('#course-picker').setAttribute('aria-expanded', 'true');
        });
        courseDrawer?.addEventListener('close', () => one('#course-picker').setAttribute('aria-expanded', 'false'));
        courseDrawer?.addEventListener('click', (event) => {
            if (event.target === courseDrawer && event.clientX < courseDrawer.getBoundingClientRect().left) courseDrawer.close();
        });

        const sortTasks = () => {
            cards.sort((a, b) => (latestFirst ? -1 : 1) * a.dataset.deadline.localeCompare(b.dataset.deadline));
            one('#task-list')?.append(...cards);
            const button = one('#task-sort');
            if (button) {
                button.querySelector('[data-sort-label]').textContent = latestFirst ? 'Terlama' : 'Terdekat';
                button.querySelector('svg').classList.toggle('rotate-180', latestFirst);
                const label = `Urutan: Deadline ${latestFirst ? 'terlama' : 'terdekat'}. Ganti ke deadline ${latestFirst ? 'terdekat' : 'terlama'}.`;
                button.setAttribute('aria-label', label);
                button.title = label;
            }
        };
        one('#task-sort')?.addEventListener('click', () => {
            latestFirst = !latestFirst;
            sortTasks();
        });
        one('[data-reset-filters]')?.addEventListener('click', () => {
            statusFilter = 'all';
            courseFilter = '';
            applyFilters();
        });

        sortTasks();
        applyFilters();
    }

    // Dashboard: hitungan dan badge deadline disegarkan dari #task-data.
    // Hanya membaca; perubahan status disimpan halaman tugas lewat POST.
    const data = one('#task-data');
    if (data) {
        const tasks = JSON.parse(data.textContent);
        const today = data.dataset.today;
        const statuses = Object.fromEntries(tasks.map((task) => [task.id, task.status]));
        const daysLeft = (task) => Math.round((Date.parse(`${task.deadline}T00:00:00Z`) - Date.parse(`${today}T00:00:00Z`)) / 86400000);
        const deadlineLabel = (task) => {
            const days = daysLeft(task);
            if (statuses[task.id] === 'done') return 'Selesai';
            if (days < 0) return 'Terlambat';
            if (days === 0) return 'Hari ini';
            if (days === 1) return 'Besok';
            return `${days} hari lagi`;
        };

        const counts = { all: tasks.length, active: 0, priority: 0, calm: 0, todo: 0, doing: 0, done: 0 };
        for (const task of tasks) {
            counts[statuses[task.id]]++;
            if (statuses[task.id] !== 'done') {
                counts.active++;
                if (daysLeft(task) <= 2) counts.priority++;
            }
            const row = all('[data-priority-task]').find((item) => item.dataset.priorityTask === String(task.id));
            const badge = row?.querySelector('[data-deadline-badge]');
            if (badge) {
                badge.textContent = deadlineLabel(task);
                badge.dataset.urgent = String(daysLeft(task) >= 0 && daysLeft(task) <= 2);
                badge.dataset.late = String(daysLeft(task) < 0);
                badge.dataset.done = String(statuses[task.id] === 'done');
            }
        }
        counts.calm = counts.active - counts.priority;

        for (const node of all('[data-count]')) node.textContent = String(counts[node.dataset.count]);
        const percent = tasks.length ? Math.round(counts.done / tasks.length * 100) : 0;
        if (one('[data-progress-percent]')) one('[data-progress-percent]').textContent = `${percent}%`;
        if (one('[data-progress]')) {
            one('[data-progress]').value = counts.done;
            one('[data-progress]').textContent = `${percent}%`;
        }
        let shown = 0;
        for (const row of all('[data-priority-task]')) {
            row.hidden = statuses[row.dataset.priorityTask] === 'done' || shown >= 3;
            if (!row.hidden) shown++;
        }
        if (one('#priorities-empty')) one('#priorities-empty').hidden = counts.active !== 0;
    }

    const schedule = one('#schedule-list');
    if (schedule) {
        let view = 'daily';
        let selectedDay = schedule.dataset.today;
        const updateSchedule = () => {
            schedule.classList.toggle('xl:grid-cols-1', view === 'weekly');
            for (const section of all('[data-schedule-day]')) section.hidden = view === 'daily' && section.dataset.scheduleDay !== selectedDay;
            for (const button of all('[data-schedule-view]')) button.setAttribute('aria-pressed', String(button.dataset.scheduleView === view));
            for (const button of all('[data-day]')) button.setAttribute('aria-pressed', String(button.dataset.day === selectedDay));
            one('#day-picker').hidden = view !== 'daily';
            one('#schedule-feedback').textContent = view === 'daily' ? `Jadwal ${one(`[data-day="${selectedDay}"]`).getAttribute('aria-label')}` : 'Jadwal satu minggu';
        };
        for (const button of all('[data-schedule-view]')) button.addEventListener('click', () => { view = button.dataset.scheduleView; updateSchedule(); });
        for (const button of all('[data-day]')) button.addEventListener('click', () => { selectedDay = button.dataset.day; updateSchedule(); });
        updateSchedule();
    }

    const filterNews = (category) => {
        const cards = all('[data-news-card]');
        let visible = 0;

        for (const card of cards) {
            card.hidden = category === 'Penting' && card.dataset.important !== 'true';
            if (!card.hidden) visible++;
        }

        for (const button of all('[data-news-filter]')) {
            button.setAttribute('aria-pressed', String(button.dataset.newsFilter === category));
        }

        const empty = one('#news-empty');
        if (empty) empty.hidden = empty.dataset.hasNews !== 'true' || visible !== 0;

        const result = one('#news-result');
        if (result) result.textContent = `${visible} pengumuman`;
    };
    for (const button of all('[data-news-filter]')) button.addEventListener('click', () => filterNews(button.dataset.newsFilter));
    one('[data-news-reset]')?.addEventListener('click', () => filterNews('Semua'));
})();


const toast = document.getElementById('toast');

if (toast) {
    const isError = toast.getAttribute('role') === 'alert';

    // Pesan error diberi waktu lebih lama.
    const duration = isError ? 6000 : 1500;

    setTimeout(() => {
        toast.classList.remove('animate-toast-in');
        toast.classList.add('animate-toast-out');

        setTimeout(() => {
            toast.remove();
        }, 250);
    }, duration);
}

(() => {
const select = document.getElementById('jenis');

const buttons = [
    ...document.querySelectorAll('[data-jenis]')
];

if (!select) return;

const update = () => {
    buttons.forEach((button) => {
        const active =
            button.dataset.jenis === select.value;


            button.setAttribute(
            'aria-pressed',
            String(active)
        );
        button.classList.toggle(
            'border-lime-300',
            active
        );
        button.classList.toggle(
            'bg-lime-300/10',
            active
        );
        button.classList.toggle(
            'text-lime-200',
            active
        );
        button.classList.toggle(
            'border-stone-700',
            !active
        );

        button.classList.toggle(
            'text-stone-400',
            !active
        );
    });
};


buttons.forEach((button) => {
    button.addEventListener('click', () => {
        select.value = button.dataset.jenis;
        update();
    });
});

select.addEventListener('change', update);

update();
})();

// ------------------------------------------------ Logout confirmation

(() => {
    const dialog = document.getElementById('logout-confirm');
    const cancel = document.querySelector('[data-logout-cancel]');
    const accept = document.querySelector('[data-logout-accept]');

    if (!dialog || !cancel || !accept) return;

    let pendingForm = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (
            !(form instanceof HTMLFormElement) ||
            !form.matches('[data-logout-confirm]')
        ) {
            return;
        }

        event.preventDefault();

        if (dialog.open) return;

        pendingForm = form;
        dialog.showModal();
    });

    cancel.addEventListener('click', () => {
        dialog.close();
    });

    accept.addEventListener('click', () => {
        if (!pendingForm) return;

        const form = pendingForm;
        pendingForm = null;

        accept.disabled = true;

        // Mengirim form POST asli beserta token CSRF.
        // Tidak memicu event submit kedua kali.
        HTMLFormElement.prototype.submit.call(form);
    });

    dialog.addEventListener('close', () => {
        pendingForm = null;
        accept.disabled = false;
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
})();

(() => {
    const dialog = document.getElementById('anggota-dialog');
    const daftar = document.getElementById('member-list');
    if (!dialog || !daftar) return;
    const target = document.getElementById('anggota-target');
    const nama = document.getElementById('anggota-dialog-nama');
    const role = document.getElementById('anggota-role');
    const edit = document.getElementById('anggota-edit');
    const konfirmasi = document.getElementById('anggota-konfirmasi');
    const namaKonfirmasi = document.getElementById('anggota-konfirmasi-nama');
    const mintaKeluar = document.getElementById('anggota-minta-keluar');
    const batal = document.getElementById('anggota-batal');

    daftar.addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-kelola-anggota]');
        if (!tombol) return;

        target.value = tombol.dataset.id;
        nama.textContent = tombol.dataset.nama;
        namaKonfirmasi.textContent = tombol.dataset.nama;
        role.value = tombol.dataset.role;

        edit.hidden = false;
        konfirmasi.hidden = true;
        dialog.showModal();
        role.focus();
    });

    mintaKeluar.addEventListener('click', () => {
        edit.hidden = true;
        konfirmasi.hidden = false;
        batal.focus();
    });

    batal.addEventListener('click', () => dialog.close());
})();

(() => {
                const search = document.getElementById('member-search');
                const result = document.getElementById('member-result');
                const tabs = [...document.querySelectorAll('[data-member-filter]')];
                const cards = [...document.querySelectorAll('[data-member]')];
                const empty = document.getElementById('members-empty');
                const reset = document.querySelector('[data-member-reset]');
                if (!search || !cards.length) return;

                let jabatan = 'Semua';

                const apply = () => {
                    const q = search.value.trim().toLowerCase();
                    let shown = 0;

                    cards.forEach((card) => {
                        const cocokJabatan = jabatan === 'Semua' || card.dataset.memberRole === jabatan;
                        const cocokKata = q === '' || card.dataset.memberSearch.includes(q);
                        const tampil = cocokJabatan && cocokKata;
                        card.classList.toggle('hidden', !tampil);
                        if (tampil) shown++;
                    });

                    empty.classList.toggle('hidden', shown !== 0);
                    result.textContent = shown + ' anggota';
                };

                search.addEventListener('input', apply);

                tabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        jabatan = tab.dataset.memberFilter;
                        tabs.forEach((t) => t.setAttribute('aria-pressed', String(t === tab)));
                        apply();
                    });
                });

                reset.addEventListener('click', () => {
                    search.value = '';
                    jabatan = 'Semua';
                    tabs.forEach((t) => t.setAttribute('aria-pressed', String(t.dataset.memberFilter === 'Semua')));
                    apply();
                });
            })();
