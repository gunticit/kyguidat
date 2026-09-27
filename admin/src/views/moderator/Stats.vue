<template>
  <div class="flex h-screen bg-gray-50">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto flex flex-col">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />
      <main class="flex-1 p-3 sm:p-6">
  <div class="space-y-6">
    <!-- Header with Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Thống kê Kiểm duyệt viên</h1>
        <p class="text-sm text-gray-500 mt-1">Theo dõi năng suất và tiến độ xử lý sản phẩm được phân công</p>
      </div>

      <!-- Filters: Month and Year -->
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">Tháng:</label>
          <select
            v-model="selectedMonth"
            @change="fetchStats"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
          >
            <option :value="null">Cả năm</option>
            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">Năm:</label>
          <select
            v-model="selectedYear"
            @change="fetchStats"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
          >
            <option v-for="y in availableYears" :key="y" :value="y">Năm {{ y }}</option>
          </select>
        </div>

        <button
          @click="fetchStats"
          :disabled="loading"
          class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition flex items-center gap-1.5"
        >
          <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Làm mới
        </button>
      </div>
    </div>

    <!-- Quick preset badges -->
    <div class="flex items-center gap-2">
      <span class="text-xs text-gray-500 font-medium">Chọn nhanh:</span>
      <button
        @click="setPreset('this_month')"
        class="px-3 py-1 text-xs rounded-full border transition"
        :class="isThisMonth ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200'"
      >
        Tháng này
      </button>
      <button
        @click="setPreset('last_month')"
        class="px-3 py-1 text-xs rounded-full border transition"
        :class="isLastMonth ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200'"
      >
        Tháng trước
      </button>
      <button
        @click="setPreset('full_year')"
        class="px-3 py-1 text-xs rounded-full border transition"
        :class="isFullYear ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200'"
      >
        Cả năm {{ currentYear }}
      </button>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      <!-- Total Assigned -->
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Được phân công</span>
          <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ stats.total_assigned || 0 }}</p>
        <span class="text-xs text-gray-500 mt-1 block">Sản phẩm trong kỳ</span>
      </div>

      <!-- Approved -->
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Đã duyệt</span>
          <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-emerald-600 mt-2">{{ stats.total_approved || 0 }}</p>
        <span class="text-xs text-emerald-500 mt-1 block">Sản phẩm xuất bản</span>
      </div>

      <!-- Rejected -->
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-red-600 uppercase tracking-wider">Từ chối</span>
          <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-red-600 mt-2">{{ stats.total_rejected || 0 }}</p>
        <span class="text-xs text-red-500 mt-1 block">Không đạt yêu cầu</span>
      </div>

      <!-- Total Processed -->
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Đã xử lý</span>
          <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-purple-600 mt-2">{{ stats.total_processed || 0 }}</p>
        <span class="text-xs text-purple-500 mt-1 block">Duyệt + Từ chối</span>
      </div>

      <!-- Current Pending -->
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Chờ bạn duyệt</span>
          <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-amber-600 mt-2">{{ stats.current_pending || 0 }}</p>
        <span class="text-xs text-amber-500 mt-1 block">Tồn đọng hiện tại</span>
      </div>

      <!-- Completion Rate -->
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Tỷ lệ xong</span>
          <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-indigo-600 mt-2">{{ stats.completion_rate || 0 }}%</p>
        <span class="text-xs text-indigo-500 mt-1 block">Hiệu suất hoàn thành</span>
      </div>
    </div>

    <!-- Chart / Timeline breakdown -->
    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Biểu đồ tiến độ kiểm duyệt</h2>
          <p class="text-sm text-gray-500 mt-0.5">
            {{ selectedMonth ? `Chi tiết từng ngày trong Tháng ${selectedMonth}/${selectedYear}` : `Tổng hợp 12 tháng năm ${selectedYear}` }}
          </p>
        </div>
        <div class="flex items-center gap-4 text-xs font-medium">
          <span class="flex items-center gap-1.5 text-emerald-600">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Đã duyệt
          </span>
          <span class="flex items-center gap-1.5 text-red-600">
            <span class="w-3 h-3 rounded-full bg-red-500"></span> Từ chối
          </span>
        </div>
      </div>

      <!-- Visual Bar Grid -->
      <div v-if="chartData.length > 0" class="overflow-x-auto pb-2">
        <div class="flex items-end gap-2 h-56 pt-8 min-w-[600px] border-b border-gray-200">
          <div
            v-for="(item, idx) in chartData"
            :key="idx"
            class="flex-1 flex flex-col items-center justify-end h-full group relative"
          >
            <!-- Tooltip -->
            <div class="absolute -top-12 hidden group-hover:flex flex-col items-center bg-gray-900 text-white text-xs py-1 px-2.5 rounded shadow-lg z-20 whitespace-nowrap pointer-events-none">
              <span class="font-bold">{{ item.label }}</span>
              <span>Duyệt: {{ item.approved }} | Từ chối: {{ item.rejected }}</span>
            </div>

            <!-- Stacked Bars -->
            <div class="w-full max-w-[28px] flex flex-col justify-end gap-0.5 h-full">
              <!-- Approved Bar -->
              <div
                class="w-full bg-emerald-500 hover:bg-emerald-600 rounded-t transition-all duration-300"
                :style="{ height: getBarHeight(item.approved) }"
                :title="`Duyệt: ${item.approved}`"
              ></div>
              <!-- Rejected Bar -->
              <div
                v-if="item.rejected > 0"
                class="w-full bg-red-500 hover:bg-red-600 rounded-t transition-all duration-300"
                :style="{ height: getBarHeight(item.rejected) }"
                :title="`Từ chối: ${item.rejected}`"
              ></div>
            </div>

            <!-- X-axis Label -->
            <span class="text-[11px] text-gray-500 mt-2 transform -rotate-45 md:rotate-0 origin-top-left md:origin-center">
              {{ item.label }}
            </span>
          </div>
        </div>
      </div>
      <div v-else class="py-12 text-center text-gray-400 text-sm">
        Chưa có dữ liệu kiểm duyệt trong kỳ này
      </div>
    </div>

    <!-- Recent Actions Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Nhật ký duyệt gần nhất</h2>
          <p class="text-sm text-gray-500 mt-0.5">Các sản phẩm bạn đã xem xét và duyệt trong thời gian qua</p>
        </div>
        <router-link
          to="/consignments"
          class="text-sm font-medium text-primary hover:text-primary-dark transition flex items-center gap-1"
        >
          Đến danh sách ký gửi
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </router-link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
              <th class="py-3 px-6">Mã tin</th>
              <th class="py-3 px-6">Tiêu đề</th>
              <th class="py-3 px-6">Giá</th>
              <th class="py-3 px-6">Kết quả</th>
              <th class="py-3 px-6">Thời gian</th>
              <th class="py-3 px-6 text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            <tr v-for="item in recentActions" :key="item.id" class="hover:bg-gray-50/80 transition">
              <td class="py-3.5 px-6 font-mono font-medium text-gray-900">{{ item.code }}</td>
              <td class="py-3.5 px-6 font-medium text-gray-900 max-w-xs truncate" :title="item.title">
                {{ item.title }}
              </td>
              <td class="py-3.5 px-6 font-semibold text-primary">{{ formatPrice(item.price) }}</td>
              <td class="py-3.5 px-6">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="item.status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'"
                >
                  <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="item.status === 'approved' ? 'bg-emerald-500' : 'bg-red-500'"></span>
                  {{ item.status === 'approved' ? 'Đã duyệt' : 'Từ chối' }}
                </span>
                <p v-if="item.reject_reason" class="text-xs text-gray-500 mt-1 italic max-w-xs truncate" :title="item.reject_reason">
                  Lý do: {{ item.reject_reason }}
                </p>
              </td>
              <td class="py-3.5 px-6 text-gray-500 text-xs">
                {{ formatDate(item.approved_at || item.updated_at) }}
              </td>
              <td class="py-3.5 px-6 text-right">
                <router-link
                  :to="`/consignments/${item.id}`"
                  class="text-xs font-medium text-blue-600 hover:text-blue-800 transition"
                >
                  Xem chi tiết
                </router-link>
              </td>
            </tr>
            <tr v-if="recentActions.length === 0">
              <td colspan="6" class="py-8 text-center text-gray-400 text-sm">
                Chưa có thao tác duyệt nào gần đây
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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

