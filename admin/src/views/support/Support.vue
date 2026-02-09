<template>
  <div class="flex h-[calc(100vh-64px)]">
    <!-- Left Panel: Ticket List -->
    <div class="w-96 border-r border-gray-700 flex flex-col bg-gray-900">
      <!-- Header -->
      <div class="p-4 border-b border-gray-700">
        <h2 class="text-lg font-bold text-white mb-3">Hỗ trợ khách hàng</h2>
        <input v-model="searchQuery" @input="debouncedSearch" type="text"
               placeholder="Tìm kiếm ticket..."
               class="w-full px-3 py-2 bg-gray-800 text-white rounded-lg border border-gray-600 focus:border-blue-500 outline-none text-sm" />
      </div>

      <!-- Status Tabs -->
      <div class="flex border-b border-gray-700 overflow-x-auto">
        <button v-for="tab in statusTabs" :key="tab.value"
                @click="filterStatus = tab.value; loadTickets()"
                :class="['px-3 py-2 text-xs whitespace-nowrap transition',
                         filterStatus === tab.value ? 'text-blue-400 border-b-2 border-blue-400 font-semibold' : 'text-gray-400 hover:text-white']">
          {{ tab.label }}
          <span v-if="tab.count > 0" class="ml-1 px-1.5 py-0.5 bg-red-500 text-white rounded-full text-xs">{{ tab.count }}</span>
        </button>
      </div>

      <!-- Ticket List -->
      <div class="flex-1 overflow-y-auto">
        <div v-if="store.loading && !store.tickets.length" class="p-8 text-center text-gray-400">
          <div class="animate-spin w-8 h-8 border-2 border-blue-400 border-t-transparent rounded-full mx-auto mb-2"></div>
          Đang tải...
        </div>
        <div v-else-if="!store.tickets.length" class="p-8 text-center text-gray-400">
          <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          Chưa có yêu cầu hỗ trợ
        </div>
        <div v-else>
          <div v-for="ticket in store.tickets" :key="ticket.id"
               @click="selectTicket(ticket)"
               :class="['p-4 border-b border-gray-800 cursor-pointer transition hover:bg-gray-800',
                        selectedTicketId === ticket.id ? 'bg-gray-800 border-l-2 border-l-blue-500' : '']">
            <div class="flex items-start justify-between mb-1">
              <span class="text-sm font-medium text-white truncate flex-1">{{ ticket.subject }}</span>
              <span :class="getStatusClass(ticket.status)" class="px-2 py-0.5 rounded text-xs ml-2 whitespace-nowrap">
                {{ getStatusLabel(ticket.status) }}
              </span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
              <span class="font-medium text-gray-300">{{ ticket.user?.name || 'Ẩn danh' }}</span>
              <span>·</span>
              <span>{{ ticket.ticket_number }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
              <span>{{ formatDate(ticket.updated_at) }}</span>
              <span v-if="ticket.messages_count" class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                {{ ticket.messages_count }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Panel: Chat View -->
    <div class="flex-1 flex flex-col bg-gray-950">
      <!-- No ticket selected -->
      <div v-if="!selectedTicketId" class="flex-1 flex items-center justify-center text-gray-500">
        <div class="text-center">
          <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <p class="text-lg">Chọn một ticket để bắt đầu trò chuyện</p>
        </div>
      </div>

      <!-- Chat content -->
      <template v-else>
        <!-- Chat Header -->
        <div class="p-4 border-b border-gray-700 bg-gray-900 flex items-center justify-between">
          <div>
            <h3 class="text-white font-semibold">{{ store.currentTicket?.subject }}</h3>
            <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
              <span>{{ store.currentTicket?.ticket_number }}</span>
              <span>·</span>
              <span>{{ store.currentTicket?.user?.name }}</span>
              <span>·</span>
              <span>{{ store.currentTicket?.user?.email }}</span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <select v-model="ticketStatus" @change="changeStatus"
                    class="bg-gray-800 text-white text-sm rounded-lg px-3 py-1.5 border border-gray-600 outline-none">
              <option value="open">Đang mở</option>
              <option value="in_progress">Đang xử lý</option>
              <option value="waiting_reply">Chờ phản hồi</option>
              <option value="resolved">Đã giải quyết</option>
              <option value="closed">Đã đóng</option>
            </select>
            <button @click="closeTicket" v-if="ticketStatus !== 'closed'"
                    class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
              Đóng ticket
            </button>
          </div>
        </div>

        <!-- Messages Area -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
          <div v-if="store.loading" class="text-center text-gray-400 py-8">
            <div class="animate-spin w-6 h-6 border-2 border-blue-400 border-t-transparent rounded-full mx-auto"></div>
          </div>
          <div v-for="msg in store.messages" :key="msg.id"
               :class="['flex', msg.is_admin ? 'justify-end' : 'justify-start']">
            <div :class="['max-w-lg rounded-2xl px-4 py-3',
                         msg.is_admin
                           ? 'bg-blue-600 text-white rounded-br-md'
                           : 'bg-gray-800 text-gray-200 rounded-bl-md']">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-semibold" :class="msg.is_admin ? 'text-blue-200' : 'text-gray-400'">
                  {{ msg.is_admin ? 'Admin' : (msg.user?.name || 'Khách hàng') }}
                </span>
                <span class="text-xs opacity-60">{{ formatTime(msg.created_at) }}</span>
              </div>
              <p class="text-sm whitespace-pre-wrap">{{ msg.message }}</p>
            </div>
          </div>

          <!-- Typing indicator -->
          <div v-if="isUserTyping" class="flex justify-start">
            <div class="bg-gray-800 text-gray-400 rounded-2xl px-4 py-3 rounded-bl-md">
              <div class="flex items-center gap-1">
                <span class="text-xs">Khách hàng đang gõ</span>
                <span class="flex gap-1">
                  <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                  <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                  <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="p-4 border-t border-gray-700 bg-gray-900" v-if="ticketStatus !== 'closed'">
          <div class="flex gap-3">
            <textarea v-model="newMessage" @keydown.enter.exact.prevent="sendMessage"
                      @input="handleTyping"
                      placeholder="Nhập phản hồi..."
                      rows="2"
                      class="flex-1 bg-gray-800 text-white rounded-lg px-4 py-3 border border-gray-600 focus:border-blue-500 outline-none resize-none text-sm"></textarea>
            <button @click="sendMessage" :disabled="!newMessage.trim() || sending"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition self-end">
              <svg v-if="sending" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              <span v-else>Gửi</span>
            </button>
          </div>
        </div>
        <div v-else class="p-4 border-t border-gray-700 bg-gray-900 text-center text-gray-400 text-sm">
          Ticket này đã đóng
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch, computed } from 'vue'
import { useSupportStore } from '@/store/support'
import { io } from 'socket.io-client'

