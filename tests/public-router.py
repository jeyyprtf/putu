"""Test PUTU's public entry point against a local HTTP server."""
from http.cookiejar import CookieJar
from pathlib import Path
import re
import sys
from urllib.error import HTTPError
from urllib.parse import urlencode
from urllib.request import HTTPCookieProcessor, HTTPRedirectHandler, Request, build_opener


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


base = sys.argv[1].rstrip("/")
repo = Path(__file__).resolve().parents[1]
client = build_opener(NoRedirect(), HTTPCookieProcessor(CookieJar()))


def request(path, method="GET", data=None):
    try:
        response = client.open(Request(base + path, data=data, method=method), timeout=10)
    except HTTPError as error:
        response = error
    with response:
        return response.code, response.headers, response.read()


for path in ("/", "/index.php?page=login", "/app/pages/login.php", "/app/pages/register.php",
             "/app/pages/verifikasi-email.php", "/app/pages/lupa-password.php", "/app/pages/reset-password.php"):
    code, _, body = request(path)
    assert code == 200 and b"<!doctype html>" in body.lower(), path
    assert b'href="/resources/css/app.css"' in body, path

for path in ("/index.php?page=dashboard", "/app/pages/dashboard.php", "/app/pages/anggota.php",
             "/app/pages/admin/jadwal.php", "/app/pages/admin/tugas.php"):
    code, headers, _ = request(path)
    assert code == 302 and headers["Location"] == "/app/pages/login.php", path

path = "/app/pages/reset-password.php"
code, headers, _ = request(path + "?token_hash=" + "a" * 32)
assert code == 303 and headers["Location"] == path
code, _, body = request(path)
assert code == 200 and b"Buat password baru" in body
form = {}
for field in ("_csrf", "recovery_id"):
    match = re.search(r'name="' + field + r'" value="([^"]+)"', body.decode())
    assert match is not None, field
    form[field] = match[1]
form.update(password="test-password-1", password_confirmation="test-password-2")
code, _, body = request(path, "POST", urlencode(form).encode())
assert code == 200 and b"Konfirmasi password belum sama" in body

code, _, body = request("/app/pages/lupa-password.php", "POST", b"email=test%40example.test")
assert code == 200 and b"Sesi form kedaluwarsa" in body

assets = {
    "/resources/css/app.css": "text/css",
    "/resources/img/macosWallpaper.png": "image/png",
    "/public/assets/images/library-study.jpg": "image/jpeg",
    "/public/assets/js/app.js": "text/javascript",
    "/public/assets/js/web-push.js": "text/javascript",
    "/notification-sw.js": "text/javascript",
}
for path, content_type in assets.items():
    code, headers, body = request(path + "?v=test")
    assert code == 200 and headers.get_content_type() == content_type, path
    assert body == (repo / path.lstrip("/")).read_bytes(), path

code, headers, body = request("/notification-sw.js", "HEAD")
assert code == 200 and int(headers["Content-Length"]) > 0 and body == b""

for path in ("/tidak-ada", "/index.php?page=tidak-ada", "/index.php?page%5B%5D=login",
             "/app/config/database.php", "/docs/PUTU_PDM.sql",
             "/assets/images/SOURCES.md"):
    assert request(path)[0] == 404, path

print("PASS: public pages, legacy routes, auth redirects, callback, POST, assets and restricted paths.")
