<template>
  <div class="flex h-screen">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />
      <main class="p-3 sm:p-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold">Báo cáo & Thống kê</h1>
            <p class="text-sm text-gray-500 mt-1">Tổng quan hoạt động kinh doanh</p>
          </div>
          <button @click="exportExcel" :disabled="exporting" 
                  class="flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition shadow-sm">
            <svg v-if="!exporting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <svg v-else class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            {{ exporting ? 'Đang xuất...' : 'Xuất Excel' }}
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-20">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
          <span class="ml-3 text-gray-500">Đang tải dữ liệu...</span>
        </div>

        <template v-else>
          <!-- KPI Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Tổng ký gửi</p>
                  <p class="text-3xl font-bold text-gray-900">{{ stats.total_consignments || 0 }}</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl">
                  <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Chờ duyệt</p>
                  <p class="text-3xl font-bold text-yellow-600">{{ stats.pending_consignments || 0 }}</p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-xl">
                  <svg class="w-7 h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Tổng người dùng</p>
                  <p class="text-3xl font-bold text-gray-900">{{ stats.total_users || 0 }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-xl">
                  <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500 mb-1">Tổng doanh thu</p>
                  <p class="text-2xl font-bold text-purple-700">{{ formatCurrency(stats.total_transactions) }}</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-xl">
                  <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row 1 -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Consignments by Month (Bar Chart) -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Ký gửi theo tháng
              </h2>
              <div class="h-72">
                <Bar v-if="monthlyChartData" :data="monthlyChartData" :options="barOptions" />
                <p v-else class="text-gray-400 text-center py-16">Chưa có dữ liệu</p>
              </div>
            </div>

            <!-- Consignments by Status (Doughnut) -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                Phân bổ trạng thái
              </h2>
              <div class="h-72 flex items-center justify-center">
                <Doughnut v-if="statusChartData" :data="statusChartData" :options="doughnutOptions" />
                <p v-else class="text-gray-400">Chưa có dữ liệu</p>
              </div>
            </div>
          </div>

          <!-- Charts Row 2 -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue by Month (Line Chart) -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Doanh thu theo tháng
              </h2>
              <div class="h-72">
                <Line v-if="revenueChartData" :data="revenueChartData" :options="lineOptions" />
                <p v-else class="text-gray-400 text-center py-16">Chưa có dữ liệu</p>
              </div>
            </div>

            <!-- Consignments by Province (Horizontal Bar) -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Top tỉnh/thành phố
              </h2>
              <div class="h-72">
                <Bar v-if="provinceChartData" :data="provinceChartData" :options="horizontalBarOptions" />
                <p v-else class="text-gray-400 text-center py-16">Chưa có dữ liệu</p>
              </div>
            </div>
          </div>
        </template>

        <!-- Export notification -->
        <div v-if="exportMessage" class="fixed bottom-6 right-6 z-50">
          <div :class="exportSuccess ? 'bg-green-500' : 'bg-red-500'" class="text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2">
            <svg v-if="exportSuccess" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ exportMessage }}
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
import { Bar, Doughnut, Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

ChartJS.register(
  CategoryScale, LinearScale, BarElement, LineElement, PointElement,
  ArcElement, Title, Tooltip, Legend, Filler
)

const loading = ref(true)
const exporting = ref(false)
const exportMessage = ref('')
const exportSuccess = ref(true)

const stats = ref({})
const consignmentsByMonth = ref([])
const consignmentsByStatus = ref([])
const consignmentsByProvince = ref([])
const revenueByMonth = ref([])

const monthNames = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12']

const formatCurrency = (value) => {
  if (!value) return '0 đ'
  if (value >= 1e9) return (value / 1e9).toFixed(1).replace('.0', '') + ' tỷ'
  if (value >= 1e6) return (value / 1e6).toFixed(0) + ' triệu'
  return new Intl.NumberFormat('vi-VN').format(value) + ' đ'
}

// Chart data computed
const monthlyChartData = computed(() => {
  if (!consignmentsByMonth.value.length) return null
  return {
    labels: consignmentsByMonth.value.map(d => `${monthNames[d.month - 1]}/${d.year}`),
    datasets: [{
      label: 'Số ký gửi',
      data: consignmentsByMonth.value.map(d => d.count),
      backgroundColor: 'rgba(79, 70, 229, 0.8)',
      borderColor: 'rgba(79, 70, 229, 1)',
      borderWidth: 1,
      borderRadius: 6,
      maxBarThickness: 40
    }]
  }
})

const statusChartData = computed(() => {
  if (!consignmentsByStatus.value.length) return null
  const statusLabels = {
    pending: 'Chờ duyệt',
    approved: 'Đã duyệt',
    rejected: 'Từ chối',
    sold: 'Đã bán'
  }
  const statusColors = {
    pending: '#F59E0B',
    approved: '#10B981',
    rejected: '#EF4444',
    sold: '#6366F1'
  }
  return {
    labels: consignmentsByStatus.value.map(d => statusLabels[d.status] || d.status),
    datasets: [{
      data: consignmentsByStatus.value.map(d => d.count),
      backgroundColor: consignmentsByStatus.value.map(d => statusColors[d.status] || '#9CA3AF'),
      borderWidth: 0,
      hoverOffset: 8
    }]
  }
})

const revenueChartData = computed(() => {
  if (!revenueByMonth.value.length) return null
  return {
    labels: revenueByMonth.value.map(d => `${monthNames[d.month - 1]}/${d.year}`),
    datasets: [{
      label: 'Doanh thu (VNĐ)',
      data: revenueByMonth.value.map(d => d.amount),
      borderColor: 'rgba(139, 92, 246, 1)',
      backgroundColor: 'rgba(139, 92, 246, 0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: 'rgba(139, 92, 246, 1)',
      borderWidth: 2
    }]
  }
})

const provinceChartData = computed(() => {
  if (!consignmentsByProvince.value.length) return null
  return {
    labels: consignmentsByProvince.value.map(d => d.province || 'Không rõ'),
    datasets: [{
      label: 'Số ký gửi',
      data: consignmentsByProvince.value.map(d => d.count),
      backgroundColor: [
        'rgba(239, 68, 68, 0.8)',
        'rgba(249, 115, 22, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(34, 197, 94, 0.8)',
        'rgba(20, 184, 166, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(99, 102, 241, 0.8)',
        'rgba(168, 85, 247, 0.8)',
        'rgba(236, 72, 153, 0.8)',
        'rgba(107, 114, 128, 0.8)'
      ],
      borderWidth: 0,
      borderRadius: 4,
      maxBarThickness: 28
    }]
  }
})

// Chart options
const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1F2937',
      titleColor: '#F9FAFB',
      bodyColor: '#D1D5DB',
      padding: 12,
      cornerRadius: 8
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { stepSize: 1, color: '#9CA3AF' },
      grid: { color: 'rgba(156, 163, 175, 0.1)' }
    },
    x: {
      ticks: { color: '#9CA3AF' },
      grid: { display: false }
    }
  }
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: { padding: 16, usePointStyle: true, pointStyle: 'circle', font: { size: 12 } }
    },
    tooltip: {
      backgroundColor: '#1F2937',
      padding: 12,
      cornerRadius: 8
    }
  },
  cutout: '65%'
}

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1F2937',
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => formatCurrency(ctx.raw)
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        color: '#9CA3AF',
        callback: (val) => formatCurrency(val)
      },
      grid: { color: 'rgba(156, 163, 175, 0.1)' }
    },
    x: {
      ticks: { color: '#9CA3AF' },
      grid: { display: false }
    }
  }
}

const horizontalBarOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1F2937',
      padding: 12,
      cornerRadius: 8
    }
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: { stepSize: 1, color: '#9CA3AF' },
      grid: { color: 'rgba(156, 163, 175, 0.1)' }
    },
    y: {
      ticks: { color: '#374151', font: { size: 11 } },
      grid: { display: false }
    }
  }
}

// Load data
const loadData = async () => {
  loading.value = true
  try {
    const res = await adminApi.getReportOverview()
    const data = res.data.data || {}
    stats.value = data.stats || {}
    consignmentsByMonth.value = data.consignments_by_month || []
    consignmentsByStatus.value = data.consignments_by_status || []
    consignmentsByProvince.value = data.consignments_by_province || []
    revenueByMonth.value = data.revenue_by_month || []
  } catch (error) {
    console.error('Failed to load report data:', error)
  } finally {
    loading.value = false
  }
}

// Export Excel
const exportExcel = async () => {
  exporting.value = true
  exportMessage.value = ''
  try {
    const res = await adminApi.exportReport()
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `bao-cao-khodat_${new Date().toISOString().slice(0, 10)}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    exportMessage.value = '✓ Đã tải xuống thành công!'
    exportSuccess.value = true
  } catch (error) {
    exportMessage.value = 'Lỗi khi xuất báo cáo'
    exportSuccess.value = false
    console.error('Export failed:', error)
  } finally {
    exporting.value = false
    setTimeout(() => { exportMessage.value = '' }, 4000)
  }
}

onMounted(() => { loadData() })
</script>
