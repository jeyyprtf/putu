"""Run against the PHP dev server: python3 tests/smoke.py [base URL]."""
import json
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

base = (sys.argv[1] if len(sys.argv) > 1 else 'http://127.0.0.1:8000').rstrip('/')


def get(path):
    try:
        with urllib.request.urlopen(base + path, timeout=10) as response:
            return response.status, response.read().decode('utf-8', errors='replace')
    except urllib.error.HTTPError as response:
        return response.code, response.read().decode('utf-8', errors='replace')


for page in ['', 'login', 'register', 'dashboard', 'tugas', 'jadwal', 'pengumuman']:
    code, html = get('/index.php' + ('?page=' + page if page else ''))
    assert code == 200, page
    assert html.lower().count('<!doctype html>') == 1, page
    assert 'Warning:' not in html and 'Fatal error:' not in html, page
    for link in re.findall(r'(?:href|src)="([^"]+)"', html):
        if link.startswith('/') and not link.startswith('//'):
            status, _ = get(link.replace('&amp;', '&'))
            assert status == 200, (page, link, status)
    if page == 'dashboard':
        data = json.loads(re.search(r'<script id="task-data"[^>]*>(.*?)</script>', html, re.S)[1])
        assert len({task['id'] for task in data}) == len(data)
        assert all(task['status'] in ['todo', 'doing', 'done'] for task in data)
        assert sum(task['status'] == 'done' for task in data) == 1
    if page in ['login', 'register']:
        assert 'name="email"' in html and 'name="password"' in html
        assert 'Sign in with' not in html

for page in ['unknown', '../app/helpers.php', ['dashboard']]:
    query = urllib.parse.urlencode({'page[]' if isinstance(page, list) else 'page': page})
    assert get('/index.php?' + query)[0] == 404

for page in ['login', 'register', 'dashboard', 'tugas', 'jadwal', 'pengumuman']:
    code, html = get('/app/pages/' + page + '.php')
    assert code == 200 and html.lower().count('<!doctype html>') == 1
    assert 'Warning:' not in html

print('PASS: 7 pages, local links/assets, legacy URLs, sample data, and invalid routes.')
