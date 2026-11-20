const assert = require('node:assert/strict');
const { spawn } = require('node:child_process');
const { once } = require('node:events');
const { mkdtempSync, rmSync } = require('node:fs');
const { createConnection } = require('node:net');
const { tmpdir } = require('node:os');
const { join } = require('node:path');
const { test } = require('node:test');
const { setTimeout: delay } = require('node:timers/promises');

test('WA can reconnect after disconnect, and its QR and status stay scoped to debug', { timeout: 10000 }, async () => {
    const directory = mkdtempSync(join(tmpdir(), 'putu-wa-test-'));
    const child = spawn(process.execPath, ['-e', `
        const Module = require('node:module');
        const { EventEmitter } = require('node:events');
        const load = Module._load;
        let client;
        let launches = 0;
        class Client extends EventEmitter {
            constructor() {
                super();
                client = this;
                this.pupBrowser = { connected: true };
            }
            async initialize() {
                if (++launches === 1) {
                    this.info = { wid: { server: 'c.us', user: '628000000000' } };
                    this.emit('ready');
                } else {
                    this.emit('qr', 'fake-qr-for-testing');
                }
            }
            async destroy() { this.pupBrowser.connected = false; }
        }
        Module._load = function (id, ...args) {
            return id === 'whatsapp-web.js' ? { Client, LocalAuth: class {} } : load.call(this, id, ...args);
        };
        process.on('message', action => {
            if (action === 'navigate') {
                const error = new Error('Execution context was destroyed, most likely because of a navigation.');
                error.stack += '\\n    at /test/whatsapp-web.js/src/Client.js:558:36';
                Promise.reject(error);
                setImmediate(() => process.send('navigation-handled'));
            } else client.emit('disconnected');
        });
        require(${JSON.stringify(join(__dirname, 'gateway.cjs'))});
    `, '--', '--whatsapp'], {
        env: { ...process.env, PUTU_NOTIFICATION_DIR: directory },
        stdio: ['ignore', 'ignore', 'inherit', 'ipc'],
    });
    const request = action => new Promise((resolve, reject) => {
        const socket = createConnection(join(directory, 'gateway.sock'), () => socket.end(JSON.stringify({ action }) + '\n'));
        socket.setEncoding('utf8');
        let body = '';
        socket.on('data', part => { body += part; });
        socket.on('error', reject);
        socket.on('end', () => {
            try { resolve(JSON.parse(body)); } catch (error) { reject(error); }
        });
    });
    async function until(state) {
        for (let i = 0; i < 40; i++) {
            const result = await request('wa_debug').catch(() => null);
            if (result?.state === state) return result;
            await delay(50);
        }
        assert.fail('Gateway did not reach ' + state);
    }
    try {
        assert.equal((await until('ready')).reconnect, false);
        assert.equal((await request('wa_reconnect')).code, 409);
        const navigation = once(child, 'message');
        child.send('navigate');
        assert.equal((await navigation)[0], 'navigation-handled');
        assert.equal((await request('wa_debug')).state, 'ready');
        const key = (await request('status')).publicKey;
        child.send('disconnect');
        assert.equal((await until('offline')).reconnect, true);
        const results = await Promise.all([request('wa_reconnect'), request('wa_reconnect')]);
        assert.deepEqual(results.map(result => result.code).sort(), [202, 409]);
        const pairing = await until('pairing');
        assert.match(pairing.image, /^data:image\/svg\+xml;base64,/);
        assert.equal(pairing.reconnect, false);
        assert.equal((await request('status')).publicKey, key);
        assert.equal((await request('status')).image, undefined);
        assert.equal((await request('qr')).qr, 'fake-qr-for-testing');
    } finally {
        if (child.exitCode === null && child.signalCode === null) {
            const closed = once(child, 'exit');
            child.kill();
            await closed;
        }
        rmSync(directory, { recursive: true, force: true });
    }
});
