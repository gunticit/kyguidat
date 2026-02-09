<template>
  <div class="flex h-screen">
    <Sidebar />
    <div class="flex-1 overflow-auto">
      <Header />
      <main class="p-6">
        <h1 class="text-2xl font-bold mb-6">Chi tiết Ký gửi</h1>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-gray-500">Chi tiết ký gửi #{{ $route.params.id }}</p>
        </div>
      </main>
    </div>
  </div>
</template>
<script setup>
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
</script>
