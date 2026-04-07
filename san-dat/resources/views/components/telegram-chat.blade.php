<!-- AI Chat Widget -->
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

    /* Typing animation */
    .tcw-typing-dots span {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #4ade80;
        margin: 0 2px;
        animation: tcwBounce 1.4s infinite ease-in-out both;
    }
    .tcw-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .tcw-typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    .tcw-typing-dots span:nth-child(3) { animation-delay: 0s; }
    @keyframes tcwBounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }
</style>
<div id="telegramChatWidget" class="fixed bottom-24 right-4 z-[9999] md:bottom-6 md:right-6 font-sans">

    <!-- Chat Icon -->
    <button id="tcwToggleBtn" onclick="tcwToggleChat()"
        class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-[0_4px_12px_rgba(34,197,94,0.4)] hover:bg-green-600 transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-green-500/30">
        <img src="{{ asset('ai-icon-chat.png') }}" alt="AI Chat" class="w-full h-full object-cover rounded-full">
    </button>

    <!-- Chat Box -->
    <div id="tcwChatBox"
        class="hidden absolute bottom-16 right-0 w-80 md:w-96 rounded-2xl shadow-2xl border overflow-hidden transform transition-all origin-bottom-right scale-95 opacity-0">

        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a10 10 0 1 0 10 10H12V2z" />
                        <path d="M12 12V2a10 10 0 1 1-10 10h10z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm">AI Hỗ Trợ Bất Động Sản</h3>
                    <p class="text-xs text-green-100 flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                        Trợ lý AI sẵn sàng hỗ trợ bạn
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
                        <path d="M12 2a10 10 0 1 0 10 10H12V2z" />
                        <path d="M12 12V2a10 10 0 1 1-10 10h10z" />
                    </svg>
                </div>
                <div class="tcw-msg-bubble p-3 rounded-2xl rounded-bl-sm shadow-sm border">
                    <p class="text-sm">Xin chào! Tôi là trợ lý AI của Khodat. Bạn cần tư vấn gì về bất động sản?</p>
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
                <span class="text-[10px] tcw-powered">Powered by AI · Khodat</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.4/socket.io.min.js"></script>
