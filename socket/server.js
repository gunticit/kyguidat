const { Server } = require('socket.io');
const { createClient } = require('redis');
const { createAdapter } = require('@socket.io/redis-adapter');
const http = require('http');

const PORT = process.env.PORT || 3020;
const REDIS_URL = process.env.REDIS_URL || 'redis://redis:6379';
const CORS_ORIGINS = (process.env.CORS_ORIGINS || 'http://localhost:3015,http://localhost:8089').split(',');

const server = http.createServer((req, res) => {
    if (req.url === '/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ status: 'ok', timestamp: new Date().toISOString() }));
        return;
    }
    res.writeHead(404);
    res.end();
});

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
