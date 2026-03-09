const { Server } = require('socket.io');
const { createClient } = require('redis');
const { createAdapter } = require('@socket.io/redis-adapter');
const http = require('http');
const axios = require('axios');

const PORT = process.env.PORT || 3020;
const REDIS_URL = process.env.REDIS_URL || 'redis://redis:6379';
const CORS_ORIGINS = (process.env.CORS_ORIGINS || 'http://localhost:3015,http://localhost:8089').split(',');

const TELEGRAM_BOT_TOKEN = process.env.TELEGRAM_BOT_TOKEN || '6855103341:AAEoQEk3pqczDJ4knFw-Q-WBIDC9uRd4QRA';
const TELEGRAM_CHAT_ID = process.env.TELEGRAM_CHAT_ID || '887533682';

const server = http.createServer((req, res) => {
    // CORS handling for custom API
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    if (req.url === '/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ status: 'ok', timestamp: new Date().toISOString() }));
        return;
    }

    // TELEGRAM WEBHOOK ENDPOINT
    if (req.url === '/telegram-webhook' && req.method === 'POST') {
        let body = '';
        req.on('data', chunk => {
            body += chunk.toString();
        });
        req.on('end', () => {
            try {
                const data = JSON.parse(body);
                handleTelegramWebhook(data);
                res.writeHead(200);
                res.end('OK');
            } catch (err) {
                console.error('Webhook parsing error:', err);
                res.writeHead(400);
                res.end('Bad Request');
            }
        });
        return;
    }

    res.writeHead(404);
    res.end();
});

