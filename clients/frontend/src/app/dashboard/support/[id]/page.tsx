'use client';

import { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { FiArrowLeft, FiSend, FiX } from 'react-icons/fi';
import { supportApi } from '@/lib/api';
import { getSocket, connectSocket, disconnectSocket } from '@/lib/socket';
import styles from './detail.module.css';

interface Message {
    id: number;
    message: string;
    is_admin: boolean;
    created_at: string;
    user?: { id: number; name: string; avatar?: string };
    attachments?: string[];
}

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    status: string;
    priority: string;
    created_at: string;
    messages?: Message[];
}

export default function SupportDetailPage() {
    const params = useParams();
    const router = useRouter();
    const ticketId = Number(params.id);

    const [ticket, setTicket] = useState<Ticket | null>(null);
    const [messages, setMessages] = useState<Message[]>([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(true);
    const [sending, setSending] = useState(false);
    const [isAdminTyping, setIsAdminTyping] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const typingTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const socketRef = useRef<ReturnType<typeof getSocket> | null>(null);

    const scrollToBottom = useCallback(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, []);

    // Load ticket data
    useEffect(() => {
        if (!ticketId) return;

        const loadTicket = async () => {
            try {
                setLoading(true);
                const response = await supportApi.getById(ticketId);
                if (response.data.success) {
                    const data = response.data.data;
                    setTicket(data);
                    setMessages(data.messages || []);
                }
            } catch (error) {
                console.error('Error loading ticket:', error);
            } finally {
                setLoading(false);
            }
        };

        loadTicket();
    }, [ticketId]);

    // Socket setup
    useEffect(() => {
        if (!ticketId || !ticket) return;

        const token = localStorage.getItem('auth_token');
        const userStr = localStorage.getItem('user');
        if (!token || !userStr) return;

        let user: { id: number; name: string };
        try {
            user = JSON.parse(userStr);
        } catch {
            return;
        }

        const socket = connectSocket(user.id, user.name, token);
        socketRef.current = socket;

        // Join ticket room
        socket.emit('join_ticket', { ticketId });

        // Listen for new messages
        const handleNewMessage = (data: {
            ticketId: number;
            message: string;
            userId: number;
            userName: string;
            isAdmin: boolean;
            timestamp: string;
        }) => {
            if (data.ticketId === ticketId) {
                setMessages(prev => {
                    // Avoid duplicates
                    const exists = prev.some(m =>
                        m.message === data.message &&
                        Math.abs(new Date(m.created_at).getTime() - new Date(data.timestamp).getTime()) < 5000
                    );
                    if (exists) return prev;

                    return [...prev, {
                        id: Date.now(),
                        message: data.message,
                        is_admin: data.isAdmin,
                        created_at: data.timestamp,
                        user: { id: data.userId, name: data.userName },
                    }];
                });
                setTimeout(scrollToBottom, 100);
            }
        };

        // Typing indicator
        const handleTyping = (data: { role: string; isTyping: boolean }) => {
            if (data.role === 'admin') {
                setIsAdminTyping(data.isTyping);
                if (data.isTyping) {
                    if (typingTimeoutRef.current) clearTimeout(typingTimeoutRef.current);
                    typingTimeoutRef.current = setTimeout(() => setIsAdminTyping(false), 3000);
                }
            }
        };

        // Status change
        const handleStatusChange = (data: { ticketId: number; status: string }) => {
            if (data.ticketId === ticketId) {
                setTicket(prev => prev ? { ...prev, status: data.status } : prev);
            }
        };

        socket.on('new_message', handleNewMessage);
        socket.on('user_typing', handleTyping);
        socket.on('ticket_status_changed', handleStatusChange);

        return () => {
            socket.emit('leave_ticket', { ticketId });
            socket.off('new_message', handleNewMessage);
            socket.off('user_typing', handleTyping);
            socket.off('ticket_status_changed', handleStatusChange);
        };
    }, [ticketId, ticket, scrollToBottom]);

    // Scroll to bottom when messages change
    useEffect(() => {
        scrollToBottom();
    }, [messages, scrollToBottom]);

    const sendMessage = async () => {
        if (!newMessage.trim() || sending || !ticket) return;

        const messageText = newMessage.trim();
        setNewMessage('');
        setSending(true);

        try {
            const response = await supportApi.addMessage(ticketId, { message: messageText });
            if (response.data.success) {
                const newMsg = response.data.data;
                setMessages(prev => [...prev, newMsg]);

                // Emit via socket for real-time delivery to admin
                if (socketRef.current) {
                    socketRef.current.emit('send_message', {
                        ticketId,
                        message: messageText,
                        tempId: newMsg.id,
                    });
                }
            }
        } catch (error) {
            console.error('Error sending message:', error);
            setNewMessage(messageText); // Restore message on error
        } finally {
            setSending(false);
        }
    };

    const handleTyping = () => {
        if (socketRef.current && ticketId) {
            socketRef.current.emit('typing', { ticketId, isTyping: true });
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    const formatTime = (dateStr: string) => {
        return new Date(dateStr).toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const formatDate = (dateStr: string) => {
        return new Date(dateStr).toLocaleDateString('vi-VN');
    };

    const getStatusLabel = (status: string) => {
        const labels: Record<string, string> = {
            open: 'Đang mở',
            in_progress: 'Đang xử lý',
            waiting_reply: 'Chờ phản hồi',
            resolved: 'Đã giải quyết',
            closed: 'Đã đóng',
        };
        return labels[status] || status;
    };

    const getStatusClass = (status: string) => {
        const classes: Record<string, string> = {
            open: styles.statusOpen,
            in_progress: styles.statusProgress,
            waiting_reply: styles.statusWaiting,
            resolved: styles.statusResolved,
            closed: styles.statusClosed,
        };
        return classes[status] || '';
    };

    if (loading) {
        return (
            <div className={styles.loading}>
                <div className={styles.spinner}></div>
                <p>Đang tải...</p>
            </div>
        );
    }

    if (!ticket) {
        return (
            <div className={styles.loading}>
                <p>Không tìm thấy yêu cầu hỗ trợ</p>
                <button className={styles.backBtn} onClick={() => router.push('/dashboard/support')}>
                    <FiArrowLeft /> Quay lại
                </button>
            </div>
        );
    }

    return (
        <div className={styles.container}>
            {/* Header */}
            <div className={styles.header}>
                <div className={styles.headerLeft}>
                    <button
                        className={styles.backBtn}
                        onClick={() => router.push('/dashboard/support')}
                    >
                        <FiArrowLeft />
                    </button>
                    <div>
                        <h1 className={styles.ticketSubject}>{ticket.subject}</h1>
                        <div className={styles.ticketInfo}>
                            <span className={styles.ticketNumber}>{ticket.ticket_number}</span>
                            <span className={`${styles.status} ${getStatusClass(ticket.status)}`}>
                                {getStatusLabel(ticket.status)}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Messages */}
            <div className={styles.messagesContainer}>
                <div className={styles.messagesList}>
                    {messages.map((msg) => (
                        <div
                            key={msg.id}
                            className={`${styles.message} ${msg.is_admin ? styles.messageAdmin : styles.messageUser}`}
                        >
                            <div className={styles.messageBubble}>
                                {msg.is_admin && (
                                    <span className={styles.senderName}>
                                        {msg.user?.name || 'Admin'}
                                    </span>
                                )}
                                <p className={styles.messageText}>{msg.message}</p>
                                <span className={styles.messageTime}>{formatTime(msg.created_at)}</span>
                            </div>
                        </div>
                    ))}

                    {isAdminTyping && (
                        <div className={`${styles.message} ${styles.messageAdmin}`}>
                            <div className={`${styles.messageBubble} ${styles.typingBubble}`}>
                                <div className={styles.typingDots}>
                                    <span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    )}

                    <div ref={messagesEndRef} />
                </div>
            </div>

            {/* Input */}
            {ticket.status !== 'closed' ? (
                <div className={styles.inputContainer}>
                    <textarea
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        onKeyDown={handleKeyDown}
                        onInput={handleTyping}
                        placeholder="Nhập tin nhắn..."
                        rows={1}
                        className={styles.messageInput}
                    />
                    <button
                        className={styles.sendBtn}
                        onClick={sendMessage}
                        disabled={!newMessage.trim() || sending}
                    >
                        <FiSend />
                    </button>
                </div>
            ) : (
                <div className={styles.closedNotice}>
                    Yêu cầu hỗ trợ này đã được đóng
                </div>
            )}
        </div>
    );
}
