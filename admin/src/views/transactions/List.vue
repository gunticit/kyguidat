<template>
  <div class="flex h-screen">
    <Sidebar />
    <div class="flex-1 overflow-auto">
      <Header />
      <main class="p-6">
        <h1 class="text-2xl font-bold mb-6">Lịch sử Giao dịch</h1>
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="w-full">
            <thead class="bg-gray-50 text-left text-sm text-gray-500">
              <tr>
                <th class="px-6 py-4">ID</th>
                <th class="px-6 py-4">Người dùng</th>
                <th class="px-6 py-4">Loại</th>
                <th class="px-6 py-4">Số tiền</th>
                <th class="px-6 py-4">Trạng thái</th>
                <th class="px-6 py-4">Ngày</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tx in transactions" :key="tx.id" class="border-t hover:bg-gray-50">
                <td class="px-6 py-4">{{ tx.id }}</td>
                <td class="px-6 py-4">{{ tx.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ tx.type }}</td>
                <td class="px-6 py-4">{{ formatCurrency(tx.amount) }}</td>
                <td class="px-6 py-4">
                  <span :class="tx.status === 'completed' ? 'text-green-600' : 'text-yellow-600'">{{ tx.status }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(tx.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </main>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { adminApi } from '@/services/api'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
const transactions = ref([])
const formatCurrency = (v) => v ? new Intl.NumberFormat('vi-VN').format(v) + ' đ' : '0 đ'
const formatDate = (d) => d ? new Date(d).toLocaleDateString('vi-VN') : ''
onMounted(async () => {
  const res = await adminApi.getTransactions({ page: 1 })
  transactions.value = res.data.data || []
})
</script>
