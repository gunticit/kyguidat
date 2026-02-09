<template>
  <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center">
    <div>
      <h2 class="text-gray-500 text-sm">Xin chào,</h2>
      <p class="font-semibold">{{ user?.name || 'Admin' }}</p>
    </div>
    
    <div class="flex items-center space-x-4">
      <!-- Notifications -->
      <button class="p-2 rounded-full hover:bg-gray-100 relative">
        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
      </button>
      
      <!-- User Avatar -->
      <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
        <span class="text-indigo-600 font-semibold">{{ user?.name?.charAt(0) || 'A' }}</span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/store/auth'

const authStore = useAuthStore()
const user = computed(() => authStore.user)
</script>
