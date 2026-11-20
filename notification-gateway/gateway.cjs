const { execFile } = require('node:child_process');
const { ECDH } = require('node:crypto');
const { chmodSync, lstatSync, mkdirSync, readFileSync, unlinkSync, writeFileSync } = require('node:fs');
const { createConnection, createServer } = require('node:net');
const { homedir } = require('node:os');
const { join } = require('node:path');
const { promisify } = require('node:util');
const webPush = require('web-push');

process.umask(0o077);
const stateDir = process.env.PUTU_NOTIFICATION_DIR || join(homedir(), '.config/putu/notifications');
mkdirSync(stateDir, { recursive: true, mode: 0o700 });
chmodSync(stateDir, 0o700);
const socketPath = join(stateDir, 'gateway.sock');

if (process.argv.includes('--show-qr')) {
    if (!process.stdout.isTTY) {
        console.error('Buka terminal lokal untuk melihat QR WhatsApp.');
        process.exit(1);
    }
    let lastQr;
    const pollQr = () => {
        const socket = createConnection(socketPath, () => socket.write('{"action":"qr"}\n'));
        let response = '';
        socket.on('data', chunk => { response += chunk; });
        socket.on('end', () => {
            const { qr, sender } = JSON.parse(response);
            if (sender) {
                console.log('WhatsApp sudah terhubung.');
                return;
            }
            if (qr && qr !== lastQr) {
                lastQr = qr;
                console.clear();
                console.log('WhatsApp → Perangkat tertaut → Tautkan perangkat');
                require('qrcode-terminal').generate(qr, { small: true });
            }
            setTimeout(pollQr, 2000);
        });
        socket.on('error', () => { console.error('Gateway PUTU belum berjalan.'); process.exitCode = 1; });
    };
    pollQr();
    return;
}
const keyFile = join(stateDir, 'vapid.json');
try {
    writeFileSync(keyFile, JSON.stringify(webPush.generateVAPIDKeys()), { flag: 'wx', mode: 0o600 });
} catch (error) {
    if (error.code !== 'EEXIST') throw error;
}
const keys = JSON.parse(readFileSync(keyFile, 'utf8'));
webPush.setVapidDetails(process.env.PUTU_VAPID_SUBJECT || 'mailto:notifications@putu.local', keys.publicKey, keys.privateKey);

const runPhp = promisify(execFile);
const chatId = /^\d{5,30}@(c\.us|lid)$/;
// whatsapp-web.js re-injects on navigation; the old frame can reject outside initialize().
process.on('unhandledRejection', error => {
    if (error?.message === 'Execution context was destroyed, most likely because of a navigation.'
        && error?.stack?.includes('/whatsapp-web.js/src/Client.js:')) {
        console.warn('Halaman WhatsApp sedang berpindah. Gateway tetap berjalan.');
        return;
    }
    throw error;
});

let whatsapp;
let sender = '';
let qrCode;
let waState = 'disabled';
let waStartedAt = 0;
let reconnecting = false;
function startWhatsApp() {
    if (!process.argv.includes('--whatsapp')) return;
    const { Client, LocalAuth } = require('whatsapp-web.js');
    sender = '';
    qrCode = undefined;
    waState = 'starting';
    waStartedAt = Date.now();
    const client = new Client({
        authStrategy: new LocalAuth({ dataPath: join(stateDir, 'whatsapp') }),
        userAgent: false,
        webVersionCache: { type: 'none' },
        puppeteer: { headless: true, executablePath: process.env.PUTU_CHROME || '/usr/bin/google-chrome', handleSIGTERM: false },
    });
    whatsapp = client;
    client.on('qr', qr => {
        if (whatsapp !== client) return;
        sender = '';
        qrCode = qr;
        waState = 'pairing';
        console.log('QR WhatsApp siap. Buka terminal lokal dengan --show-qr.');
    });
    client.on('ready', () => {
        if (whatsapp !== client) return;
        qrCode = undefined;
        const wid = client.info?.wid;
        sender = wid?.server === 'c.us' && /^\d{8,15}$/.test(wid.user) ? wid.user : '';
        waState = sender ? 'ready' : 'offline';
        console.log(sender ? 'WhatsApp siap.' : 'Nomor pengirim WhatsApp tidak bisa dibaca.');
    });
    client.on('authenticated', () => {
        if (whatsapp !== client) return;
        qrCode = undefined;
        waState = 'starting';
        waStartedAt = Date.now();
    });
    const markOffline = () => {
        if (whatsapp !== client) return;
        sender = '';
        qrCode = undefined;
        waState = 'offline';
    };
    client.on('disconnected', markOffline);
    client.on('auth_failure', markOffline);
    client.on('message', async message => {
        if (whatsapp !== client || !sender || !chatId.test(message.from) || message.fromMe) return;
        const command = message.body.trim();
        if (!/^(?:PUTU [a-f0-9]{48}|STOP)$/i.test(command)) return;
        try {
            const child = runPhp('/usr/bin/php', [join(__dirname, '../bin/whatsapp-link.php'), sender, message.from], { timeout: 10000 });
            child.child.stdin.end(command);
            const { stdout } = await child;
            await client.sendMessage(message.from, stdout.trim());
        } catch {
            console.error('Gagal memproses sambungan WhatsApp.');
        }
    });
    client.initialize().catch(async error => {
        const reason = String(error?.message).match(/^(?:net::ERR_[A-Z_]+|Navigation timeout|Failed to launch the browser process|Profile in use|Cannot read properties of undefined)/)?.[0] || error?.name || 'startup';
        console.error(`WhatsApp belum siap (${reason}). Web Push tetap berjalan.`);
        await client.destroy().catch(() => {});
        markOffline();
    });
}

