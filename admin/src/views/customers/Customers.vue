<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar />
    <div class="flex-1 flex flex-col overflow-hidden">
      <Header />
      <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-7xl mx-auto">
          <!-- Header -->
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Quản lý Khách hàng</h1>
            <span class="text-sm text-gray-500">Tài khoản đăng ký từ website</span>
          </div>

          <!-- Search -->
          <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex gap-4">
              <div class="flex-1">
                <input v-model="searchQuery" @input="debouncedSearch" type="text" placeholder="Tìm kiếm theo tên, email, số điện thoại..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
              </div>
              <button @click="fetchCustomers" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
              <p class="text-sm text-gray-500">Tổng khách hàng</p>
              <p class="text-2xl font-bold text-indigo-600">{{ meta.total || 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
              <p class="text-sm text-gray-500">Có ký gửi</p>
              <p class="text-2xl font-bold text-green-600">{{ customersWithConsignments }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
              <p class="text-sm text-gray-500">Trang hiện tại</p>
              <p class="text-2xl font-bold text-gray-700">{{ meta.current_page || 1 }}/{{ meta.last_page || 1 }}</p>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
            <p class="mt-2 text-gray-500">Đang tải...</p>
          </div>

          <!-- Error -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-red-600">{{ error }}</p>
          </div>

          <!-- Table -->
          <div v-if="!loading" class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số điện thoại</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số ký gửi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày đăng ký</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="customer in customers" :key="customer.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm mr-3">
                        {{ customer.name?.charAt(0)?.toUpperCase() || '?' }}
                      </div>
                      <span class="text-sm font-medium text-gray-900">{{ customer.name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ customer.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ customer.phone || '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="customer.consignments_count > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="px-2 py-1 rounded-full text-xs font-medium">
                      {{ customer.consignments_count || 0 }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ new Date(customer.created_at).toLocaleDateString('vi-VN') }}
                  </td>
                </tr>
                <tr v-if="customers.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                    Không tìm thấy khách hàng nào
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="meta.last_page > 1" class="flex justify-center gap-2 mt-6">
            <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page <= 1" class="px-3 py-1 border rounded-lg disabled:opacity-50 hover:bg-gray-50 transition">
              ← Trước
            </button>
            <template v-for="page in paginationPages" :key="page">
              <button v-if="page !== '...'" @click="changePage(page)" :class="page === meta.current_page ? 'bg-indigo-600 text-white' : 'hover:bg-gray-50'" class="px-3 py-1 border rounded-lg transition">
                {{ page }}
              </button>
              <span v-else class="px-2 py-1 text-gray-400">...</span>
            </template>
            <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" class="px-3 py-1 border rounded-lg disabled:opacity-50 hover:bg-gray-50 transition">
              Sau →
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '@/services/api'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'

const customers = ref([])
const loading = ref(false)
const error = ref('')
const searchQuery = ref('')
const meta = ref({ current_page: 1, last_page: 1, total: 0 })

let searchTimeout = null

const customersWithConsignments = computed(() => {
  return customers.value.filter(c => (c.consignments_count || 0) > 0).length
})

const paginationPages = computed(() => {
  const pages = []
  const total = meta.value.last_page
  const current = meta.value.current_page
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
    if (current < total - 2) pages.push('...')
    pages.push(total)
  }
  return pages
})

const fetchCustomers = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = { page: meta.value.current_page }
    if (searchQuery.value) params.search = searchQuery.value
    const res = await adminApi.getCustomers(params)
    customers.value = res.data.data
    meta.value = res.data.meta
  } catch (e) {
    error.value = 'Lỗi khi tải danh sách khách hàng'
    console.error('Error fetching customers:', e)
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    meta.value.current_page = 1
    fetchCustomers()
  }, 400)
}

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return
  meta.value.current_page = page
  fetchCustomers()
}

onMounted(() => {
  fetchCustomers()
})
</script>