const store = useSupportStore()

// State
const searchQuery = ref('')
const filterStatus = ref('')
const selectedTicketId = ref(null)
const newMessage = ref('')
const sending = ref(false)
const ticketStatus = ref('')
const isUserTyping = ref(false)
const messagesContainer = ref(null)

// Socket
const SOCKET_URL = import.meta.env.VITE_SOCKET_URL || 'http://localhost:3020'
let socket = null
let typingTimeout = null
let searchTimeout = null

// Status tabs
const statusTabs = computed(() => [
  { label: 'Tất cả', value: '', count: 0 },
  { label: 'Đang mở', value: 'open', count: store.counts.open },
  { label: 'Đang xử lý', value: 'in_progress', count: store.counts.in_progress },
  { label: 'Chờ phản hồi', value: 'waiting_reply', count: store.counts.waiting_reply },
  { label: 'Đã đóng', value: 'closed', count: 0 },
])

// Socket.IO setup
function initSocket() {
  socket = io(SOCKET_URL, {
    transports: ['websocket', 'polling'],
    reconnection: true,
    reconnectionAttempts: 10,
    reconnectionDelay: 2000,
  })

  socket.on('connect', () => {
    console.log('✅ Socket connected')
    // Authenticate as admin
    const user = JSON.parse(localStorage.getItem('admin_user') || '{}')
    socket.emit('authenticate', {
      userId: user.id,
      role: 'admin',
      name: user.name || 'Admin',
      token: localStorage.getItem('admin_token'),
    })
  })

  socket.on('authenticated', () => {
    console.log('✅ Socket authenticated as admin')
  })

  // New message received
  socket.on('new_message', (data) => {
    store.addSocketMessage(data)
    scrollToBottom()
  })

  // New ticket notification
  socket.on('new_ticket_notification', (data) => {
    store.incrementUnread()
    loadTickets()
    // Play notification sound
    playNotificationSound()
  })

  // Ticket message notification (when user sends message in a ticket admin isn't viewing)
  socket.on('ticket_message_notification', (data) => {
    if (selectedTicketId.value !== data.ticketId) {
      store.incrementUnread()
      playNotificationSound()
    }
    loadTickets() // Refresh list
  })

  // Typing indicator
  socket.on('user_typing', (data) => {
    if (data.role !== 'admin' && data.isTyping) {
      isUserTyping.value = true
      clearTimeout(typingTimeout)
      typingTimeout = setTimeout(() => {
        isUserTyping.value = false
      }, 3000)
    } else {
      isUserTyping.value = false
    }
  })

  // Ticket status changed
  socket.on('ticket_status_changed', (data) => {
    if (store.currentTicket && store.currentTicket.id === data.ticketId) {
      ticketStatus.value = data.status
    }
    loadTickets()
  })

  socket.on('disconnect', () => {
    console.log('🔌 Socket disconnected')
  })
}

