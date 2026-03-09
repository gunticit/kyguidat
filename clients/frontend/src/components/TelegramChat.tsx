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
        <div className="fixed bottom-24 right-4 z-[9999] md:bottom-6 md:right-6 font-sans">
            {/* Toggle Button */}
            {!isOpen && (
                <button
                    onClick={() => setIsOpen(true)}
                    className="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-[0_4px_12px_rgba(34,197,94,0.4)] hover:bg-green-600 transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-green-500/30"
                >
                    <FaTelegramPlane size={24} className="mr-1 mt-1" />
                </button>
            )}

            {/* Chat Box */}
            <div
                className={`flex flex-col absolute bottom-0 right-0 w-[320px] md:w-[360px] bg-white dark:bg-[#111827] rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden transform transition-all origin-bottom-right ${isOpen ? 'scale-100 opacity-100' : 'scale-95 opacity-0 pointer-events-none'
                    }`}
            >
                {/* Header */}
                <div className="bg-gradient-to-r from-green-500 to-green-600 p-4 text-white flex justify-between items-center">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <FaTelegramPlane size={20} />
                        </div>
                        <div>
                            <h3 className="font-bold text-sm m-0">Hỗ Trợ Trực Tuyến</h3>
                            <p className="text-xs text-green-100 flex items-center gap-1 m-0 mt-0.5">
                                <span className="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                                Đang hoạt động
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={() => setIsOpen(false)}
                        className="text-white hover:text-gray-200 transition focus:outline-none"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                {/* Messages */}
                <div className="h-72 p-4 overflow-y-auto bg-gray-50 dark:bg-[#0b1121] flex flex-col gap-3">
                    {messages.map((msg) => (
                        <div key={msg.id} className={`flex items-end gap-2 w-full ${msg.sender === 'user' ? 'justify-end pl-6' : 'pr-6'}`}>
                            {msg.sender === 'bot' && (
                                <div className="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center flex-shrink-0 text-green-600 dark:text-green-400">
                                    <FaTelegramPlane size={14} />
                                </div>
                            )}
                            <div
                                className={`p-3 rounded-2xl shadow-sm text-sm ${msg.sender === 'user'
                                    ? 'bg-green-500 text-white rounded-br-sm'
                                    : 'bg-white dark:bg-[#1a2332] text-gray-700 dark:text-gray-200 border border-gray-100 dark:border-gray-800 rounded-bl-sm'
                                    }`}
                            >
                                {msg.text}
                            </div>
                        </div>
                    ))}
                    <div ref={messagesEndRef} />
                </div>

                {/* Input Area */}
                <div className="p-3 bg-white dark:bg-[#111827] border-t border-gray-100 dark:border-gray-800">
                    <form onSubmit={handleSend} className="relative flex items-center">
                        <input
                            ref={inputRef}
                            type="text"
                            value={inputText}
                            onChange={(e) => setInputText(e.target.value)}
                            placeholder="Nhập tin nhắn..."
                            disabled={isSending}
                            className="w-full bg-gray-100 dark:bg-[#1a2332] text-gray-800 dark:text-white text-sm rounded-full py-2.5 pl-4 pr-12 focus:outline-none focus:ring-2 focus:ring-green-500/50 border border-gray-200 dark:border-gray-700 transition-all disabled:opacity-70"
                        />
                        <button
                            type="submit"
                            disabled={isSending || !inputText.trim()}
                            className="absolute right-1 w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500/50 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            <FaTelegramPlane size={14} className="ml-0.5 mt-0.5" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
