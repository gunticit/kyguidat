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

    const messagesEndRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    const TELEGRAM_BOT_TOKEN = "6855103341:AAEoQEk3pqczDJ4knFw-Q-WBIDC9uRd4QRA";
    const TELEGRAM_CHAT_ID = "887533682"; // Thay bằng Chat ID thật

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
        if (!text || isSending) return;

        // User message
        const newMsgId = Date.now();
        setMessages(prev => [...prev, { id: newMsgId, text, sender: 'user' }]);
        setInputText('');
        setIsSending(true);

        try {
            const url = `https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`;
            const payload = {
                chat_id: TELEGRAM_CHAT_ID,
                text: `💬 Tin nhắn KHÁCH HÀNG (App Khodat):\n\n${text}`
            };

            const res = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.ok) {
                setMessages(prev => [...prev, { id: Date.now() + 1, text: "Cảm ơn bạn! Chúng tôi đã nhận được tin nhắn và sẽ phản hồi sớm nhất.", sender: 'bot' }]);
            } else {
                throw new Error(data.description || "Lỗi khi gửi");
            }
        } catch (error) {
            console.error("Telegram send error:", error);
            setMessages(prev => [...prev, { id: Date.now() + 2, text: "Xin lỗi, hệ thống bị lỗi khi gửi tin nhắn. (Lưu ý Admin: Vui lòng thay nội dung YOUR_CHAT_ID_HERE bằng Chat ID thực)", sender: 'bot' }]);
        } finally {
            setIsSending(false);
        }
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
