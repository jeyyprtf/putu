// Node 22+. Start PHP on :8000 and headless Chrome with --remote-debugging-port=9334.
import assert from 'node:assert/strict';
const base = process.env.BASE_URL || 'http://127.0.0.1:8000';
const cdp = process.env.CDP_URL || 'http://127.0.0.1:9334';
const tab = await (await fetch(`${cdp}/json/new?about:blank`, { method: 'PUT' })).json();
const socket = new WebSocket(tab.webSocketDebuggerUrl);
await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
let id = 0;
const pending = new Map();
const exceptions = [];
socket.onmessage = (event) => {
    const message = JSON.parse(event.data);
    if (message.method === 'Runtime.exceptionThrown') exceptions.push(message.params);
    if (message.id) { pending.get(message.id)?.(message); pending.delete(message.id); }
};
const send = (method, params = {}) => new Promise((resolve, reject) => {
    const key = ++id;
    const timer = setTimeout(() => { pending.delete(key); reject(new Error(`Timeout: ${method}`)); }, 20000);
    pending.set(key, (message) => { clearTimeout(timer); message.error ? reject(new Error(JSON.stringify(message.error))) : resolve(message.result); });
    socket.send(JSON.stringify({ id: key, method, params }));
});
const evaluate = async (expression) => {
    const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
    assert.equal(result.exceptionDetails, undefined, JSON.stringify(result.exceptionDetails));
    return result.result.value;
};
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const navigate = async (page, legacy = false) => {
    const [name, ...query] = page.split('&');
    const path = legacy ? '/index.php?page=' + page : name ? '/app/pages/' + name + '.php' + (query.length ? '?' + query.join('&') : '') : '/index.php';
    await send('Page.navigate', { url: base + path });
    for (let count = 0; count < 100; count++) {
        await wait(100);
        if (await evaluate('document.readyState === "complete" && !!document.title && getComputedStyle(document.body).backgroundColor !== "rgba(0, 0, 0, 0)"')) return;
    }
    throw new Error('Page not ready: ' + page);
};
const visibleCount = (selector) => evaluate(`[...document.querySelectorAll(${JSON.stringify(selector)})].filter(e => !e.hidden).length`);
const click = (selector) => evaluate(`document.querySelector(${JSON.stringify(selector)}).click()`);
const change = (selector, value) => evaluate(`(() => { const e = document.querySelector(${JSON.stringify(selector)}); e.value = ${JSON.stringify(value)}; e.dispatchEvent(new Event('change', {bubbles:true})); })()`);
try {
    await send('Page.enable'); await send('Runtime.enable');
    await navigate('tugas');
    await evaluate('sessionStorage.clear()'); await navigate('tugas');
    assert.equal(await visibleCount('[data-task-card]'), 6);
    assert.equal(await evaluate('document.querySelector("#task-result").textContent'), '6 tugas');
    assert.equal(await evaluate('document.querySelector("footer")'), null);
    await click('#status-filter');
    assert.equal(await visibleCount('[data-task-card]'), 5);
    assert.equal(await evaluate('document.querySelector("[data-task-id=stack]").hidden'), false, 'Ongoing tasks are unfinished');
    await click('#status-filter');
    assert.equal(await visibleCount('[data-task-card]'), 1);
    await click('#status-filter');
    assert.equal(await visibleCount('[data-task-card]'), 6);
    await change('[data-task-id="normalisasi"] [data-task-status]', 'done');
    await click('#status-filter');
    assert.equal(await visibleCount('[data-task-card]'), 4);
    await click('#status-filter');
    assert.equal(await visibleCount('[data-task-card]'), 2);
    await click('[data-course-filter="Sistem Operasi"]');
    assert.equal(await visibleCount('[data-task-card]'), 0);
    assert.equal(await evaluate('document.querySelector("#tasks-empty").hidden'), false);
    await click('[data-reset-filters]');
    assert.equal(await evaluate('document.querySelector("#status-filter").dataset.status'), 'all');
    assert.equal(await evaluate('document.querySelector("[data-course-filter][aria-pressed=true]").dataset.courseFilter'), '');
    assert.equal(await visibleCount('[data-task-card]'), 6);
    await click('[data-course-filter="Basis Data"]');
    assert.equal(await visibleCount('[data-task-card]'), 2);
    await click('[data-course-filter=""]');
    const deadlines = () => evaluate('[...document.querySelectorAll("[data-task-card]")].map(card => card.dataset.deadline)');
    const nearestFirst = await deadlines();
    assert.deepEqual(nearestFirst, [...nearestFirst].sort());
    await click('#task-sort');
    assert.deepEqual(await deadlines(), [...nearestFirst].reverse());
    await click('#task-sort');
    assert.deepEqual(await deadlines(), nearestFirst);
    await click('[data-task-id="stack"] summary');
    assert.equal(await evaluate('document.querySelector("[data-task-id=stack] details").open'), true);
    await navigate('dashboard');
    assert.equal(await evaluate('document.querySelector("[data-progress-percent]").textContent'), '33%');
    assert.equal(await evaluate('document.querySelector("[data-count=active]").textContent'), '4');
    await navigate('dashboard', true);
    assert.equal(await evaluate('document.querySelector("[data-progress-percent]").textContent'), '33%');
    await navigate('tugas&status=done');
    assert.equal(await evaluate('document.querySelector("#status-filter").dataset.status'), 'done');
    assert.equal(await visibleCount('[data-task-card]'), 2);
    await change('[data-task-id="erd"] [data-task-status]', 'todo');
    assert.equal(await evaluate('document.querySelector("[data-task-id=erd] [data-deadline-badge]").textContent'), 'Terlambat');
    assert.equal(await visibleCount('[data-task-card]'), 1);
    for (const status of ['todo', 'doing', 'unfinished']) {
        await navigate(`tugas&status=${status}`);
        assert.equal(await evaluate('document.querySelector("#status-filter").dataset.status'), 'unfinished');
        assert.equal(await visibleCount('[data-task-card]'), 5);
    }
    await navigate('tugas&status=invalid');
    assert.equal(await visibleCount('[data-task-card]'), 6);
    await navigate('jadwal');
    assert.equal(await visibleCount('[data-schedule-day]'), 1);
    await click('[data-day="6"]');
    assert.equal(await evaluate('document.getElementById("day-6").closest("section").hidden'), false);
    await click('[data-schedule-view="weekly"]');
    assert.equal(await visibleCount('[data-schedule-day]'), 7);
    await navigate('pengumuman');
    assert.deepEqual(await evaluate('[...document.querySelectorAll("[data-news-filter]")].map(button => button.dataset.newsFilter)'), ['Semua', 'Penting']);
    assert.equal(await evaluate('document.querySelector("footer")'), null);
    assert.equal(await evaluate('document.body.innerText.includes("Stay in the loop") || document.body.innerText.includes("Informasi penting punya tempatnya sendiri.")'), false);
    await click('[data-news-filter="Penting"]');
    assert.equal(await visibleCount('[data-news-card]'), 1);
    await click('[data-news-card]:not([hidden]) summary');
    assert.equal(await evaluate('document.querySelector("[data-news-card]:not([hidden]) details").open'), true);
    await click('[data-news-filter="Semua"]');
    assert.equal(await visibleCount('[data-news-card]'), 3);
    await navigate('login');
    await click('[data-password-toggle]');
    assert.equal(await evaluate('document.querySelector("#password").type'), 'text');
    await evaluate('document.querySelector("#email").value="test@example.com"; document.querySelector("#password").value="examplepassword"; document.querySelector("form").requestSubmit()');
    assert.equal(await evaluate('document.querySelector("#auth-feedback").hidden'), false);
    await navigate('register');
    await evaluate('document.querySelector("#password").value="short"; document.querySelector("#password").dispatchEvent(new Event("input",{bubbles:true}))');
    assert.equal(await evaluate('document.querySelector("#password").minLength'), 8);
    for (const page of ['', 'login', 'register', 'dashboard', 'tugas', 'jadwal', 'pengumuman']) {
        await navigate(page);
        for (const width of [320, 390, 768, 1024, 1440]) {
            await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 768 });
            await wait(80);
            assert.equal(await evaluate('document.documentElement.scrollWidth <= innerWidth'), true, `${page} overflow at ${width}px`);
            if (page === 'tugas' && width < 640) {
                assert.equal(await evaluate('(() => { const row = document.querySelector("[data-course-filter]").parentElement; row.scrollLeft = row.scrollWidth; return row.scrollLeft > 0; })()'), true, 'Course pills scroll on mobile');
                assert.equal(await evaluate('[...document.querySelectorAll("#status-filter, #task-sort, [data-course-filter]")].every(button => button.offsetHeight >= 44)'), true, 'Touch targets stay at least 44px');
            }
        }
    }
    assert.deepEqual(exceptions, [], 'Browser JavaScript errors');
    console.log('PASS: filters, sorting, details, preview progress, schedule, auth controls, and 35 viewport checks.');
} finally {
    await send('Page.close').catch(() => {});
    socket.close();
}
