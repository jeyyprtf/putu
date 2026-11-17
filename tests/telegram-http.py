"""Run only against a local server using a disposable test DB; creates test users/classes."""
import http.cookiejar
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
import uuid

base = sys.argv[1].rstrip('/')
assert urllib.parse.urlparse(base).hostname in ('127.0.0.1', 'localhost'), 'Use a local TEST server.'


class LocalRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        if not newurl.startswith(base + '/'):
            return None  # Never follow the connect link to real Telegram.
        return super().redirect_request(req, fp, code, msg, headers, newurl)


def client():
    return urllib.request.build_opener(LocalRedirect(), urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))


def request(browser, path, data=None):
    encoded = urllib.parse.urlencode(data).encode() if data is not None else None
    try:
        response = browser.open(base + path, data=encoded, timeout=10)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        html = response.read().decode()
        assert 'Fatal error:' not in html and 'Warning:' not in html, html
        return response.status, html, response.headers


def csrf(html):
    return re.search(r'name="_csrf" value="([a-f0-9]+)"', html)[1]


settings = '/app/pages/pengaturan.php'
anonymous = client()
assert 'Masuk' in request(anonymous, settings)[1]
browser = client()
_, html, _ = request(browser, '/app/pages/register.php')
email = 'telegram-http-' + uuid.uuid4().hex[:10] + '@example.test'
_, html, _ = request(browser, '/app/pages/register.php', {
    '_csrf': csrf(html), 'nama': 'Telegram HTTP Test', 'email': email, 'password': 'Test-local-only-1729'
})
_, html, _ = request(browser, '/app/pages/gabung.php?mode=create')
_, html, _ = request(browser, '/app/pages/gabung.php?mode=create', {'_csrf': csrf(html), 'name': 'Telegram Test Class'})
_, html, _ = request(browser, settings)
assert 'Notifikasi Telegram' in html and 'Telegram belum terhubung' in html
assert re.search(r'name="reminder_hours"[^>]*value="24"', html)
assert '123:test' not in html
assert 'value="telegram_test"' not in html, 'Tombol tes hanya untuk akun terhubung.'
_, rejected, _ = request(browser, settings, {'aksi': 'telegram_test'})
assert 'Sesi form kedaluwarsa' in rejected
_, rejected, _ = request(browser, settings, {'_csrf': csrf(html), 'aksi': 'telegram_test', 'chat_id': '999999'})
assert 'Hubungkan akun Telegram terlebih dahulu' in rejected

_, rejected, _ = request(browser, settings, {'aksi': 'telegram_save', 'reminder_hours': '6'})
assert 'Sesi form kedaluwarsa' in rejected
assert re.search(r'name="reminder_hours"[^>]*value="24"', request(browser, settings)[1])
for bad in ['0', '169', '1.5', '-1', 'abc']:
    _, rejected, _ = request(browser, settings, {'_csrf': csrf(html), 'aksi': 'telegram_save', 'reminder_hours': bad})
    assert '1–168 jam' in rejected
_, rejected, _ = request(browser, settings, {'_csrf': csrf(html), 'aksi': 'telegram_save', 'reminder_hours[]': '6'})
assert 'Waktu pengingat harus' in rejected

_, html, _ = request(browser, settings, {'_csrf': csrf(html), 'aksi': 'telegram_save',
    'reminder_hours': '6', 'reminders_enabled': '1', 'id_user': '999999'})
assert 'Pengaturan notifikasi disimpan.' in html
assert re.search(r'name="reminder_hours"[^>]*value="6"', html)
assert re.search(r'name="reminders_enabled"[^>]*checked', html)
assert not re.search(r'name="news_enabled"[^>]*checked', html)

# A second user keeps independent defaults.
second = client()
_, other, _ = request(second, '/app/pages/register.php')
request(second, '/app/pages/register.php', {'_csrf': csrf(other), 'nama': 'Other User',
    'email': 'other-' + email, 'password': 'Test-local-only-1729'})
_, other, _ = request(second, '/app/pages/gabung.php?mode=create')
request(second, '/app/pages/gabung.php?mode=create', {'_csrf': csrf(other), 'name': 'Other Test Class'})
_, other, _ = request(second, settings)
assert re.search(r'name="reminder_hours"[^>]*value="24"', other)

status, _, headers = request(browser, settings, {'_csrf': csrf(html), 'aksi': 'telegram_connect'})
assert status == 302
assert re.fullmatch(r'https://t.me/putu_pbl_bot\?start=[a-f0-9]{48}', headers['Location'])
assert headers['Referrer-Policy'] == 'no-referrer'
_, html, _ = request(browser, settings, {'_csrf': csrf(html), 'aksi': 'telegram_disconnect'})
assert 'Koneksi Telegram diputus.' in html
assert request(anonymous, '/bin/telegram-worker.php')[0] == 404
print('PASS: auth, CSRF, preference validation/persistence, per-user isolation, connect redirect, disconnect, CLI guard; no Telegram requests.')
print('Test login for local visual QA: ' + email)
