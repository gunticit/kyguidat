<template>
  <div class="flex h-screen">
    <Sidebar />
    
    <div class="flex-1 overflow-auto">
      <Header />
      
      <main class="p-6">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-full">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Tổng người dùng</p>
                <p class="text-2xl font-bold">{{ stats.total_users || 0 }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-full">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Tổng ký gửi</p>
                <p class="text-2xl font-bold">{{ stats.total_consignments || 0 }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-full">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Chờ duyệt</p>
                <p class="text-2xl font-bold text-yellow-600">{{ stats.pending_consignments || 0 }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-full">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Tổng giao dịch</p>
                <p class="text-2xl font-bold">{{ formatCurrency(stats.total_transactions) }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Recent Pending -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Ký gửi chờ duyệt gần đây</h2>
          </div>
          <div class="p-6">
            <p v-if="loading" class="text-gray-500 text-center py-4">Đang tải...</p>
            <div v-else-if="pendingConsignments.length === 0" class="text-gray-500 text-center py-4">
              Không có ký gửi nào chờ duyệt
            </div>
            <table v-else class="w-full">
              <thead class="text-left text-gray-500 text-sm">
                <tr>
                  <th class="pb-4">Mã</th>
                  <th class="pb-4">Tiêu đề</th>
                  <th class="pb-4">Giá</th>
                  <th class="pb-4">Ngày tạo</th>
                  <th class="pb-4">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in pendingConsignments" :key="item.id" class="border-t">
                  <td class="py-4 text-sm">{{ item.code }}</td>
                  <td class="py-4">{{ item.title }}</td>
                  <td class="py-4">{{ formatCurrency(item.price) }}</td>
                  <td class="py-4 text-sm text-gray-500">{{ formatDate(item.created_at) }}</td>
                  <td class="py-4">
                    <router-link :to="`/consignments/${item.id}`" 
                                 class="text-indigo-600 hover:underline text-sm">
                      Xem chi tiết
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { adminApi } from '@/services/api'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'

const stats = ref({})
const consignments = ref([])
const loading = ref(true)

const pendingConsignments = computed(() => 
  consignments.value.filter(c => c.status === 'pending').slice(0, 5)
)

const formatCurrency = (value) => {
  if (!value) return '0 đ'
  return new Intl.NumberFormat('vi-VN').format(value) + ' đ'
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('vi-VN')
}

onMounted(async () => {
  try {
    const [dashRes, consignRes] = await Promise.all([
      adminApi.getDashboard(),
      adminApi.getConsignments({ status: 'pending', limit: 10 })
    ])
    stats.value = dashRes.data.data || {}
    consignments.value = consignRes.data.data || []
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>
