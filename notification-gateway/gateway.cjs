const { execFile } = require('node:child_process');
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
let whatsapp;
let sender = '';
let qrCode;
function startWhatsApp() {
    if (!process.argv.includes('--whatsapp')) return;
    const { Client, LocalAuth } = require('whatsapp-web.js');
    whatsapp = new Client({
        authStrategy: new LocalAuth({ dataPath: join(stateDir, 'whatsapp') }),
        userAgent: false,
        webVersionCache: { type: 'none' },
        puppeteer: { headless: true, executablePath: process.env.PUTU_CHROME || '/usr/bin/google-chrome', handleSIGTERM: false },
    });
    whatsapp.on('qr', qr => {
        sender = '';
        qrCode = qr;
        console.log('QR WhatsApp siap. Buka terminal lokal dengan --show-qr.');
    });
    whatsapp.on('ready', () => {
        qrCode = undefined;
        const wid = whatsapp.info.wid;
        sender = wid.server === 'c.us' && /^\d{8,15}$/.test(wid.user) ? wid.user : '';
        console.log(sender ? 'WhatsApp siap.' : 'Nomor pengirim WhatsApp tidak bisa dibaca.');
    });
    whatsapp.on('authenticated', () => { qrCode = undefined; });
    whatsapp.on('disconnected', () => { sender = ''; qrCode = undefined; });
    whatsapp.on('auth_failure', () => { sender = ''; qrCode = undefined; });
    whatsapp.on('message', async message => {
        if (!sender || !chatId.test(message.from) || message.fromMe) return;
        const command = message.body.trim();
        if (!/^(?:PUTU [a-f0-9]{48}|STOP)$/i.test(command)) return;
        try {
            const child = runPhp('/usr/bin/php', [join(__dirname, '../bin/whatsapp-link.php'), sender, message.from], { timeout: 10000 });
            child.child.stdin.end(command);
            const { stdout } = await child;
            await whatsapp.sendMessage(message.from, stdout.trim());
        } catch {
            console.error('Gagal memproses sambungan WhatsApp.');
        }
    });
    whatsapp.initialize().catch(async error => {
        const reason = String(error.message).match(/^(?:net::ERR_[A-Z_]+|Navigation timeout|Failed to launch the browser process|Profile in use|Cannot read properties of undefined)/)?.[0] || error.name;
        console.error(`WhatsApp belum siap (${reason}). Web Push tetap berjalan.`);
        await whatsapp.destroy().catch(() => {});
    });
}

function validEndpoint(endpoint) {
    const url = new URL(endpoint);
    const host = url.hostname;
    return url.protocol === 'https:' && !url.port && !url.username && !url.password && !url.hash && (
        host === 'fcm.googleapis.com' || host === 'updates.push.services.mozilla.com' ||
        host === 'web.push.apple.com' || host.endsWith('.push.apple.com') ||
        host.endsWith('.notify.windows.com')
    );
}

async function handle(input) {
    if (input.action === 'status') return { code: 200, publicKey: keys.publicKey, sender };
    if (input.action === 'qr') return { code: 200, qr: qrCode, sender };
    if (input.action === 'whatsapp' && chatId.test(input.target) && typeof input.text === 'string') {
        if (!sender || input.sender !== sender) return { code: 503 };
        await whatsapp.sendMessage(input.target, input.text, { linkPreview: false });
        return { code: 200 };
    }
    if (input.action === 'web' && validEndpoint(input.subscription?.endpoint)) {
        try {
            await webPush.sendNotification(input.subscription, JSON.stringify(input.payload), { TTL: 120, timeout: 5000 });
            return { code: 200 };
        } catch (error) {
            return { code: [404, 410, 429].includes(error.statusCode) ? error.statusCode : 503 };
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
