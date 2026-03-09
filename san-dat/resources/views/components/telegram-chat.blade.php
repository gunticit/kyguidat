<!-- Telegram Chat Widget -->
<div id="telegramChatWidget" class="fixed bottom-24 right-4 z-[9999] md:bottom-6 md:right-6 font-sans">

    <!-- Chat Icon -->
    <button id="tcwToggleBtn" onclick="tcwToggleChat()"
        class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-[0_4px_12px_rgba(34,197,94,0.4)] hover:bg-green-600 transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-green-500/30">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path
                d="M21.198 2.007L1.83 9.421c-1.107.424-1.127 1.936-.027 2.387l5.448 2.228M21.198 2.007L16.48 21.09c-.218.868-1.293 1.15-1.928.5L9.624 16.7M21.198 2.007L9.623 16.7m0 0l-2.373 4.88c-.378.777-1.503.744-1.834-.055l-2.046-4.834" />
        </svg>
    </button>

    <!-- Chat Box -->
    <div id="tcwChatBox"
        class="hidden absolute bottom-16 right-0 w-80 md:w-96 bg-white dark:bg-navy-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-navy-600 overflow-hidden transform transition-all origin-bottom-right scale-95 opacity-0">

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
        <div id="tcwMessages" class="h-64 sm:h-72 p-4 overflow-y-auto bg-gray-50 dark:bg-navy-900 flex flex-col gap-3">
            <div class="flex items-end gap-2 pr-6">
                <div
                    class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center flex-shrink-0 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg>
                </div>
                <div
                    class="bg-white dark:bg-navy-700 p-3 rounded-2xl rounded-bl-sm shadow-sm border border-gray-100 dark:border-navy-600">
                    <p class="text-sm text-gray-700 dark:text-gray-200">Chào bạn! Chúng tôi có thể giúp gì cho bạn hôm
                        nay?</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white dark:bg-navy-800 border-t border-gray-100 dark:border-navy-600">
            <form id="tcwForm" onsubmit="tcwSendMessage(event)" class="relative flex items-center">
                <input type="text" id="tcwInput" autocomplete="off" placeholder="Nhập tin nhắn..."
                    class="w-full bg-gray-100 dark:bg-navy-700 text-gray-800 dark:text-white text-sm rounded-full py-2.5 pl-4 pr-12 focus:outline-none focus:ring-2 focus:ring-green-500/50 border border-gray-200 dark:border-navy-600 transition-all"
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
                <span class="text-[10px] text-gray-400 dark:text-gray-500">Powered by Khodat</span>
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
            if(!tcwIsOpen) {
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
        setTimeout(() => { if(input.disabled) input.disabled = false; }, 5000);
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
                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center flex-shrink-0 text-green-600 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                </div>
                <div class="bg-white dark:bg-navy-700 border border-gray-100 dark:border-navy-600 p-3 rounded-2xl rounded-bl-sm shadow-sm text-gray-700 dark:text-gray-200">
                    <p class="text-sm" style="white-space: pre-wrap; word-wrap: break-word;">${text}</p>
                </div>
            </div>`;
        }
        msgs.insertAdjacentHTML('beforeend', html);
        msgs.scrollTop = msgs.scrollHeight;
    }
</script>