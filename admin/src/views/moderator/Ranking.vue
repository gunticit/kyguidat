<template>
  <div class="space-y-6">
    <!-- Header with Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Admin</span>
          <h1 class="text-2xl font-bold text-gray-900">Xếp hạng & Hiệu suất Kiểm duyệt</h1>
        </div>
        <p class="text-sm text-gray-500 mt-1">Đánh giá năng suất, tỷ lệ xử lý và xếp hạng thi đua giữa các kiểm duyệt viên</p>
      </div>

      <!-- Filters: Moderator, Month, Year -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Filter Moderator -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">Nhân viên:</label>
          <select
            v-model="selectedModeratorId"
            @change="fetchLeaderboard"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none max-w-[180px]"
          >
            <option :value="null">Tất cả kiểm duyệt viên</option>
            <option v-for="mod in moderatorsList" :key="mod.id" :value="mod.id">
              {{ mod.name }}
            </option>
          </select>
        </div>

        <!-- Filter Month -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">Tháng:</label>
          <select
            v-model="selectedMonth"
            @change="fetchLeaderboard"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
          >
            <option :value="null">Cả năm</option>
            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
          </select>
        </div>

        <!-- Filter Year -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">Năm:</label>
          <select
            v-model="selectedYear"
            @change="fetchLeaderboard"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
          >
            <option v-for="y in availableYears" :key="y" :value="y">Năm {{ y }}</option>
          </select>
        </div>

        <button
          @click="fetchLeaderboard"
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

    <!-- System Totals KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng nhân sự kiểm duyệt</span>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ systemTotals.total_moderators || 0 }}</p>
        <span class="text-xs text-gray-400 mt-1 block">Kiểm duyệt viên & Kiểm soát</span>
      </div>

      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Tổng bài đã duyệt</span>
        <p class="text-2xl font-bold text-emerald-600 mt-2">{{ systemTotals.total_approved || 0 }}</p>
        <span class="text-xs text-emerald-500 mt-1 block">Trong kỳ đã chọn</span>
      </div>

      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <span class="text-xs font-semibold text-red-600 uppercase tracking-wider">Tổng bài từ chối</span>
        <p class="text-2xl font-bold text-red-600 mt-2">{{ systemTotals.total_rejected || 0 }}</p>
        <span class="text-xs text-red-500 mt-1 block">Không đạt yêu cầu</span>
      </div>

      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Tổng sản lượng xử lý</span>
        <p class="text-2xl font-bold text-purple-600 mt-2">{{ systemTotals.total_processed || 0 }}</p>
        <span class="text-xs text-purple-500 mt-1 block">Toàn hệ thống</span>
      </div>

      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
        <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Bài đang chờ xử lý</span>
        <p class="text-2xl font-bold text-amber-600 mt-2">{{ systemTotals.total_pending || 0 }}</p>
        <span class="text-xs text-amber-500 mt-1 block">Tồn đọng hiện tại</span>
      </div>
    </div>

    <!-- Top 3 Podium (Huy chương Vàng, Bạc, Đồng) -->
    <div v-if="topThree.length >= 2" class="bg-gradient-to-r from-amber-50 via-white to-orange-50 p-6 rounded-xl border border-amber-200/60 shadow-sm">
      <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
        <span class="text-xl">🏆</span> Top Nhân Viên Kiểm Duyệt Xuất Sắc Nhất
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <!-- Rank 2: Silver -->
        <div v-if="topThree[1]" class="bg-white p-5 rounded-xl border-2 border-gray-200 shadow-sm order-2 md:order-1 text-center relative pt-8">
          <div class="absolute -top-4 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-slate-300 text-slate-800 font-bold flex items-center justify-center shadow text-sm border-2 border-white">
            2
          </div>
          <h3 class="font-bold text-gray-900 text-lg">{{ topThree[1].name }}</h3>
          <p class="text-xs text-gray-500">{{ topThree[1].email }}</p>
          <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-gray-400">Đã duyệt:</span>
              <p class="font-bold text-emerald-600 text-sm">{{ topThree[1].total_approved }}</p>
            </div>
            <div>
              <span class="text-gray-400">Tổng xử lý:</span>
              <p class="font-bold text-purple-600 text-sm">{{ topThree[1].total_processed }}</p>
            </div>
          </div>
        </div>

        <!-- Rank 1: Gold -->
        <div v-if="topThree[0]" class="bg-gradient-to-b from-amber-50 to-white p-6 rounded-xl border-2 border-amber-400 shadow-md order-1 md:order-2 text-center relative pt-10 md:-translate-y-2">
          <div class="absolute -top-5 left-1/2 -translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-tr from-amber-400 to-yellow-300 text-amber-950 font-extrabold flex items-center justify-center shadow-lg text-lg border-2 border-white">
            👑 1
          </div>
          <h3 class="font-bold text-gray-900 text-xl text-amber-900">{{ topThree[0].name }}</h3>
          <p class="text-xs text-amber-700/80">{{ topThree[0].email }}</p>
          <div class="mt-4 pt-4 border-t border-amber-100 grid grid-cols-3 gap-2 text-xs">
            <div>
              <span class="text-gray-500">Đã duyệt:</span>
              <p class="font-extrabold text-emerald-600 text-base">{{ topThree[0].total_approved }}</p>
            </div>
            <div>
              <span class="text-gray-500">Từ chối:</span>
              <p class="font-extrabold text-red-500 text-base">{{ topThree[0].total_rejected }}</p>
            </div>
            <div>
              <span class="text-gray-500">Tổng xử lý:</span>
              <p class="font-extrabold text-purple-600 text-base">{{ topThree[0].total_processed }}</p>
            </div>
          </div>
          <div class="mt-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
            Hiệu suất: {{ topThree[0].completion_rate }}%
          </div>
        </div>

        <!-- Rank 3: Bronze -->
        <div v-if="topThree[2]" class="bg-white p-5 rounded-xl border-2 border-amber-700/20 shadow-sm order-3 text-center relative pt-8">
          <div class="absolute -top-4 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-amber-700/40 text-amber-950 font-bold flex items-center justify-center shadow text-sm border-2 border-white">
            3
          </div>
          <h3 class="font-bold text-gray-900 text-lg">{{ topThree[2].name }}</h3>
          <p class="text-xs text-gray-500">{{ topThree[2].email }}</p>
          <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-gray-400">Đã duyệt:</span>
              <p class="font-bold text-emerald-600 text-sm">{{ topThree[2].total_approved }}</p>
            </div>
            <div>
              <span class="text-gray-400">Tổng xử lý:</span>
              <p class="font-bold text-purple-600 text-sm">{{ topThree[2].total_processed }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Bảng Xếp Hạng Chi Tiết</h2>
          <p class="text-sm text-gray-500 mt-0.5">
            Dữ liệu tổng hợp theo {{ selectedMonth ? `Tháng ${selectedMonth}/${selectedYear}` : `Năm ${selectedYear}` }}
          </p>
        </div>
        <div class="text-xs text-gray-500">
          Sắp xếp theo: <span class="font-semibold text-gray-700">Tổng bài đã xử lý</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
              <th class="py-3.5 px-6 text-center w-16">Hạng</th>
              <th class="py-3.5 px-6">Kiểm duyệt viên</th>
              <th class="py-3.5 px-6 text-center">Đã duyệt</th>
              <th class="py-3.5 px-6 text-center">Từ chối</th>
              <th class="py-3.5 px-6 text-center">Tổng xử lý</th>
              <th class="py-3.5 px-6 text-center">Tồn đọng (Pending)</th>
              <th class="py-3.5 px-6 text-center">Tỷ lệ xong</th>
              <th class="py-3.5 px-6 text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            <tr
              v-for="item in leaderboard"
              :key="item.id"
              class="hover:bg-gray-50/80 transition"
              :class="{ 'bg-amber-50/30': item.rank === 1 }"
            >
              <!-- Rank -->
              <td class="py-4 px-6 text-center">
                <span
                  class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold"
                  :class="getRankBadgeClass(item.rank)"
                >
                  {{ item.rank }}
                </span>
              </td>

              <!-- Moderator Info -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-sm flex-shrink-0">
                    {{ item.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <span class="font-medium text-gray-900 block">{{ item.name }}</span>
                    <span class="text-xs text-gray-400 block">{{ item.email }}</span>
                  </div>
                </div>
              </td>

              <!-- Approved -->
              <td class="py-4 px-6 text-center font-semibold text-emerald-600">
                {{ item.total_approved }}
              </td>

              <!-- Rejected -->
              <td class="py-4 px-6 text-center font-semibold text-red-500">
                {{ item.total_rejected }}
              </td>

              <!-- Total Processed -->
              <td class="py-4 px-6 text-center font-bold text-purple-600">
                {{ item.total_processed }}
              </td>

              <!-- Pending Backlog -->
              <td class="py-4 px-6 text-center">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="item.current_pending > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-600'"
                >
                  {{ item.current_pending }} bài
                </span>
              </td>

              <!-- Completion Rate -->
              <td class="py-4 px-6 text-center">
                <div class="flex flex-col items-center">
                  <span class="font-medium text-xs text-gray-900">{{ item.completion_rate }}%</span>
                  <div class="w-16 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                    <div
                      class="h-full bg-primary rounded-full transition-all duration-300"
                      :style="{ width: `${Math.min(100, item.completion_rate)}%` }"
                    ></div>
                  </div>
                </div>
              </td>

              <!-- Action: View their consignments -->
              <td class="py-4 px-6 text-right">
                <router-link
                  :to="`/consignments?moderator_id=${item.id}`"
                  class="text-xs font-medium text-primary hover:text-primary-dark transition inline-flex items-center gap-1"
                >
                  Xem bài phụ trách
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </router-link>
              </td>
            </tr>

            <tr v-if="leaderboard.length === 0">
              <td colspan="8" class="py-12 text-center text-gray-400 text-sm">
                Không tìm thấy dữ liệu kiểm duyệt viên nào
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '@/services/api'

const currentYear = new Date().getFullYear()
const currentMonth = new Date().getMonth() + 1

const selectedYear = ref(currentYear)
const selectedMonth = ref(currentMonth)
const selectedModeratorId = ref(null)
const loading = ref(false)

const moderatorsList = ref([])
const leaderboard = ref([])
const systemTotals = ref({
  total_moderators: 0,
  total_approved: 0,
  total_rejected: 0,
  total_processed: 0,
  total_pending: 0,
})

const availableYears = computed(() => {
  const years = []
  for (let y = currentYear + 1; y >= currentYear - 3; y--) {
    years.push(y)
  }
  return years
})

const topThree = computed(() => {
  return leaderboard.value.slice(0, 3)
})

function getRankBadgeClass(rank) {
  if (rank === 1) return 'bg-amber-400 text-amber-950 shadow-sm'
  if (rank === 2) return 'bg-slate-300 text-slate-800'
  if (rank === 3) return 'bg-amber-700/30 text-amber-900'
  return 'bg-gray-100 text-gray-600'
}

async function fetchModerators() {
  try {
    const res = await adminApi.getModeratorsList()
    if (res.data?.success && res.data?.data) {
      moderatorsList.value = res.data.data
    }
  } catch (err) {
    console.error('Error fetching moderators list:', err)
  }
}

async function fetchLeaderboard() {
  loading.value = true
  try {
    const params = {
      year: selectedYear.value,
    }
    if (selectedMonth.value) {
      params.month = selectedMonth.value
    }
    if (selectedModeratorId.value) {
      params.moderator_id = selectedModeratorId.value
    }
    const res = await adminApi.getModeratorsLeaderboard(params)
    if (res.data?.success && res.data?.data) {
      leaderboard.value = res.data.data.leaderboard || []
      systemTotals.value = res.data.data.system_totals || {}
    }
  } catch (err) {
    console.error('Error fetching leaderboard:', err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchModerators()
  await fetchLeaderboard()
})
</script>