function playNotificationSound() {
  try {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQAGAACBhYqFbF1fdJivrJBhNjVgipKRfWxiXm6Ij4x0T0I3WXOFkZuPd2BRWXWBjZePfXBcVm2Gg4l1VD0qN05Xc42TjHtpTzxFVl5rg5SRh3hwY19sfIWEfm9dTk1NXnKIkZSPgXdwbXB0dXFoXVFGPkhXaoGRoKaknpaSinxfMyQwS2iAnqOnnI6Hj5ybk35fQC04UW2MpKylmpCMjpCNhXBRNygvRl10jp+opZ6Xk5KPhXBZRjwuNkVWbH2RnKajop+cm5mUjIJ4bWJURz43Oj5IVGRwfIiQl5ygoqSjoZ6YkoiAdWtgVktCPDo/R1BdanZ/h42Sl5qbnJuZlo+HfXJoW1BJRD9ARk1WYGt1foaNk5eZmpqYlJCKgnd')
    audio.volume = 0.3
    audio.play().catch(() => {})
  } catch (e) {}
}

// Actions
async function loadTickets() {
  const params = {}
  if (filterStatus.value) params.status = filterStatus.value
  if (searchQuery.value) params.search = searchQuery.value
  await store.fetchTickets(params)
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadTickets()
  }, 300)
}

async function selectTicket(ticket) {
  selectedTicketId.value = ticket.id
  ticketStatus.value = ticket.status

  // Leave previous room
  if (socket) socket.emit('leave_ticket', { ticketId: selectedTicketId.value })

  // Fetch ticket details
  await store.fetchTicket(ticket.id)

  // Join room
  if (socket) socket.emit('join_ticket', { ticketId: ticket.id })

  store.resetUnread()
  await nextTick()
  scrollToBottom()
}

async function sendMessage() {
  if (!newMessage.value.trim() || !selectedTicketId.value || sending.value) return

  sending.value = true
  const message = newMessage.value.trim()
  newMessage.value = ''

  // Send via API
  const result = await store.sendReply(selectedTicketId.value, message)

  // Also emit via socket for real-time
  if (socket && result) {
    socket.emit('send_message', {
      ticketId: selectedTicketId.value,
      message,
      tempId: result.id,
    })
  }

  sending.value = false
  await nextTick()
  scrollToBottom()
}

function handleTyping() {
  if (socket && selectedTicketId.value) {
    socket.emit('typing', { ticketId: selectedTicketId.value, isTyping: true })
  }
}

async function changeStatus() {
  if (!selectedTicketId.value) return
  await store.updateStatus(selectedTicketId.value, ticketStatus.value)

  if (socket) {
    socket.emit('ticket_updated', {
      ticketId: selectedTicketId.value,
      status: ticketStatus.value,
    })
  }
  loadTickets()
}

async function closeTicket() {
  if (!selectedTicketId.value) return
  if (!confirm('Bạn có chắc muốn đóng ticket này?')) return

  await store.closeTicket(selectedTicketId.value)
  ticketStatus.value = 'closed'

  if (socket) {
    socket.emit('ticket_updated', {
      ticketId: selectedTicketId.value,
      status: 'closed',
    })
  }
  loadTickets()
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

// Helpers
function getStatusLabel(status) {
  const labels = {
    open: 'Mở',
    in_progress: 'Đang xử lý',
    waiting_reply: 'Chờ phản hồi',
    resolved: 'Đã giải quyết',
    closed: 'Đã đóng',
  }
  return labels[status] || status
}

function getStatusClass(status) {
  const classes = {
    open: 'bg-green-500/20 text-green-400',
    in_progress: 'bg-yellow-500/20 text-yellow-400',
    waiting_reply: 'bg-purple-500/20 text-purple-400',
    resolved: 'bg-blue-500/20 text-blue-400',
    closed: 'bg-gray-500/20 text-gray-400',
  }
  return classes[status] || 'bg-gray-500/20 text-gray-400'
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now - date
  if (diff < 60000) return 'Vừa xong'
  if (diff < 3600000) return `${Math.floor(diff / 60000)} phút trước`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} giờ trước`
  return date.toLocaleDateString('vi-VN')
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
}

// Lifecycle
onMounted(() => {
  loadTickets()
  initSocket()
})

onUnmounted(() => {
  if (socket) {
    socket.disconnect()
    socket = null
  }
})
</script>
