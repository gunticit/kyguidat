<template>
  <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
    <div>
      <h2 class="text-gray-500 text-sm">Xin chào,</h2>
      <p class="font-semibold">{{ user?.name || 'Admin' }}</p>
    </div>
    
    <div class="flex items-center space-x-3">
      <!-- Notifications -->
      <div class="relative" ref="notifRef">
        <button @click="toggleNotifications" class="p-2 rounded-full hover:bg-gray-100 relative transition">
          <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full px-1">
            {{ unreadCount > 99 ? '99+' : unreadCount }}
          </span>
        </button>

        <!-- Notification Dropdown -->
        <Transition name="dropdown">
          <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
              <h3 class="font-semibold text-sm text-gray-700">Thông báo</h3>
              <button v-if="unreadCount > 0" @click="markAllRead" class="text-xs text-indigo-600 hover:underline">
                Đánh dấu đã đọc
              </button>
            </div>
            <div class="max-h-80 overflow-y-auto">
              <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-400 text-sm">
                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Chưa có thông báo
              </div>
              <div v-for="(notif, index) in notifications" :key="index"
                   @click="handleNotifClick(notif)"
                   :class="notif.read ? 'bg-white' : 'bg-indigo-50/50'"
                   class="px-4 py-3 border-b border-gray-50 hover:bg-gray-50 cursor-pointer transition flex gap-3">
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm"
                     :class="notifIconClass(notif.type)">
                  {{ notifIcon(notif.type) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-gray-800 leading-snug" :class="{ 'font-medium': !notif.read }">{{ notif.message }}</p>
                  <p class="text-xs text-gray-400 mt-1">{{ notif.time }}</p>
                </div>
                <div v-if="!notif.read" class="flex-shrink-0 w-2 h-2 bg-indigo-500 rounded-full mt-2"></div>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- User Profile -->
      <div class="relative" ref="profileRef">
        <button @click="toggleProfile" class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-100 transition">
          <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center">
            <span class="text-indigo-600 font-semibold text-sm">{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
          </div>
        </button>

        <!-- Profile Dropdown -->
        <Transition name="dropdown">
          <div v-if="showProfile" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b">
              <p class="font-semibold text-sm text-gray-800 truncate">{{ user?.name || 'Admin' }}</p>
              <p class="text-xs text-gray-500 truncate">{{ user?.email || '' }}</p>
              <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-medium rounded-full"
                    :class="roleBadgeClass">
                {{ roleLabel }}
              </span>
            </div>
            <div class="py-1">
              <button @click="goToSettings" class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Cài đặt
              </button>
              <hr class="my-1 border-gray-100" />
              <button @click="logout" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Đăng xuất
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { adminApi } from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)

// Dropdowns
const showNotifications = ref(false)
const showProfile = ref(false)
const notifRef = ref(null)
const profileRef = ref(null)

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showProfile.value = false
}

const toggleProfile = () => {
  showProfile.value = !showProfile.value
  showNotifications.value = false
}

// Close dropdowns on outside click
const handleClickOutside = (e) => {
  if (notifRef.value && !notifRef.value.contains(e.target)) {
    showNotifications.value = false
  }
  if (profileRef.value && !profileRef.value.contains(e.target)) {
    showProfile.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

// Notifications — from recent consignment activity
const notifications = ref([])
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

const NOTIF_READ_KEY = 'admin_notif_read_ids'

const getReadIds = () => {
  try {
    return JSON.parse(localStorage.getItem(NOTIF_READ_KEY) || '[]')
  } catch { return [] }
}

const saveReadIds = (ids) => {
  localStorage.setItem(NOTIF_READ_KEY, JSON.stringify(ids))
}

const fetchNotifications = async () => {
  try {
    const res = await adminApi.getConsignments({ per_page: 5, status: 'pending' })
    const pending = res.data?.data || []
    const readIds = getReadIds()
    notifications.value = pending.map(c => ({
      id: c.id,
      type: 'pending',
      message: `Ký gửi mới "${c.title}" đang chờ duyệt`,
      time: formatTimeAgo(c.created_at),
      read: readIds.includes(c.id),
      link: '/consignments'
    }))
  } catch {
    // Silently fail — notifications are non-critical
  }
}

const markAllRead = () => {
  const ids = notifications.value.map(n => n.id)
  notifications.value.forEach(n => n.read = true)
  const readIds = getReadIds()
  saveReadIds([...new Set([...readIds, ...ids])])
}

const handleNotifClick = (notif) => {
  notif.read = true
  const readIds = getReadIds()
  if (!readIds.includes(notif.id)) {
    readIds.push(notif.id)
    saveReadIds(readIds)
  }
  showNotifications.value = false
  if (notif.link) router.push(notif.link)
}

const notifIcon = (type) => ({
  pending: '📋',
  approved: '✅',
  rejected: '❌',
  support: '💬'
}[type] || '🔔')

const notifIconClass = (type) => ({
  pending: 'bg-yellow-100',
  approved: 'bg-green-100',
  rejected: 'bg-red-100',
  support: 'bg-blue-100'
}[type] || 'bg-gray-100')

// User profile
const roleLabel = computed(() => ({
  admin: 'Quản trị viên',
  moderator: 'Kiểm duyệt viên',
  publisher: 'Người đăng tin'
}[authStore.userRole] || authStore.userRole))

const roleBadgeClass = computed(() => ({
  admin: 'bg-red-100 text-red-700',
  moderator: 'bg-blue-100 text-blue-700',
  publisher: 'bg-green-100 text-green-700'
}[authStore.userRole] || 'bg-gray-100 text-gray-700'))

const goToSettings = () => {
  showProfile.value = false
  router.push('/settings')
}

const logout = () => {
  showProfile.value = false
  authStore.logout()
  router.push('/login')
}

// Time ago helper
function formatTimeAgo(dateStr) {
  if (!dateStr) return ''
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'Vừa xong'
  if (mins < 60) return `${mins} phút trước`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs} giờ trước`
  const days = Math.floor(hrs / 24)
  if (days < 7) return `${days} ngày trước`
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

onMounted(fetchNotifications)
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.97);
}
</style>
