// index.js - Simple WA Bot dengan HTTP API
const makeWASocket = require("@whiskeysockets/baileys").default;
const { useMultiFileAuthState, fetchLatestBaileysVersion, DisconnectReason } = require("@whiskeysockets/baileys");
const qrcodeTerm = require("qrcode-terminal");
const QRCode = require("qrcode");
const P = require("pino");
const express = require('express');
const bodyParser = require('body-parser');

require("dotenv").config();

// ===== Express App untuk HTTP API =====
const app = express();
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Variable global untuk socket WA
let globalSock = null;

// ===== Helpers =====
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const lastSent = new Map();
const MIN_GAP = 2200; // ms minimal jeda antar pesan per JID

// ---- Helper kirim WA =====
async function sendWAMessage(phone, message) {
  if (!globalSock) {
    throw new Error('WhatsApp not connected');
  }
  
  let jid = phone;
  if (!jid.includes('@')) {
    jid = jid.replace(/\D/g, '');
    if (!jid.startsWith('62')) {
      if (jid.startsWith('0')) {
        jid = '62' + jid.substring(1);
      } else if (jid.startsWith('8')) {
        jid = '62' + jid;
      }
    }
    jid = jid + '@s.whatsapp.net';
  }
  
  console.log(`[WA-API] Sending to ${jid}: ${message.substring(0, 50)}...`);
  await globalSock.sendMessage(jid, { text: message });
  
  return { success: true, jid, message: 'Message sent' };
}

// ===== HTTP API Endpoints =====
app.get('/', (req, res) => {
  res.json({ 
    message: 'WhatsApp Bot API is running',
    endpoints: {
      health: '/api/health',
      sendWA: 'POST /api/send-wa'
    }
  });
});

app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    wa_connected: globalSock ? true : false,
    timestamp: new Date().toISOString()
  });
});

app.post('/api/send-wa', async (req, res) => {
  try {
    const { phone, message, nama_akun, email, password, uuid } = req.body;
    
    if (!phone) {
      return res.status(400).json({ 
        success: false, 
        error: 'phone is required' 
      });
    }
    
    if (!message && !nama_akun) {
      return res.status(400).json({ 
        success: false, 
        error: 'either message or nama_akun is required' 
      });
    }
    
    if (!globalSock) {
      return res.status(503).json({ 
        success: false, 
        error: 'WhatsApp not connected. Please scan QR code first.' 
      });
    }
    
    let finalMessage = message || '';
    if (nama_akun && email && password) {
      finalMessage = `*🎉 Akun Panen Poin Telah Dibuat!* 🎉\n\n`;
      finalMessage += `Halo *${nama_akun}*,\n\n`;
      finalMessage += `Akun Panen Poin Anda telah berhasil dibuat!\n\n`;
      finalMessage += `📧 *Email:* ${email}\n`;
      finalMessage += `🔑 *Password:* ${password}\n`;
      if (uuid) {
        finalMessage += `🆔 *UUID:* ${uuid}\n`;
      }
      finalMessage += `\n*🚀 Langkah Selanjutnya:*\n`;
      finalMessage += `1️⃣ Kunjungi: https://panenpoin-myads.com/\n`;
      finalMessage += `2️⃣ Login dengan email & password di atas\n`;
      finalMessage += `3️⃣ Cek saldo poin di dashboard\n`;
      finalMessage += `4️⃣ Tingkatkan transaksi untuk tambah poin\n`;
      finalMessage += `5️⃣ Semakin banyak transaksi = Semakin banyak poin! 💰\n\n`;
      finalMessage += `⚠️ *PENTING:*\n`;
      finalMessage += `• Jangan bagikan password kepada siapapun\n`;
      finalMessage += `• Ubah password setelah login pertama\n\n`;
      finalMessage += `Terima kasih! 🙏`;
    }
    
    const result = await sendWAMessage(phone, finalMessage);
    console.log(`[API] Message sent to ${phone}`);
    res.json(result);
    
  } catch (error) {
    console.error('[API] Error sending message:', error);
    res.status(500).json({ 
      success: false, 
      error: error.message 
    });
  }
});

// Start Express server
const PORT = process.env.WA_BOT_PORT || 3000;
app.listen(PORT, () => {
  console.log(`✅ HTTP API Server running on http://localhost:${PORT}`);
  console.log(`📍 Health check: http://localhost:${PORT}/api/health`);
  console.log(`📍 Send WA: POST http://localhost:${PORT}/api/send-wa`);
});

// ===== WA Boot =====
async function start() {
  const { state, saveCreds } = await useMultiFileAuthState("./auth");
  const { version } = await fetchLatestBaileysVersion();

  const sock = makeWASocket({
    version,
    logger: P({ level: "error" }), // Kurangi log warning
    auth: state,
    syncFullHistory: true, // Sync history untuk koneksi lebih stabil
    retryRequestDelayMs: 10000, // Retry delay 10 detik
    maxMsgsInMemory: 300, // Limit buffer
  });

  globalSock = sock;
  sock.ev.on("creds.update", saveCreds);

  sock.ev.on("connection.update", async ({ connection, lastDisconnect, qr }) => {
    try {
      if (qr) {
        qrcodeTerm.generate(qr, { small: true });
        await QRCode.toFile("qr.png", qr);
        await QRCode.toFile("qr.jpg", qr, { type: "jpeg" });
        console.log("✅ QR disimpan: qr.png & qr.jpg — scan dari HP untuk login");
      }
      if (connection === "open") {
        console.log("✅ WA connected!");
      }
      if (connection === "close") {
        const code = lastDisconnect?.error?.output?.statusCode;
        const shouldReconnect = code !== DisconnectReason.loggedOut && code !== 401;
        console.log("🔌 connection closed:", code, "reconnect:", shouldReconnect);
        if (shouldReconnect) start();
      }
    } catch (e) {
      console.error("connection.update error:", e);
    }
  });

  // Handle incoming messages (optional: just log, don't reply)
  sock.ev.on("messages.upsert", async ({ messages }) => {
    if (!messages || !messages.length) return;
    for (const m of messages) {
      try {
        if (m.key?.fromMe) continue;
        const jid = m.key?.remoteJid;
        const text = m.message?.conversation || m.message?.extendedTextMessage?.text || '';
        console.log(`[📨] Message from ${jid}: ${text}`);
      } catch (e) {
        console.error("message handler error:", e);
      }
    }
  });
}

start().catch(console.error);
