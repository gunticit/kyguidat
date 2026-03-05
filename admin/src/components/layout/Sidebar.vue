<template>
  <!-- Mobile overlay -->
  <div v-if="isOpen" @click="close" class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

  <aside :class="[
    'bg-gray-900 text-white flex flex-col z-50 transition-transform duration-300',
    'fixed inset-y-0 left-0 w-64 lg:static lg:translate-x-0',
    isOpen ? 'translate-x-0' : '-translate-x-full'
  ]">
    <!-- Logo -->
    <div class="p-4 border-b border-gray-800 flex items-center justify-between">
      <h1 class="text-xl font-bold">Khodat Admin</h1>
      <button @click="close" class="lg:hidden p-1 rounded hover:bg-gray-800 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 p-4 overflow-y-auto">
      <ul class="space-y-2">
        <!-- IT account only sees Settings -->
        <template v-if="authStore.isIT">
          <li>
            <router-link to="/settings" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/settings' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Cài đặt
            </router-link>
          </li>
        </template>
        
        <!-- Normal users see all menus -->
        <template v-else>
          <li>
            <router-link to="/" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
              Dashboard
            </router-link>
          </li>
          <li>
            <router-link to="/consignments" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path.startsWith('/consignments') }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
              Ký gửi
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/users" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/users' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
              Người dùng
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/customers" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/customers' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Khách hàng
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/transactions" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/transactions' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Giao dịch
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/reports" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/reports' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Báo cáo
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/support" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/support' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
              Hỗ trợ
              <span v-if="supportStore.unreadCount > 0" class="ml-auto px-2 py-0.5 bg-red-500 text-white text-xs rounded-full">
                {{ supportStore.unreadCount }}
              </span>
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/articles" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/articles' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
              </svg>
              Tin tức
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/pages" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/pages' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Trang
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/locations" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/locations' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Tỉnh thành
            </router-link>
          </li>
          <li v-if="authStore.isAdmin">
            <router-link to="/settings" @click="close"
                         class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-800 transition"
                         :class="{ 'bg-gray-800': $route.path === '/settings' }">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Cài đặt
            </router-link>
          </li>
        </template>
      </ul>
    </nav>
    
    <!-- Logout -->
    <div class="p-4 border-t border-gray-800">
      <button @click="logout" class="flex items-center w-full px-4 py-2 rounded-lg hover:bg-gray-800 transition text-red-400">
        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Đăng xuất
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { useSupportStore } from '@/store/support'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const supportStore = useSupportStore()

const isOpen = ref(false)

const open = () => { isOpen.value = true }
const close = () => { isOpen.value = false }

// Close sidebar on route change (mobile)
watch(() => route.path, () => { isOpen.value = false })

const logout = () => {
  authStore.logout()
  router.push('/login')
}

defineExpose({ open, close, isOpen })
</script>