function canReconnect() {
    return !reconnecting && (waState === 'offline' || (waState === 'starting' && Date.now() - waStartedAt > 60000));
}

async function reconnectWhatsApp() {
    if (!canReconnect()) return { code: 409 };
    reconnecting = true;
    waState = 'starting';
    waStartedAt = Date.now();
    qrCode = undefined;
    try {
        if (whatsapp?.pupBrowser?.connected) await whatsapp.destroy();
        startWhatsApp();
        return { code: 202 };
    } catch {
        waState = 'offline';
        return { code: 503 };
    } finally {
        reconnecting = false;
    }
}

function whatsappQrImage(value) {
    // qrcode-terminal already ships the QR encoder used by --show-qr.
    const QRCode = require('qrcode-terminal/vendor/QRCode');
    const { L } = require('qrcode-terminal/vendor/QRCode/QRErrorCorrectLevel');
    const qr = new QRCode(-1, L);
    qr.addData(value);
    qr.make();
    const size = qr.getModuleCount() + 8;
    const path = qr.modules.map((row, y) => row.map((dark, x) => dark ? `M${x + 4} ${y + 4.5}h1` : '').join('')).join('');
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${size}" shape-rendering="crispEdges"><path fill="white" d="M0 0h${size}v${size}H0z"/><path stroke="black" d="${path}"/></svg>`;
    return 'data:image/svg+xml;base64,' + Buffer.from(svg).toString('base64');
}

function validSubscription(subscription) {
    const endpoint = subscription?.endpoint;
    const { p256dh, auth } = subscription?.keys ?? {};
    if (typeof endpoint !== 'string' || endpoint.length > 2048 || !URL.canParse(endpoint)
        || typeof p256dh !== 'string' || !/^[A-Za-z0-9_-]{87}$/.test(p256dh)
        || typeof auth !== 'string' || !/^[A-Za-z0-9_-]{22}$/.test(auth)) return false;

    const url = new URL(endpoint);
    const host = url.hostname;
    const allowedHost = (
        host === 'fcm.googleapis.com' || host === 'updates.push.services.mozilla.com' ||
        host === 'web.push.apple.com' || host.endsWith('.push.apple.com') ||
        host.endsWith('.notify.windows.com')
    );
    if (!allowedHost || url.protocol !== 'https:' || url.port || url.username || url.password || url.hash) return false;

    const publicKey = Buffer.from(p256dh, 'base64url');
    const secret = Buffer.from(auth, 'base64url');
    if (publicKey[0] !== 4 || publicKey.toString('base64url') !== p256dh
        || secret.toString('base64url') !== auth) return false;
    try {
        ECDH.convertKey(publicKey, 'prime256v1');
        return true;
    } catch {
        return false;
    }
}

async function handle(input) {
    if (input.action === 'status') return { code: 200, publicKey: keys.publicKey, sender };
    if (input.action === 'qr') return { code: 200, qr: qrCode, sender };
    if (input.action === 'wa_debug') return { code: 200, state: waState, reconnect: canReconnect(), image: qrCode ? whatsappQrImage(qrCode) : null };
    if (input.action === 'wa_reconnect') return reconnectWhatsApp();
    if (input.action === 'whatsapp' && chatId.test(input.target) && typeof input.text === 'string') {
        if (!sender) return { code: 503 };
        if (input.sender !== sender) return { code: 422 };
        const message = await whatsapp.sendMessage(input.target, input.text, {
            linkPreview: false, sendSeen: false, waitUntilMsgSent: true,
        });
        return { code: message?.id ? 200 : 422 };
    }
    if (input.action === 'web_validate' || input.action === 'web') {
        if (!validSubscription(input.subscription)) return { code: 400 };
        if (input.action === 'web_validate') return { code: 200 };
        const ttl = input.ttl ?? 120;
        if (!Number.isInteger(ttl) || ttl < 1 || ttl > 3600) return { code: 422 };
        try {
            await webPush.sendNotification(input.subscription, JSON.stringify(input.payload), { TTL: ttl, timeout: 5000 });
            return { code: 200 };
        } catch (error) {
            const code = [404, 410, 429].includes(error.statusCode) ? error.statusCode : 503;
            const header = error.headers?.['retry-after'] ?? '';
            const seconds = /^\d+$/.test(header) ? Number(header) : Math.ceil((Date.parse(header) - Date.now()) / 1000);
            return { code, retryAfter: Number.isSafeInteger(seconds) && seconds > 0 ? seconds : 0 };
        }
    }
    return { code: 400 };
}

const server = createServer(socket => {
    socket.setEncoding('utf8');
    socket.setTimeout(8000, () => socket.destroy());
    socket.on('error', () => {});
    let body = '';
    const receive = async chunk => {
        body += chunk;
        if (body.length > 16384) return socket.destroy();
        if (!body.endsWith('\n')) return;
        socket.off('data', receive);
        try {
            socket.end(JSON.stringify(await handle(JSON.parse(body))) + '\n');
        } catch {
            socket.end('{"code":503}\n');
        }
    };
    socket.on('data', receive);
});
server.on('listening', () => {
    console.log('Gateway notifikasi siap.');
    startWhatsApp();
});
server.once('error', error => {
    if (error.code !== 'EADDRINUSE' || !lstatSync(socketPath).isSocket()) throw error;
    const probe = createConnection(socketPath, () => {
        probe.destroy();
        console.error('Gateway lain sudah berjalan.');
        process.exitCode = 1;
    });
    probe.on('error', failure => {
        if (failure.code !== 'ECONNREFUSED') throw failure;
        unlinkSync(socketPath);
        server.listen(socketPath);
    });
});
server.listen(socketPath);
process.on('SIGTERM', async () => {
    await whatsapp?.destroy().catch(() => {});
    process.exit(0);
});
