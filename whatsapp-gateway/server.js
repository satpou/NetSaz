const express = require('express');
const { default: makeWASocket, DisconnectReason, useMultiFileAuthState, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(express.json());

const PORT = process.env.PORT || 3001;
const AUTH_BASE_DIR = path.join(__dirname, 'auth_info');

// Ensure base auth directory exists
if (!fs.existsSync(AUTH_BASE_DIR)) {
    fs.mkdirSync(AUTH_BASE_DIR, { recursive: true });
}

// Format phone number to WhatsApp JID
function formatJid(phone) {
    let clean = (phone || '').replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    }
    return `${clean}@s.whatsapp.net`;
}

// Sanitize session identifier (prevent directory traversal)
function sanitizeSessionId(id) {
    if (!id || typeof id !== 'string') return 'default';
    const clean = id.replace(/[^a-zA-Z0-9_-]/g, '');
    return clean || 'default';
}

// Cache Baileys version globally to prevent network delay on every session connect
let cachedBaileysVersion = null;
async function getBaileysVersion() {
    if (cachedBaileysVersion) return cachedBaileysVersion;
    try {
        const { version } = await fetchLatestBaileysVersion();
        cachedBaileysVersion = version;
        return cachedBaileysVersion;
    } catch (e) {
        return [2, 3000, 1015901307];
    }
}

/**
 * TenantSession manages an isolated Baileys socket and credentials for a single ISP tenant
 */
class TenantSession {
    constructor(sessionId) {
        this.sessionId = sanitizeSessionId(sessionId);
        this.authDir = path.join(AUTH_BASE_DIR, this.sessionId);
        this.sock = null;
        this.status = 'INITIALIZING'; // 'INITIALIZING' | 'SCAN_QR' | 'CONNECTED' | 'DISCONNECTED'
        this.qrDataUrl = null;
        this.connectedUser = null;
        this.connectedAt = null;
        this.isReconnecting = false;
        this.reconnectTimer = null;
        this.createdAt = new Date().toISOString();

        if (!fs.existsSync(this.authDir)) {
            fs.mkdirSync(this.authDir, { recursive: true });
        }
    }

    async connect() {
        if (this.isReconnecting) return;
        this.isReconnecting = true;

        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }

        try {
            const { state, saveCreds } = await useMultiFileAuthState(this.authDir);
            const version = await getBaileysVersion();

            this.sock = makeWASocket({
                version,
                auth: state,
                logger: pino({ level: 'silent' }),
                browser: ['NetSaz (' + this.sessionId + ')', 'Chrome', '120.0.0.0'],
                connectTimeoutMs: 60000,
                defaultQueryTimeoutMs: 60000,
                keepAliveIntervalMs: 30000,
            });

            this.sock.ev.on('creds.update', saveCreds);

            this.sock.ev.on('connection.update', async (update) => {
                const { connection, lastDisconnect, qr } = update;

                if (qr) {
                    try {
                        this.qrDataUrl = await QRCode.toDataURL(qr, {
                            width: 320,
                            margin: 2,
                            color: { dark: '#0F172A', light: '#FFFFFF' }
                        });
                        this.status = 'SCAN_QR';
                        console.log(`[WA Gateway][${this.sessionId}] QR Code generated, waiting for scan...`);
                    } catch (err) {
                        console.error(`[WA Gateway][${this.sessionId}] QR Error:`, err);
                    }
                }

                if (connection === 'close') {
                    const statusCode = lastDisconnect?.error?.output?.statusCode;
                    const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                    console.log(`[WA Gateway][${this.sessionId}] Connection closed (code: ${statusCode}). Reconnect: ${shouldReconnect}`);

                    this.status = 'DISCONNECTED';
                    this.connectedUser = null;
                    this.connectedAt = null;

                    if (statusCode === 408) {
                        // QR timeout: reconnect immediately (500ms) to generate fresh QR without delay
                        console.log(`[WA Gateway][${this.sessionId}] QR Code timed out, refreshing immediately...`);
                        this.status = 'INITIALIZING';
                        this.qrDataUrl = null;
                        this.isReconnecting = false;
                        this.reconnectTimer = setTimeout(() => this.connect(), 500);
                    } else if (statusCode === DisconnectReason.loggedOut) {
                        console.log(`[WA Gateway][${this.sessionId}] Session logged out. Clearing auth credentials...`);
                        try {
                            fs.rmSync(this.authDir, { recursive: true, force: true });
                            fs.mkdirSync(this.authDir, { recursive: true });
                        } catch (e) {}
                        this.qrDataUrl = null;
                        this.isReconnecting = false;
                        this.reconnectTimer = setTimeout(() => this.connect(), 1000);
                    } else {
                        this.isReconnecting = false;
                        this.reconnectTimer = setTimeout(() => this.connect(), 1500);
                    }
                } else if (connection === 'open') {
                    this.status = 'CONNECTED';
                    this.qrDataUrl = null;
                    this.connectedAt = new Date().toISOString();

                    const jid = this.sock.user?.id || '';
                    const phone = jid.split(':')[0] || jid.split('@')[0];
                    this.connectedUser = {
                        id: jid,
                        phone: phone,
                        name: this.sock.user?.name || `Admin WhatsApp (${this.sessionId})`
                    };

                    console.log(`[WA Gateway][${this.sessionId}] WhatsApp CONNECTED! Phone: ${phone}`);
                    this.isReconnecting = false;
                }
            });
        } catch (err) {
            console.error(`[WA Gateway][${this.sessionId}] Error initializing socket:`, err);
            this.status = 'DISCONNECTED';
            this.isReconnecting = false;
            this.reconnectTimer = setTimeout(() => this.connect(), 5000);
        }
    }

    async sendMessage(targetPhone, messageText) {
        if (this.status !== 'CONNECTED' || !this.sock) {
            throw new Error(`WhatsApp untuk sesi [${this.sessionId}] belum terhubung. Silakan scan QR code di menu Pengaturan.`);
        }
        const jid = formatJid(targetPhone);
        return await this.sock.sendMessage(jid, { text: messageText });
    }

    async logout() {
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }

        try {
            if (this.sock) {
                await this.sock.logout().catch(() => {});
            }
        } catch (e) {}

        try {
            fs.rmSync(this.authDir, { recursive: true, force: true });
            fs.mkdirSync(this.authDir, { recursive: true });
        } catch (e) {}

        this.status = 'INITIALIZING';
        this.qrDataUrl = null;
        this.connectedUser = null;
        this.connectedAt = null;
        this.isReconnecting = false;

        this.reconnectTimer = setTimeout(() => this.connect(), 1000);
    }

    getStatusPayload() {
        return {
            success: true,
            sessionId: this.sessionId,
            status: this.status,
            qr: this.qrDataUrl,
            user: this.connectedUser,
            connected_at: this.connectedAt,
            uptime: Math.floor(process.uptime()),
        };
    }
}

// Global Session Registry
const sessions = new Map();

function getOrCreateSession(rawId) {
    const sessionId = sanitizeSessionId(rawId);
    if (!sessions.has(sessionId)) {
        console.log(`[WA Gateway] Creating new session manager for: ${sessionId}`);
        const session = new TenantSession(sessionId);
        sessions.set(sessionId, session);
        session.connect();
    }
    return sessions.get(sessionId);
}

// Auto-restore any saved sessions on startup
function restoreSavedSessions() {
    try {
        if (!fs.existsSync(AUTH_BASE_DIR)) return;
        const entries = fs.readdirSync(AUTH_BASE_DIR, { withFileTypes: true });
        for (const entry of entries) {
            if (entry.isDirectory()) {
                const credFile = path.join(AUTH_BASE_DIR, entry.name, 'creds.json');
                if (fs.existsSync(credFile)) {
                    console.log(`[WA Gateway] Restoring saved session: ${entry.name}`);
                    getOrCreateSession(entry.name);
                }
            }
        }
    } catch (err) {
        console.error('[WA Gateway] Failed restoring saved sessions:', err);
    }
}

// ---------------- API ENDPOINTS ----------------

// Get Session Status & QR Code (isolated per tenant)
app.get('/status', (req, res) => {
    const sessionId = req.query.sessionId || req.query.session || req.headers['x-session-id'] || 'default';
    const session = getOrCreateSession(sessionId);
    // If disconnected and not reconnecting, kickstart connection immediately
    if (session.status === 'DISCONNECTED' && !session.isReconnecting && !session.reconnectTimer) {
        session.connect();
    }
    res.json(session.getStatusPayload());
});

