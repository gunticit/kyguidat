<template>
  <div class="flex h-screen">
    <Sidebar />
    <div class="flex-1 overflow-auto">
      <Header />
      <main class="p-6">
        <h1 class="text-2xl font-bold mb-6">Báo cáo & Thống kê</h1>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Thống kê ký gửi theo tháng</h2>
            <p class="text-gray-500">Chart will be rendered here</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Thống kê giao dịch</h2>
            <p class="text-gray-500">Chart will be rendered here</p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
</script>
