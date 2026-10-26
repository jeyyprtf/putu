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
    one('[data-auth-form]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        authFeedback('Autentikasi belum terhubung. Jelajahi pratinjau mahasiswa di bawah ini; data form tidak disimpan.');
    });
    one('[data-forgot-password]')?.addEventListener('click', () => {
        authFeedback('Pemulihan password akan tersedia setelah akun dan backend terhubung.');
    });

    const data = one('#task-data');
    if (data) {
        const tasks = JSON.parse(data.textContent);
        const today = data.dataset.today;
        const storageKey = `putu-preview:${data.dataset.storagePath}:${today}`;
        const validStatuses = ['todo', 'doing', 'done'];
        const statuses = Object.fromEntries(tasks.map((task) => [task.id, task.status]));
        try {
            const saved = JSON.parse(sessionStorage.getItem(storageKey));
            for (const task of tasks) {
                if (validStatuses.includes(saved?.[task.id])) statuses[task.id] = saved[task.id];
            }
        } catch { /* Pratinjau tetap bekerja jika penyimpanan browser tidak tersedia. */ }
        const daysLeft = (task) => Math.round((Date.parse(`${task.deadline}T00:00:00Z`) - Date.parse(`${today}T00:00:00Z`)) / 86400000);
        const deadlineLabel = (task) => {
            const days = daysLeft(task);
            if (statuses[task.id] === 'done') return 'Selesai';
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
                const done = statuses[card.dataset.taskId] === 'done';
                card.hidden = (statusFilter === 'unfinished' && done) || (statusFilter === 'done' && !done) || (courseFilter !== '' && card.dataset.course !== courseFilter);
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
            for (const pill of all('[data-course-filter]')) pill.setAttribute('aria-pressed', String(pill.dataset.courseFilter === courseFilter));
            if (one('#task-result')) one('#task-result').textContent = `${visible} tugas`;
            if (one('#tasks-empty')) one('#tasks-empty').hidden = visible !== 0;
        };
        const refresh = () => {
            const counts = { all: tasks.length, active: 0, priority: 0, todo: 0, doing: 0, done: 0 };
            for (const task of tasks) {
                counts[statuses[task.id]]++;
                if (statuses[task.id] !== 'done') {
                    counts.active++;
                    if (daysLeft(task) <= 2) counts.priority++;
                }
                const card = cards.find((card) => card.dataset.taskId === task.id);
                if (card) {
                    card.dataset.status = statuses[task.id];
                    card.querySelector('[data-task-status]').value = statuses[task.id];
                    const badge = card.querySelector('[data-deadline-badge]');
                    badge.textContent = deadlineLabel(task);
                    badge.dataset.urgent = String(daysLeft(task) >= 0 && daysLeft(task) <= 2);
                    badge.dataset.late = String(daysLeft(task) < 0);
                    badge.dataset.done = String(statuses[task.id] === 'done');
                }
            }
            for (const node of all('[data-count]')) {
                const count = counts[node.dataset.count];
                node.textContent = node.closest('[aria-label="Ringkasan tugas"]') ? String(count).padStart(2, '0') : String(count);
            }
            const percent = Math.round(counts.done / tasks.length * 100);
            if (one('[data-progress-percent]')) one('[data-progress-percent]').textContent = `${percent}%`;
            if (one('[data-progress]')) {
                one('[data-progress]').value = counts.done;
                one('[data-progress]').textContent = `${percent}%`;
            }
            let shown = 0;
            for (const row of all('[data-priority-task]')) {
                row.hidden = statuses[row.dataset.priorityTask] === 'done' || shown >= 4;
                if (!row.hidden) shown++;
            }
            if (one('#priorities-empty')) one('#priorities-empty').hidden = counts.active !== 0;
            applyFilters();
        };
        for (const select of all('[data-task-status]')) {
            select.addEventListener('change', () => {
                const id = select.closest('[data-task-card]').dataset.taskId;
                if (!validStatuses.includes(select.value)) return;
                statuses[id] = select.value;
                let message = 'Progres pratinjau diperbarui di tab ini. Belum tersimpan ke akun.';
                try { sessionStorage.setItem(storageKey, JSON.stringify(statuses)); }
                catch { message = 'Progres berubah sementara. Browser tidak mengizinkan penyimpanan; perubahan hilang saat pindah halaman.'; }
                refresh();
                one('#task-feedback').textContent = message;
            });
        }
        one('#status-filter')?.addEventListener('click', () => {
            statusFilter = statusModes[(statusModes.indexOf(statusFilter) + 1) % statusModes.length];
            applyFilters();
        });
        for (const button of all('[data-course-filter]')) button.addEventListener('click', () => {
            courseFilter = button.dataset.courseFilter;
            applyFilters();
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
        refresh();
    }

    const schedule = one('#schedule-list');
    if (schedule) {
        let view = 'daily';
        let selectedDay = schedule.dataset.today;
        const updateSchedule = () => {
            schedule.classList.toggle('xl:grid-cols-2', view === 'weekly');
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
        let visible = 0;
        for (const card of all('[data-news-card]')) {
            card.hidden = category === 'Penting' && card.dataset.important !== 'true';
            if (!card.hidden) visible++;
        }
        for (const button of all('[data-news-filter]')) button.setAttribute('aria-pressed', String(button.dataset.newsFilter === category));
        one('#news-empty').hidden = visible !== 0;
        one('#news-result').textContent = `${visible} pengumuman`;
    };
    for (const button of all('[data-news-filter]')) button.addEventListener('click', () => filterNews(button.dataset.newsFilter));
    one('[data-news-reset]')?.addEventListener('click', () => filterNews('Semua'));
})();
