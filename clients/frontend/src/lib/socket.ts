import { io, Socket } from 'socket.io-client';

const SOCKET_URL = process.env.NEXT_PUBLIC_SOCKET_URL || 'https://socket.kyguidatvuon.com';

let socket: Socket | null = null;

export function getSocket(): Socket {
    if (!socket) {
        socket = io(SOCKET_URL, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: 10,
            reconnectionDelay: 2000,
            autoConnect: false,
        });
    }
    return socket;
}

export function connectSocket(userId: number, name: string, token: string): Socket {
    const s = getSocket();

    if (!s.connected) {
        s.connect();
    }

    s.on('connect', () => {
        s.emit('authenticate', {
            userId,
            role: 'user',
            name,
            token,
        });
    });

    // If already connected, authenticate immediately
    if (s.connected) {
        s.emit('authenticate', {
            userId,
            role: 'user',
            name,
            token,
        });
    }

    return s;
}

export function disconnectSocket() {
    if (socket) {
        socket.disconnect();
        socket = null;
    }
}

export default { getSocket, connectSocket, disconnectSocket };