const currentYear = new Date().getFullYear()
const currentMonth = new Date().getMonth() + 1

const selectedYear = ref(currentYear)
const selectedMonth = ref(currentMonth)
const loading = ref(false)

const stats = ref({
  total_assigned: 0,
  total_approved: 0,
  total_rejected: 0,
  total_processed: 0,
  current_pending: 0,
  completion_rate: 0,
})

const chartData = ref([])
const recentActions = ref([])

const availableYears = computed(() => {
  const years = []
  for (let y = currentYear + 1; y >= currentYear - 3; y--) {
    years.push(y)
  }
  return years
})

const isThisMonth = computed(() => selectedYear.value === currentYear && selectedMonth.value === currentMonth)
const isLastMonth = computed(() => {
  const lastM = currentMonth === 1 ? 12 : currentMonth - 1
  const lastY = currentMonth === 1 ? currentYear - 1 : currentYear
  return selectedYear.value === lastY && selectedMonth.value === lastM
})
const isFullYear = computed(() => selectedYear.value === currentYear && selectedMonth.value === null)

const maxChartValue = computed(() => {
  if (!chartData.value || chartData.value.length === 0) return 10
  const max = Math.max(...chartData.value.map(i => Math.max(i.approved || 0, i.rejected || 0)))
  return max > 0 ? max : 10
})

function getBarHeight(val) {
  if (!val || val <= 0) return '0%'
  const percent = Math.min(100, Math.round((val / maxChartValue.value) * 100))
  return `${Math.max(6, percent)}%`
}

function setPreset(type) {
  if (type === 'this_month') {
    selectedYear.value = currentYear
    selectedMonth.value = currentMonth
  } else if (type === 'last_month') {
    selectedYear.value = currentMonth === 1 ? currentYear - 1 : currentYear
    selectedMonth.value = currentMonth === 1 ? 12 : currentMonth - 1
  } else if (type === 'full_year') {
    selectedYear.value = currentYear
    selectedMonth.value = null
  }
  fetchStats()
}

async function fetchStats() {
  loading.value = true
  try {
    const params = {
      year: selectedYear.value,
    }
    if (selectedMonth.value) {
      params.month = selectedMonth.value
    }
    const res = await adminApi.getModeratorMyStats(params)
    if (res.data?.success && res.data?.data) {
      const data = res.data.data
      stats.value = data.stats || {}
      chartData.value = data.chart_data || []
      recentActions.value = data.recent_actions || []
    }
  } catch (err) {
    console.error('Error fetching moderator stats:', err)
  } finally {
    loading.value = false
  }
}

function formatPrice(value) {
  if (!value && value !== 0) return '0 đ'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  return d.toLocaleString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  fetchStats()
})
</script>
