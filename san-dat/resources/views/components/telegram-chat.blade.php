<!-- Telegram Chat Widget -->
<style>
    /* Chat widget theme-aware styles */
    #tcwChatBox {
        background: #111827;
        border-color: #1e293b;
    }

    #tcwMessages {
        background: #0b1121;
    }

    .tcw-msg-bubble {
        background: #1a2332;
        border-color: #1e293b;
        color: #e5e7eb;
    }

    .tcw-msg-avatar {
        background: #064e3b;
        color: #4ade80;
    }

    .tcw-input-area {
        background: #111827;
        border-color: #1e293b;
    }

    #tcwInput {
        background: #1a2332 !important;
        color: #f3f4f6 !important;
        border-color: #1e293b !important;
    }

    #tcwInput::placeholder {
        color: #6b7280 !important;
    }

    .tcw-powered {
        color: #6b7280;
    }

    /* Light mode overrides */
    [data-theme="day"] #tcwChatBox {
        background: #ffffff;
        border-color: #e2e8f0;
    }

    [data-theme="day"] #tcwMessages {
        background: #f8fafc;
    }

    [data-theme="day"] .tcw-msg-bubble {
        background: #ffffff;
        border-color: #e2e8f0;
        color: #334155;
    }

    [data-theme="day"] .tcw-msg-avatar {
        background: #dcfce7;
        color: #16a34a;
    }

    [data-theme="day"] .tcw-input-area {
        background: #ffffff;
        border-color: #e2e8f0;
    }

    [data-theme="day"] #tcwInput {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #e2e8f0 !important;
    }

    [data-theme="day"] #tcwInput::placeholder {
        color: #94a3b8 !important;
    }

    [data-theme="day"] .tcw-powered {
        color: #94a3b8;
    }
</style>
<div id="telegramChatWidget" class="fixed bottom-24 right-4 z-[9999] md:bottom-6 md:right-6 font-sans">

    <!-- Chat Icon -->
    <button id="tcwToggleBtn" onclick="tcwToggleChat()"
        class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-[0_4px_12px_rgba(34,197,94,0.4)] hover:bg-green-600 transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-green-500/30">
        <img src="{{ asset('images/ai-icon-chat.png') }}" alt="AI Chat" class="w-full h-full object-cover rounded-full">
    </button>

    <!-- Chat Box -->
    <div id="tcwChatBox"
        class="hidden absolute bottom-16 right-0 w-80 md:w-96 rounded-2xl shadow-2xl border overflow-hidden transform transition-all origin-bottom-right scale-95 opacity-0">

        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a10 10 0 1 0 10 10H12V2z" />
                        <path d="M12 12V2a10 10 0 1 1-10 10h10z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm">Hỗ Trợ Trực Tuyến</h3>
                    <p class="text-xs text-green-100 flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                        Đang hoạt động. Nhắn để được hỗ trợ!
                    </p>
                </div>
            </div>
            <button onclick="tcwToggleChat()" class="text-white hover:text-gray-200 transition focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="tcwMessages" class="h-64 sm:h-72 p-4 overflow-y-auto flex flex-col gap-3">
            <div class="flex items-end gap-2 pr-6">
                <div class="w-8 h-8 rounded-full tcw-msg-avatar flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg>
                </div>
                <div class="tcw-msg-bubble p-3 rounded-2xl rounded-bl-sm shadow-sm border">
                    <p class="text-sm">Chào bạn! Chúng tôi có thể giúp gì cho bạn hôm nay?</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 tcw-input-area border-t">
            <form id="tcwForm" onsubmit="tcwSendMessage(event)" class="relative flex items-center">
                <input type="text" id="tcwInput" autocomplete="off" placeholder="Nhập tin nhắn..."
                    class="w-full text-sm rounded-full py-2.5 pl-4 pr-12 focus:outline-none focus:ring-2 focus:ring-green-500/50 border transition-all"
                    required>
                <button type="submit" id="tcwSendBtn"
                    class="absolute right-1 w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
            <div class="mt-2 text-center">
                <span class="text-[10px] tcw-powered">Powered by Khodat</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.4/socket.io.min.js"></script>
