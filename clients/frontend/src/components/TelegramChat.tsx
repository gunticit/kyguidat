'use client';

import React, { useState, useRef, useEffect } from 'react';
import { FaTelegramPlane } from 'react-icons/fa';

export default function TelegramChat() {
    const [isOpen, setIsOpen] = useState(false);
    const [messages, setMessages] = useState<{ id: number; text: string; sender: 'user' | 'bot' }[]>([
        { id: 1, text: 'Chào bạn! Chúng tôi có thể giúp gì cho bạn hôm nay?', sender: 'bot' }
    ]);
    const [inputText, setInputText] = useState('');
    const [isSending, setIsSending] = useState(false);

    // Lưu trữ guestId duy nhất
    const [guestId, setGuestId] = useState('');
    const [socketReady, setSocketReady] = useState(false);
    const socketRef = useRef<any>(null);

    const messagesEndRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        // Init guest session
        let tcwGuestId = localStorage.getItem('tcw_session_id');
        if (!tcwGuestId) {
            tcwGuestId = 'GUEST_' + Math.random().toString(36).substr(2, 9).toUpperCase();
            localStorage.setItem('tcw_session_id', tcwGuestId);
        }
        setGuestId(tcwGuestId);

        // Dynamically import socket.io-client to avoid SSR issues
        import('socket.io-client').then(({ io }) => {
            const socketUrl = process.env.NEXT_PUBLIC_SOCKET_URL || 'https://socket.khodat.com';
            const socket = io(socketUrl, {
                transports: ['websocket', 'polling']
            });

            socketRef.current = socket;

            socket.on('connect', () => {
                setSocketReady(true);
                socket.emit('join_guest_chat', { guestId: tcwGuestId });
            });

            socket.on('telegram_admin_reply', (data: any) => {
                setMessages(prev => [...prev, { id: Date.now(), text: data.text, sender: 'bot' }]);

                // Show floating bounce if closed
                setIsOpen(currentIsOpen => {
                    return currentIsOpen;
                });
            });

            socket.on('guest_message_sent', () => {
                setIsSending(false);
                if (inputRef.current) inputRef.current.focus();
            });

            socket.on('guest_message_error', (data: any) => {
                setMessages(prev => [...prev, { id: Date.now(), text: "Hệ thống gián đoạn: " + (data.message || 'Lỗi gửi tin lên Telegram'), sender: 'bot' }]);
                setIsSending(false);
            });

            socket.on('disconnect', () => {
                setSocketReady(false);
            });

            return () => {
                socket.disconnect();
            };
        });
    }, []);

    useEffect(() => {
        if (isOpen && messagesEndRef.current) {
            messagesEndRef.current.scrollIntoView({ behavior: 'smooth' });
        }
        if (isOpen && inputRef.current) {
            inputRef.current.focus();
        }
    }, [isOpen, messages]);

    const handleSend = async (e: React.FormEvent) => {
        e.preventDefault();
        const text = inputText.trim();
        if (!text || isSending || !socketRef.current || !socketReady) return;

        // User message
        const newMsgId = Date.now();
        setMessages(prev => [...prev, { id: newMsgId, text, sender: 'user' }]);
        setInputText('');
        setIsSending(true);

        socketRef.current.emit('guest_message', {
            guestId,
            text: text,
            platform: 'App Next.js',
        });

        // Timeout unlock in case socket hangs
        setTimeout(() => setIsSending(false), 5000);
    };

    return (
        <div style={{
            position: 'fixed',
            bottom: '24px',
            right: '24px',
            zIndex: 9999,
            fontFamily: "'Inter', sans-serif"
        }}>
            {/* Toggle Button */}
            {!isOpen && (
                <button
                    onClick={() => setIsOpen(true)}
                    style={{
                        width: '56px',
                        height: '56px',
                        backgroundColor: '#22c55e',
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: 'white',
                        boxShadow: '0 4px 12px rgba(34,197,94,0.4)',
                        border: 'none',
                        cursor: 'pointer',
                        transition: 'transform 0.2s ease'
                    }}
                    onMouseOver={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                    onMouseOut={(e) => e.currentTarget.style.transform = 'scale(1)'}
                >
                    <FaTelegramPlane size={24} style={{ marginRight: '2px', marginTop: '2px' }} />
                </button>
            )}

            {/* Chat Box */}
            <div
                style={{
                    display: isOpen ? 'flex' : 'none',
                    flexDirection: 'column',
                    position: 'absolute',
                    bottom: '0',
                    right: '0',
                    width: '320px',
                    backgroundColor: 'var(--card, #ffffff)',
                    borderRadius: '16px',
                    boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                    border: '1px solid var(--border, #e2e8f0)',
                    overflow: 'hidden',
                    transformOrigin: 'bottom right',
                    transition: 'all 0.3s ease',
                    opacity: isOpen ? 1 : 0,
                    transform: isOpen ? 'scale(1)' : 'scale(0.95)'
                }}
            >
                {/* Header */}
                <div style={{
                    background: 'linear-gradient(to right, #22c55e, #16a34a)',
                    padding: '16px',
                    color: 'white',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center'
                }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <div style={{
                            width: '40px',
                            height: '40px',
                            backgroundColor: 'rgba(255,255,255,0.2)',
                            borderRadius: '50%',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                        }}>
                            <FaTelegramPlane size={20} />
                        </div>
                        <div>
                            <h3 style={{ margin: 0, fontSize: '14px', fontWeight: 'bold' }}>Hỗ Trợ Trực Tuyến</h3>
                            <p style={{ margin: '2px 0 0 0', fontSize: '12px', color: '#dcfce7', display: 'flex', alignItems: 'center', gap: '4px' }}>
                                <span style={{ width: '8px', height: '8px', backgroundColor: '#86efac', borderRadius: '50%' }}></span>
                                Đang hoạt động
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={() => setIsOpen(false)}
                        style={{
                            background: 'none', border: 'none', color: 'white', cursor: 'pointer', padding: '4px'
                        }}
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                {/* Messages */}
                <div style={{
                    height: '280px',
                    padding: '16px',
                    overflowY: 'auto',
                    backgroundColor: 'var(--background, #f8fafc)',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '12px'
                }}>
                    {messages.map((msg) => (
                        <div key={msg.id} style={{
                            display: 'flex',
                            alignItems: 'flex-end',
                            gap: '8px',
                            width: '100%',
                            justifyContent: msg.sender === 'user' ? 'flex-end' : 'flex-start',
                            paddingLeft: msg.sender === 'user' ? '24px' : '0',
                            paddingRight: msg.sender === 'user' ? '0' : '24px'
                        }}>
                            {msg.sender === 'bot' && (
                                <div style={{
                                    width: '32px',
                                    height: '32px',
                                    borderRadius: '50%',
                                    backgroundColor: '#dcfce7',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    flexShrink: 0,
                                    color: '#16a34a'
                                }}>
                                    <FaTelegramPlane size={14} />
                                </div>
                            )}
                            <div style={{
                                padding: '12px',
                                fontSize: '14px',
                                whiteSpace: 'pre-wrap',
                                wordWrap: 'break-word',
                                backgroundColor: msg.sender === 'user' ? '#22c55e' : 'var(--card, #ffffff)',
                                color: msg.sender === 'user' ? 'white' : 'var(--text, #1e293b)',
                                borderRadius: '16px',
                                borderBottomRightRadius: msg.sender === 'user' ? '4px' : '16px',
                                borderBottomLeftRadius: msg.sender === 'user' ? '16px' : '4px',
                                boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
                                border: msg.sender === 'bot' ? '1px solid var(--border, #f1f5f9)' : 'none'
                            }}>
                                {msg.text}
                            </div>
                        </div>
                    ))}
                    <div ref={messagesEndRef} />
                </div>

                {/* Input Area */}
                <div style={{
                    padding: '12px',
                    backgroundColor: 'var(--card, #ffffff)',
                    borderTop: '1px solid var(--border, #f1f5f9)'
                }}>
                    <style>
                        {`
                            .tcw-app-input {
                                color: #000 !important;
                            }
                            [data-theme="dark"] .tcw-app-input {
                                color: #fff !important;
                            }
                        `}
                    </style>
                    <form onSubmit={handleSend} style={{ position: 'relative', display: 'flex', alignItems: 'center' }}>
                        <input
                            ref={inputRef}
                            type="text"
                            value={inputText}
                            onChange={(e) => setInputText(e.target.value)}
                            placeholder="Nhập tin nhắn..."
                            disabled={isSending}
                            className="tcw-app-input"
                            style={{
                                width: '100%',
                                backgroundColor: 'var(--background, #f1f5f9)',
                                fontSize: '14px',
                                borderRadius: '9999px',
                                padding: '10px 48px 10px 16px',
                                border: '1px solid var(--border, transparent)',
                                outline: 'none',
                                opacity: isSending ? 0.7 : 1
                            }}
                        />
                        <button
                            type="submit"
                            disabled={isSending || !inputText.trim()}
                            style={{
                                position: 'absolute',
                                right: '4px',
                                width: '32px',
                                height: '32px',
                                backgroundColor: (isSending || !inputText.trim()) ? '#9ca3af' : '#22c55e',
                                color: 'white',
                                borderRadius: '50%',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                border: 'none',
                                cursor: (isSending || !inputText.trim()) ? 'not-allowed' : 'pointer'
                            }}
                        >
                            <FaTelegramPlane size={14} style={{ marginLeft: '2px', marginTop: '2px' }} />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
};