// Function to process incoming Telegram messages (Admin replied)
function handleTelegramWebhook(data) {
    if (data.message && data.message.reply_to_message && data.message.text) {
        const originalText = data.message.reply_to_message.text;
        const replyText = data.message.text;

        // Lấy đoạn "#ID:xxx" hoặc "ID: xxx"
        const match = originalText.match(/#ID:([a-zA-Z0-9_-]+)/);
        if (match && match[1]) {
            const guestId = match[1];
            // Admin replies to Guest via socket room
            io.to(`guest_room_${guestId}`).emit('telegram_admin_reply', {
                text: replyText,
                timestamp: new Date().toISOString()
            });
            console.log(`✉️ Chuyển tiếp tin nhắn từ Telegram Admin tới Khách ${guestId}`);
        }
    }
}

const io = new Server(server, {
    cors: {
        origin: CORS_ORIGINS,
        methods: ['GET', 'POST'],
        credentials: true
    },
    transports: ['websocket', 'polling']
});

// Redis adapter for horizontal scaling
async function setupRedis() {
    try {
        const pubClient = createClient({ url: REDIS_URL });
        const subClient = pubClient.duplicate();
        await Promise.all([pubClient.connect(), subClient.connect()]);
        io.adapter(createAdapter(pubClient, subClient));
        console.log('✅ Redis adapter connected');
    } catch (err) {
        console.warn('⚠️ Redis adapter failed, using default in-memory adapter:', err.message);
    }
}

// Track online users
const onlineUsers = new Map(); // socketId -> { userId, role, name }
const userSockets = new Map(); // `${role}:${userId}` -> Set<socketId>

function addUserSocket(userId, role, socketId, name) {
    const key = `${role}:${userId}`;
    if (!userSockets.has(key)) {
        userSockets.set(key, new Set());
    }
    userSockets.get(key).add(socketId);
    onlineUsers.set(socketId, { userId, role, name });
}

function removeUserSocket(socketId) {
    const user = onlineUsers.get(socketId);
    if (user) {
        const key = `${user.role}:${user.userId}`;
        const sockets = userSockets.get(key);
        if (sockets) {
            sockets.delete(socketId);
            if (sockets.size === 0) {
                userSockets.delete(key);
            }
        }
        onlineUsers.delete(socketId);
    }
}

function getAdminSockets() {
    const adminSockets = [];
    for (const [key, socketIds] of userSockets.entries()) {
        if (key.startsWith('admin:')) {
            adminSockets.push(...socketIds);
        }
    }
    return adminSockets;
}

// Socket.IO connection handler
io.on('connection', (socket) => {
    console.log(`🔌 Client connected: ${socket.id}`);

    // Authentication - client sends user info after connecting
    socket.on('authenticate', (data) => {
        const { userId, role, name, token } = data;
        if (!userId || !role) {
            socket.emit('auth_error', { message: 'Missing userId or role' });
            return;
        }

        addUserSocket(userId, role, socket.id, name || 'Unknown');
        socket.userId = userId;
        socket.userRole = role;
        socket.userName = name;

        // Admin joins a special admin room
        if (role === 'admin') {
            socket.join('admin_room');
        }

        socket.emit('authenticated', { userId, role });
        console.log(`✅ Authenticated: ${name} (${role}) - ${socket.id}`);
    });

    // Join a ticket room for real-time chat
    socket.on('join_ticket', (data) => {
        const { ticketId } = data;
        if (!ticketId) return;

        const room = `ticket:${ticketId}`;
        socket.join(room);
        console.log(`📋 ${socket.userName || socket.id} joined ${room}`);

        // Notify others in room
        socket.to(room).emit('user_joined', {
            userId: socket.userId,
            name: socket.userName,
            role: socket.userRole
        });
    });

    // Leave a ticket room
    socket.on('leave_ticket', (data) => {
        const { ticketId } = data;
        if (!ticketId) return;

        const room = `ticket:${ticketId}`;
        socket.leave(room);
        console.log(`👋 ${socket.userName || socket.id} left ${room}`);
    });

    // Send a message in a ticket
    socket.on('send_message', (data) => {
        const { ticketId, message, attachments } = data;
        if (!ticketId || !message) return;

        const room = `ticket:${ticketId}`;
        const messageData = {
            ticketId,
            message,
            attachments: attachments || [],
            userId: socket.userId,
            userName: socket.userName,
            role: socket.userRole,
            isAdmin: socket.userRole === 'admin',
            timestamp: new Date().toISOString(),
            tempId: data.tempId // For client-side optimistic updates
        };

        // Broadcast to everyone in the room except the sender
        socket.to(room).emit('new_message', messageData);

        // If user sent the message, also notify all admins
        if (socket.userRole !== 'admin') {
            socket.to('admin_room').emit('ticket_message_notification', {
                ticketId,
                userName: socket.userName,
                preview: message.substring(0, 100)
            });
        }

        console.log(`💬 Message in ticket:${ticketId} from ${socket.userName}`);
    });

    // New ticket created
    socket.on('new_ticket', (data) => {
        const { ticket } = data;
        if (!ticket) return;

        // Notify all admins
        io.to('admin_room').emit('new_ticket_notification', {
            ticket,
            userName: socket.userName,
            userId: socket.userId
        });

        console.log(`🎫 New ticket created by ${socket.userName}`);
    });

    // Ticket status updated
    socket.on('ticket_updated', (data) => {
        const { ticketId, status, updatedBy } = data;
        if (!ticketId) return;

        const room = `ticket:${ticketId}`;
        io.to(room).emit('ticket_status_changed', {
            ticketId,
            status,
            updatedBy: updatedBy || socket.userName,
            role: socket.userRole,
            timestamp: new Date().toISOString()
        });

        // Also notify admin room
        io.to('admin_room').emit('ticket_status_notification', {
            ticketId,
            status,
            updatedBy: updatedBy || socket.userName
        });
    });

    // Typing indicator
    socket.on('typing', (data) => {
        const { ticketId, isTyping } = data;
        if (!ticketId) return;

        const room = `ticket:${ticketId}`;
        socket.to(room).emit('user_typing', {
            userId: socket.userId,
            userName: socket.userName,
            role: socket.userRole,
            isTyping: !!isTyping
        });
    });

    // Get online admins count (for user to know if admin is available)
    socket.on('get_online_admins', () => {
        const adminCount = getAdminSockets().length;
        socket.emit('online_admins_count', { count: adminCount });
    });

    // Disconnect
    socket.on('disconnect', (reason) => {
        console.log(`❌ Client disconnected: ${socket.userName || socket.id} (${reason})`);
        removeUserSocket(socket.id);
    });

    // ============================================
    // TELEGRAM GUEST LIVECHAT
    // ============================================
    socket.on('join_guest_chat', (data) => {
        const guestId = data?.guestId;
        if (guestId) {
            socket.join(`guest_room_${guestId}`);
            console.log(`💬 Khách vãng lai ${guestId} vào phòng chat.`);
        }
    });

    socket.on('guest_message', async (data) => {
        const { guestId, text, platform } = data;
        if (!guestId || !text) return;

        const platformName = platform || 'Sàn Web';
        const messageText = `💬 KHÁCH HÀNG (${platformName}):\n\n${text}\n\n#ID:${guestId}`;

        try {
            await axios.post(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
                chat_id: TELEGRAM_CHAT_ID,
                text: messageText
            });
            // Gửi lại tín hiệu thành công cho Client báo đang rảnh tay
            socket.emit('guest_message_sent', { status: 'success' });
            console.log(`🚀 Đã chuyển tin Khách ${guestId} lên Telegram.`);
        } catch (error) {
            console.error('Telegram send error from socket:', error?.response?.data || error.message);
            socket.emit('guest_message_error', { message: 'Lỗi khi chuyển tin cho Admin.' });
        }
    });
});

// Start server
async function start() {
    await setupRedis();
    server.listen(PORT, () => {
        console.log(`🚀 Socket.IO server running on port ${PORT}`);
        console.log(`📡 CORS origins: ${CORS_ORIGINS.join(', ')}`);
    });
}

start();