<script>
    let tcwIsOpen = false;
    let tcwSocket = null;
    let tcwGuestId = localStorage.getItem('tcw_session_id');

    if (!tcwGuestId) {
        tcwGuestId = 'GUEST_' + Math.random().toString(36).substr(2, 9).toUpperCase();
        localStorage.setItem('tcw_session_id', tcwGuestId);
    }

    function initTcwSocket() {
        if (tcwSocket) return;

        tcwSocket = io("https://socket.khodat.com", {
            transports: ['websocket', 'polling']
        });

        tcwSocket.on('connect', () => {
            console.log('🔗 Connected to Live Chat Server');
            tcwSocket.emit('join_guest_chat', { guestId: tcwGuestId });
        });

        tcwSocket.on('telegram_admin_reply', (data) => {
            tcwRenderMsg(data.text, 'bot');

            // Auto open box when unread message come
            if (!tcwIsOpen) {
                const btn = document.getElementById('tcwToggleBtn');
                btn.classList.add('animate-bounce');
                setTimeout(() => btn.classList.remove('animate-bounce'), 3000);
            }
        });

        tcwSocket.on('guest_message_sent', () => {
            const input = document.getElementById('tcwInput');
            input.disabled = false;
            input.focus();
        });

        tcwSocket.on('guest_message_error', (data) => {
            tcwRenderMsg("Hệ thống gián đoạn: " + (data.message || 'Lỗi gửi tin lên Telegram'), 'bot');
            const input = document.getElementById('tcwInput');
            input.disabled = false;
        });
    }

    function tcwToggleChat() {
        if (!tcwSocket) {
            initTcwSocket();
        }

        const chatBox = document.getElementById('tcwChatBox');
        if (tcwIsOpen) {
            chatBox.classList.replace('scale-100', 'scale-95');
            chatBox.classList.replace('opacity-100', 'opacity-0');
            setTimeout(() => { chatBox.classList.add('hidden'); chatBox.classList.remove('flex', 'flex-col'); }, 200);
            tcwIsOpen = false;
        } else {
            chatBox.classList.remove('hidden');
            chatBox.classList.add('flex', 'flex-col');
            setTimeout(() => {
                chatBox.classList.replace('scale-95', 'scale-100');
                chatBox.classList.replace('opacity-0', 'opacity-100');
                document.getElementById('tcwInput').focus();
            }, 10);
            tcwIsOpen = true;
        }
    }

    function tcwSendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('tcwInput');
        const text = input.value.trim();
        if (!text) return;

        // Bật connect nếu user Enter mà box lỗi ko tự connect
        if (!tcwSocket || !tcwSocket.connected) initTcwSocket();

        tcwRenderMsg(text, 'user');
        input.value = '';
        input.disabled = true;

        tcwSocket.emit('guest_message', {
            guestId: tcwGuestId,
            text: text,
            platform: 'Sàn Web',
        });

        // Timeout unlock in case socket hangs
        setTimeout(() => { if (input.disabled) input.disabled = false; }, 5000);
    }

    function tcwRenderMsg(text, sender) {
        const msgs = document.getElementById('tcwMessages');
        let html = '';
        if (sender === 'user') {
            html = `
            <div class="flex items-end justify-end gap-2 pl-6 mb-2">
                <div class="bg-green-500 text-white p-3 rounded-2xl rounded-br-sm shadow-sm">
                    <p class="text-sm" style="white-space: pre-wrap; word-wrap: break-word;">${text}</p>
                </div>
            </div>`;
        } else {
            html = `
            <div class="flex items-end gap-2 pr-6 mb-2">
                <div class="w-8 h-8 rounded-full tcw-msg-avatar flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                </div>
                <div class="tcw-msg-bubble border p-3 rounded-2xl rounded-bl-sm shadow-sm">
                    <p class="text-sm" style="white-space: pre-wrap; word-wrap: break-word;">${text}</p>
                </div>
            </div>`;
        }
        msgs.insertAdjacentHTML('beforeend', html);
        msgs.scrollTop = msgs.scrollHeight;
    }
</script>