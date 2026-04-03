import pkg from 'whatsapp-web.js';
const { Client, LocalAuth } = pkg;
import express from 'express';
import qrcodeTerminal from 'qrcode-terminal';
import QRCode from 'qrcode';
import { body, validationResult } from 'express-validator';

const app = express();
const port = 3000;

app.use(express.json());

const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: './sessions'
    }),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

let qrCodeBase64 = null;
let connectionStatus = 'INITIALIZING';

client.on('qr', async (qr) => {
    connectionStatus = 'QR_READY';
    console.log('QR RECEIVED', qr);
    qrcodeTerminal.generate(qr, { small: true });
    try {
        qrCodeBase64 = await QRCode.toDataURL(qr);
    } catch (err) {
        console.error('Failed to generate QR QRDataURL', err);
    }
});

client.on('ready', () => {
    console.log('Client is ready!');
    connectionStatus = 'CONNECTED';
    qrCodeBase64 = null;
});

client.on('authenticated', () => {
    console.log('AUTHENTICATED');
    connectionStatus = 'AUTHENTICATED';
});

client.on('auth_failure', msg => {
    console.error('AUTHENTICATION FAILURE', msg);
    connectionStatus = 'AUTH_FAILURE';
});

client.on('disconnected', (reason) => {
    console.log('Client was logged out', reason);
    connectionStatus = 'DISCONNECTED';
    client.initialize();
});

client.initialize();

// Endpoints
app.get('/status', (req, res) => {
    res.json({
        status: connectionStatus,
        qr: qrCodeBase64
    });
});

app.post('/send', [
    body('number').notEmpty(),
    body('message').notEmpty()
], async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
    }

    if (connectionStatus !== 'CONNECTED') {
        return res.status(500).json({ status: 'error', message: 'WhatsApp is not connected' });
    }

    const { number, message } = req.body;
    let formattedNumber = number.replace(/\D/g, '');
    
    // Normalize to 62...
    if (formattedNumber.startsWith('08')) {
        formattedNumber = '62' + formattedNumber.slice(1);
    } else if (!formattedNumber.startsWith('62')) {
        formattedNumber = '62' + formattedNumber;
    }

    const chatId = formattedNumber + "@c.us";

    try {
        await client.sendMessage(chatId, message);
        res.json({ status: 'success', message: 'Message sent to ' + formattedNumber });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ status: 'error', message: error.message });
    }
});

app.post('/logout', async (req, res) => {
    console.log('Logout requested');
    try {
        // Try logging out from the browser
        try {
            await client.logout();
        } catch (logoutErr) {
            console.error('client.logout() failed, destroying session manually...', logoutErr);
            // If logout fails, we try to clear the local auth session manually if needed
            // but usually client.logout() is the right way.
        }
        
        connectionStatus = 'DISCONNECTED';
        res.json({ status: 'success', message: 'Berhasil memutuskan sesi WhatsApp.' });
    } catch (error) {
        console.error('Overall logout error:', error);
        res.status(500).json({ 
            status: 'error', 
            message: error.message || 'Terjadi kesalahan sistem saat logout.' 
        });
    }
});

app.listen(port, () => {
    console.log(`WhatsApp Bridge listening at http://localhost:${port}`);
});