app.get('/sessions/:session/status', (req, res) => {
    const session = getOrCreateSession(req.params.session);
    res.json(session.getStatusPayload());
});

// List all active sessions (SuperAdmin / diagnostics)
app.get('/sessions', (req, res) => {
    const list = [];
    for (const [id, s] of sessions.entries()) {
        list.push({
            sessionId: id,
            status: s.status,
            user: s.connectedUser,
            connected_at: s.connectedAt,
        });
    }
    res.json({
        success: true,
        count: list.length,
        sessions: list,
    });
});

// Send Message (routed to specific tenant's WhatsApp session)
app.post('/send', async (req, res) => {
    const { sessionId, session, to, phone, message, text } = req.body;
    const targetSessionId = sessionId || session || req.headers['x-session-id'] || 'default';
    const targetPhone = to || phone;
    const targetText = message || text;

    if (!targetPhone || !targetText) {
        return res.status(400).json({
            success: false,
            message: 'Parameter "to" dan "message" wajib diisi.'
        });
    }

    const activeSession = getOrCreateSession(targetSessionId);
    if (activeSession.status !== 'CONNECTED' || !activeSession.sock) {
        return res.status(503).json({
            success: false,
            sessionId: activeSession.sessionId,
            status: activeSession.status,
            message: `WhatsApp untuk ISP ini (${activeSession.sessionId}) belum terhubung. Silakan scan QR Code di Pengaturan terlebih dahulu.`
        });
    }

    try {
        const result = await activeSession.sendMessage(targetPhone, targetText);
        res.json({
            success: true,
            sessionId: activeSession.sessionId,
            message: 'Pesan berhasil terkirim ke WhatsApp.',
            messageId: result?.key?.id,
            to: targetPhone,
            from: activeSession.connectedUser?.phone || null,
        });
    } catch (err) {
        console.error(`[WA Gateway][${targetSessionId}] Send error:`, err);
        res.status(500).json({
            success: false,
            sessionId: activeSession.sessionId,
            message: 'Gagal mengirim pesan WhatsApp: ' + err.message
        });
    }
});

// Open-WA compatibility endpoint: /api/sessions/:session/messages/send-text
app.post('/api/sessions/:session/messages/send-text', async (req, res) => {
    const sessionId = req.params.session;
    const { chatId, text } = req.body;

    if (!chatId || !text) {
        return res.status(400).json({ success: false, message: 'chatId and text required' });
    }

    const activeSession = getOrCreateSession(sessionId);
    if (activeSession.status !== 'CONNECTED' || !activeSession.sock) {
        return res.status(503).json({
            success: false,
            sessionId: activeSession.sessionId,
            status: activeSession.status,
            message: `Sesi ${activeSession.sessionId} belum terhubung.`
        });
    }

    try {
        const phone = chatId.replace('@c.us', '').replace('@s.whatsapp.net', '');
        const result = await activeSession.sendMessage(phone, text);
        res.json({ success: true, messageId: result?.key?.id, sessionId: activeSession.sessionId });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Logout only a specific tenant's session
app.post('/logout', async (req, res) => {
    const sessionId = req.body.sessionId || req.body.session || req.query.sessionId || 'default';
    const activeSession = getOrCreateSession(sessionId);
    await activeSession.logout();

    res.json({
        success: true,
        sessionId: activeSession.sessionId,
        message: `Sesi WhatsApp untuk ${activeSession.sessionId} berhasil direset. Silakan scan QR Code baru.`
    });
});

app.post('/api/sessions/:session/logout', async (req, res) => {
    const activeSession = getOrCreateSession(req.params.session);
    await activeSession.logout();
    res.json({ success: true, sessionId: activeSession.sessionId });
});

// Restart Gateway Server
app.post('/restart', (req, res) => {
    for (const [id, s] of sessions.entries()) {
        try {
            if (s.sock) s.sock.end(undefined);
        } catch (e) {}
    }
    sessions.clear();
    setTimeout(restoreSavedSessions, 1000);
    res.json({ success: true, message: 'Gateway direstart dan semua sesi aktif dimuat ulang.' });
});

app.listen(PORT, '127.0.0.1', () => {
    console.log(`[NetSaz WA Gateway] Multi-tenant server listening on http://127.0.0.1:${PORT}`);
    restoreSavedSessions();
});
