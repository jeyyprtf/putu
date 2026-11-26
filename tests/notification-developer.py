"""Use isolated PHP, Telegram and gateway fixtures to test the Developer panel."""
from hashlib import sha256
from http.cookiejar import CookieJar
from pathlib import Path
from tempfile import TemporaryDirectory
from urllib.error import HTTPError
from urllib.parse import urlencode
from urllib.request import HTTPCookieProcessor, Request, build_opener
import json
import os
import re
import shutil
import socket
from socketserver import StreamRequestHandler, UnixStreamServer
from subprocess import Popen
from threading import Thread
import time


CODE = "0123456789abcdef0123456789abcdef"
BOT_TOKEN = "123:fixture-initial"
URL = "/app/pages/developer.php"

TELEGRAM_STUB = r"""<?php
class FixtureTelegram {
    public $context;
    private string $payload;
    private int $offset = 0;
    public function stream_open($path, $mode, $options, &$opened): bool {
        if (!preg_match('~/bot(\d+):([^/]+)/(getMe|getWebhookInfo)$~', $path, $match)) {
            throw new RuntimeException('Unexpected Telegram action.');
        }
        [, $id, $secret, $action] = $match;
        $result = $action === 'getMe'
            ? ['id' => (int) $id, 'username' => 'fixture_bot']
            : ['url' => $secret === 'webhook' ? 'https://example.test' : ''];
        $this->payload = json_encode($secret === 'rejected'
            ? ['ok' => false, 'error_code' => 401] : ['ok' => true, 'result' => $result]);
        return true;
    }
    public function stream_read($count): string {
        $chunk = substr($this->payload, $this->offset, $count);
        $this->offset += strlen($chunk);
        return $chunk;
    }
    public function stream_eof(): bool { return $this->offset >= strlen($this->payload); }
    public function stream_stat(): array { return []; }
}
stream_wrapper_unregister('https');
stream_wrapper_register('https', FixtureTelegram::class);
"""


def client():
    return build_opener(HTTPCookieProcessor(CookieJar()))


def request(browser, path=URL, form=None, headers=None):
    encoded = None if form is None else urlencode(form).encode()
    try:
        response = browser.open(Request(BASE + path, data=encoded, headers=headers or {}), timeout=4)
    except HTTPError as error:
        response = error
    with response:
        return response.status, response.read().decode(), response.headers


def csrf(html):
    return re.search(r'name="_csrf" value="([a-f0-9]+)"', html)[1]


class Gateway(StreamRequestHandler):
    state = "disabled"
    actions = []

    def handle(self):
        data = json.loads(self.rfile.readline())
        action = data["action"]
        self.actions.append(action)
        if action == "wa_reconnect":
            type(self).state = "pairing"
            result = {"code": 202}
        elif action == "wa_debug":
            result = {"code": 200, "state": self.state, "reconnect": self.state == "disabled",
                      "image": "data:image/svg+xml;base64,QUJD" if self.state == "pairing" else None}
        elif action == "status":
            result = {"code": 200, "publicKey": "fixture-public-key"}
        elif action == "web":
            assert data["payload"]["title"] == "Tes notifikasi PUTU"
            result = {"code": 200}
        else:
            raise AssertionError(action)
        self.wfile.write((json.dumps(result) + "\n").encode())


