const express = require('express');
const { default: makeWASocket, DisconnectReason, useMultiFileAuthState, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(express.json());

const PORT = process.env.PORT || 3001;
const AUTH_DIR = path.join(__dirname, 'auth_info');

let sock = null;
let connectionStatus = 'INITIALIZING'; // 'INITIALIZING' | 'SCAN_QR' | 'CONNECTED' | 'DISCONNECTED'
let qrDataUrl = null;
let connectedUser = null;
let connectedAt = null;
let isReconnecting = false;

// Ensure auth dir exists
if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

async function connectToWhatsApp() {
    if (isReconnecting) return;
    isReconnecting = true;

    try {
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            auth: state,
            printQRInTerminal: true,
            logger: pino({ level: 'silent' }),
            browser: ['NetSaz Billing', 'Chrome', '120.0.0.0'],
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 30000,
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                try {
                    qrDataUrl = await QRCode.toDataURL(qr, {
                        width: 320,
                        margin: 2,
                        color: { dark: '#0F172A', light: '#FFFFFF' }
                    });
                    connectionStatus = 'SCAN_QR';
                    console.log('[NetSaz WA Gateway] QR Code generated, waiting for scan...');
                } catch (err) {
                    console.error('[NetSaz WA Gateway] QR Error:', err);
                }
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                console.log(`[NetSaz WA Gateway] Connection closed (code: ${statusCode}). Reconnect: ${shouldReconnect}`);

                connectionStatus = 'DISCONNECTED';
                connectedUser = null;
                connectedAt = null;

                if (statusCode === DisconnectReason.loggedOut) {
                    // Session was logged out by phone/server
                    console.log('[NetSaz WA Gateway] Device logged out. Clearing auth credentials...');
                    try {
                        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                        fs.mkdirSync(AUTH_DIR, { recursive: true });
                    } catch (e) {}
                    qrDataUrl = null;
                    isReconnecting = false;
                    setTimeout(connectToWhatsApp, 2000);
                } else {
                    // Temporary disconnect / glitch, reconnect using existing auth
                    isReconnecting = false;
                    setTimeout(connectToWhatsApp, 3000);
                }
            } else if (connection === 'open') {
                connectionStatus = 'CONNECTED';
                qrDataUrl = null;
                connectedAt = new Date().toISOString();

                const jid = sock.user?.id || '';
                const phone = jid.split(':')[0] || jid.split('@')[0];
                connectedUser = {
                    id: jid,
                    phone: phone,
                    name: sock.user?.name || 'Admin WhatsApp'
                };

                console.log(`[NetSaz WA Gateway] WhatsApp CONNECTED! Phone: ${phone}`);
                isReconnecting = false;
            }
        });
    } catch (err) {
        console.error('[NetSaz WA Gateway] Error initializing socket:', err);
        connectionStatus = 'DISCONNECTED';
        isReconnecting = false;
        setTimeout(connectToWhatsApp, 5000);
    }
}

// Format phone number to WhatsApp JID
function formatJid(phone) {
    let clean = phone.replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    }
    return `${clean}@s.whatsapp.net`;
}

// ---------------- API ENDPOINTS ----------------

// Status & QR Code
app.get('/status', (req, res) => {
    res.json({
        success: true,
        status: connectionStatus,
        qr: qrDataUrl,
        user: connectedUser,
        connected_at: connectedAt,
        uptime: Math.floor(process.uptime()),
    });
});

// Send Text Message
app.post('/send', async (req, res) => {
    const { to, phone, message, text } = req.body;
    const targetPhone = to || phone;
    const targetText = message || text;

    if (!targetPhone || !targetText) {
        return res.status(400).json({
            success: false,
            message: 'Parameter "to" dan "message" wajib diisi.'
        });
    }

    if (connectionStatus !== 'CONNECTED' || !sock) {
        return res.status(503).json({
            success: false,
            status: connectionStatus,
            message: 'WhatsApp Gateway belum terhubung. Silakan scan QR Code terlebih dahulu.'
        });
    }

    try {
        const jid = formatJid(targetPhone);
        const result = await sock.sendMessage(jid, { text: targetText });

        res.json({
            success: true,
            message: 'Pesan berhasil terkirim ke WhatsApp.',
            messageId: result?.key?.id,
            to: targetPhone
        });
    } catch (err) {
        console.error('[NetSaz WA Gateway] Failed to send message:', err);
        res.status(500).json({
            success: false,
            message: 'Gagal mengirim pesan WhatsApp: ' + err.message
        });
    }
});

// Compatibility Open-WA endpoint: /api/sessions/:session/messages/send-text
app.post('/api/sessions/:session/messages/send-text', async (req, res) => {
    const { chatId, text } = req.body;
    if (!chatId || !text) {
        return res.status(400).json({ success: false, message: 'chatId and text required' });
    }
    try {
        const phone = chatId.replace('@c.us', '').replace('@s.whatsapp.net', '');
        const jid = formatJid(phone);
        const result = await sock.sendMessage(jid, { text });
        res.json({ success: true, messageId: result?.key?.id });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Logout & Clear Session (for generating fresh QR code)
app.post('/logout', async (req, res) => {
    try {
        if (sock) {
            await sock.logout().catch(() => {});
        }
        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (e) {}

        connectionStatus = 'INITIALIZING';
        qrDataUrl = null;
        connectedUser = null;
        connectedAt = null;
        isReconnecting = false;

        setTimeout(connectToWhatsApp, 1000);

        res.json({
            success: true,
            message: 'Sesi WhatsApp berhasil direset. Silakan scan QR Code baru.'
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Restart Gateway
app.post('/restart', (req, res) => {
    try {
        if (sock) {
            sock.end(undefined);
        }
        isReconnecting = false;
        setTimeout(connectToWhatsApp, 1000);
        res.json({ success: true, message: 'Gateway direstart.' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

app.listen(PORT, '127.0.0.1', () => {
    console.log(`[NetSaz WA Gateway] Server listening on http://127.0.0.1:${PORT}`);
    connectToWhatsApp();
});