<script>
    var tcwIsOpen = false;
    var tcwSocket = null;
    var tcwGuestId = null;
    try { tcwGuestId = localStorage.getItem('tcw_session_id'); } catch(e) {}

    if (!tcwGuestId) {
        tcwGuestId = 'GUEST_' + Math.random().toString(36).substr(2, 9).toUpperCase();
        try { localStorage.setItem('tcw_session_id', tcwGuestId); } catch(e) {}
    }

    // Initialize Telegram socket (for admin monitoring)
    function initTcwSocket() {
        if (tcwSocket) return;
        try {
            tcwSocket = io("https://socket.khodat.com", {
                transports: ['websocket', 'polling']
            });
            tcwSocket.on('connect', function() {
                tcwSocket.emit('join_guest_chat', { guestId: tcwGuestId });
            });
            tcwSocket.on('telegram_admin_reply', function(data) {
                tcwRenderMsg(data.text, 'bot');
                if (!tcwIsOpen) {
                    var btn = document.getElementById('tcwToggleBtn');
                    btn.classList.add('animate-bounce');
                    setTimeout(function() { btn.classList.remove('animate-bounce'); }, 3000);
                }
            });
        } catch(e) {
            console.warn('Socket init failed:', e);
        }
    }

    function tcwToggleChat() {
        if (!tcwSocket) initTcwSocket();

        var chatBox = document.getElementById('tcwChatBox');
        if (tcwIsOpen) {
            chatBox.classList.replace('scale-100', 'scale-95');
            chatBox.classList.replace('opacity-100', 'opacity-0');
            setTimeout(function() { chatBox.classList.add('hidden'); chatBox.classList.remove('flex', 'flex-col'); }, 200);
            tcwIsOpen = false;
        } else {
            chatBox.classList.remove('hidden');
            chatBox.classList.add('flex', 'flex-col');
            setTimeout(function() {
                chatBox.classList.replace('scale-95', 'scale-100');
                chatBox.classList.replace('opacity-0', 'opacity-100');
                document.getElementById('tcwInput').focus();
            }, 10);
            tcwIsOpen = true;
        }
    }

    function tcwSendMessage(e) {
        e.preventDefault();
        var input = document.getElementById('tcwInput');
        var text = input.value.trim();
        if (!text) return;

        // Show user message
        tcwRenderMsg(text, 'user');
        input.value = '';
        input.disabled = true;
        document.getElementById('tcwSendBtn').disabled = true;

        // Show typing indicator
        tcwShowTyping();

        // Forward to Telegram (admin monitoring)
        if (tcwSocket && tcwSocket.connected) {
            tcwSocket.emit('guest_message', {
                guestId: tcwGuestId,
                text: text,
                platform: 'Sàn Web (AI)',
            });
        }

        // Call AI API
        fetch('/api/ai-chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            tcwHideTyping();
            var reply = (data && data.text) ? data.text : 'Xin lỗi, tôi không thể trả lời lúc này.';
            tcwRenderMsg(reply, 'bot');
        })
        .catch(function(err) {
            tcwHideTyping();
            tcwRenderMsg('Kết nối bị gián đoạn. Vui lòng thử lại.', 'bot');
        })
        .finally(function() {
            input.disabled = false;
            document.getElementById('tcwSendBtn').disabled = false;
            input.focus();
        });
    }

    function tcwShowTyping() {
        var msgs = document.getElementById('tcwMessages');
        var html = '<div id="tcwTyping" class="flex items-end gap-2 pr-6 mb-2">' +
            '<div class="w-8 h-8 rounded-full tcw-msg-avatar flex items-center justify-center flex-shrink-0">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10H12V2z"/><path d="M12 12V2a10 10 0 1 1-10 10h10z"/></svg>' +
            '</div>' +
            '<div class="tcw-msg-bubble border p-3 rounded-2xl rounded-bl-sm shadow-sm">' +
                '<div class="tcw-typing-dots"><span></span><span></span><span></span></div>' +
            '</div>' +
        '</div>';
        msgs.insertAdjacentHTML('beforeend', html);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function tcwHideTyping() {
        var el = document.getElementById('tcwTyping');
        if (el) el.remove();
    }

    function tcwRenderMsg(text, sender) {
        var msgs = document.getElementById('tcwMessages');
        // Escape HTML to prevent XSS
        var escaped = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        // Convert markdown-like formatting
        escaped = escaped.replace(/\n/g, '<br>');
        // Convert "- " list items to styled list
        escaped = escaped.replace(/^- (.+)/gm, '<span style="display:block;padding-left:12px;text-indent:-12px;">• $1</span>');

        var html = '';
        if (sender === 'user') {
            html = '<div class="flex items-end justify-end gap-2 pl-6 mb-2">' +
                '<div class="bg-green-500 text-white p-3 rounded-2xl rounded-br-sm shadow-sm">' +
                    '<p class="text-sm" style="white-space:pre-wrap;word-wrap:break-word;">' + escaped + '</p>' +
                '</div>' +
            '</div>';
        } else {
            html = '<div class="flex items-end gap-2 pr-6 mb-2">' +
                '<div class="w-8 h-8 rounded-full tcw-msg-avatar flex items-center justify-center flex-shrink-0">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10H12V2z"/><path d="M12 12V2a10 10 0 1 1-10 10h10z"/></svg>' +
                '</div>' +
                '<div class="tcw-msg-bubble border p-3 rounded-2xl rounded-bl-sm shadow-sm">' +
                    '<p class="text-sm" style="white-space:pre-wrap;word-wrap:break-word;">' + escaped + '</p>' +
                '</div>' +
            '</div>';
        }
        msgs.insertAdjacentHTML('beforeend', html);
        msgs.scrollTop = msgs.scrollHeight;
    }
</script>