with TemporaryDirectory(prefix="putu-dev-test-") as workdir:
    tmp = Path(workdir)
    code_root = tmp / "app-copy"
    code_root.mkdir()
    for directory in ("app", "public", "resources"):
        shutil.copytree(directory, code_root / directory, ignore=shutil.ignore_patterns("*.local.php"))
    for path in ("index.php", "notification-sw.js"):
        shutil.copy2(path, code_root / path)
    (tmp / "sessions").mkdir()
    (tmp / "telegram.php").write_text(TELEGRAM_STUB)
    state = tmp / "notifications"
    state.mkdir(mode=0o700)
    (state / "worker.heartbeat").touch()
    env = {"PATH": os.environ["PATH"], "PUTU_NOTIFICATION_DIR": str(state),
           "PUTU_DEVELOPER_TUNNEL_HOST": "putu-tunnel.example.test",
           "PUTU_DEVELOPER_CODE_HASH": sha256(CODE.encode()).hexdigest()}

    with socket.socket() as listener:
        listener.bind(("127.0.0.1", 0))
        port = listener.getsockname()[1]
    BASE = f"http://127.0.0.1:{port}"

    with UnixStreamServer(str(state / "gateway.sock"), Gateway) as gateway, (tmp / "php.log").open("w") as log:
        Thread(target=gateway.serve_forever, daemon=True).start()
        php = Popen(["php", "-d", f"session.save_path={tmp / 'sessions'}",
                     "-d", f"auto_prepend_file={tmp / 'telegram.php'}",
                     "-S", f"127.0.0.1:{port}", "-t", "public", "public/index.php"],
                    cwd=code_root, env=env, stdout=log, stderr=log)
        try:
            admin = client()
            for _ in range(30):
                try:
                    status, html, headers = request(admin)
                    break
                except OSError:
                    time.sleep(0.1)
            assert status == 200 and "Masuk Developer" in html
            assert headers["Cache-Control"] == "no-store"
            assert "frame-ancestors 'none'" in headers["Content-Security-Policy"]
            assert "putu_developer=" in headers["Set-Cookie"]
            assert request(admin, URL + "?status=1")[0] == 403
            assert request(admin, headers={"Host": "putu.example.test"})[0] == 404
            tunnel = {"Host": "putu-tunnel.example.test", "X-Forwarded-Proto": "https", "CF-Ray": "fixture"}
            status, _, tunnel_headers = request(client(), headers=tunnel)
            assert status == 200 and "secure" in tunnel_headers["Set-Cookie"].lower()
            assert request(client(), headers={**tunnel, "X-Forwarded-Proto": "http"})[0] == 404
            assert request(admin, headers={"X-Forwarded-For": "192.0.2.1"})[0] == 404
            assert request(admin, form={"aksi": "login", "kode": CODE})[0] == 403
            assert request(admin, form={"aksi": "login", "kode": "f" * 32, "_csrf": csrf(html)})[0] == 403
            assert request(admin, form={"aksi": "wa_reconnect", "_csrf": csrf(html)})[0] == 403
            assert not Gateway.actions

            status, page, _ = request(admin, form={"aksi": "login", "kode": CODE, "_csrf": csrf(html)})
            assert status == 200 and "Worker pengingat terdeteksi" in page and CODE not in page
            assert request(client(), URL + "?status=1")[0] == 403
            assert request(admin, form={"aksi": "telegram_save", "bot_token": BOT_TOKEN})[0] == 403

            def action(name, **values):
                code, output, _ = request(admin, form={"aksi": name, "_csrf": csrf(page), **values})
                assert code == 200 and BOT_TOKEN not in output
                return output

            assert "Token ditolak" in action("telegram_save", bot_token="123:rejected")
            assert "memakai webhook" in action("telegram_save", bot_token="123:webhook")
            saved = state / "telegram-bot.json"
            assert not saved.exists()
            assert "Token disimpan untuk @fixture_bot" in action("telegram_save", bot_token=BOT_TOKEN)
            assert (saved.stat().st_mode & 0o777) == 0o600
            assert json.loads(saved.read_text())["TELEGRAM_BOT_TOKEN"] == BOT_TOKEN
            assert "siap memakai polling" in action("telegram_check")
            assert "token bot yang sama" in action("telegram_save", bot_token="456:other-bot")
            assert json.loads(saved.read_text())["TELEGRAM_BOT_TOKEN"] == BOT_TOKEN
            assert "Token disimpan" in action("telegram_save", bot_token="123:rotated")
            assert json.loads(saved.read_text())["TELEGRAM_BOT_TOKEN"] == "123:rotated"

            assert "WhatsApp sedang menyambungkan" in action("wa_reconnect")
            assert "data:image" in request(admin, URL + "?status=1")[1]
            assert request(client(), URL + "?status=1")[0] == 403
            assert "Layanan push menerima tes" in action("web_test", subscription='{"endpoint":"fixture"}')
            assert "Tunggu 15 detik" in action("web_test", subscription='{"endpoint":"fixture"}')
            assert "Masuk Developer" in action("logout")
            assert request(admin, URL + "?status=1")[0] == 403
        finally:
            php.terminate()
            php.wait(timeout=5)
            gateway.shutdown()
    assert not re.search(r"PHP (Warning|Fatal error)", (tmp / "php.log").read_text())
    print("PASS: developer code, CSRF, Telegram validation/storage, private WhatsApp QR, Web Push and logout.")
