<template>
  <div class="flex h-screen">
    <Sidebar />
    <div class="flex-1 overflow-auto">
      <Header />
      <main class="p-6">
        <!-- Back + Title -->
        <div class="flex items-center gap-4 mb-6">
          <router-link to="/consignments" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          </router-link>
          <h1 class="text-2xl font-bold">Chi tiết Ký gửi #{{ data?.order_number || $route.params.id }}</h1>
          <span v-if="data" :class="statusClass(data.status)" class="px-3 py-1 rounded-full text-xs font-medium ml-2">{{ statusText(data.status) }}</span>
        </div>

        <div v-if="loading" class="text-center py-12 text-gray-500">Đang tải...</div>
        <div v-else-if="error" class="text-center py-12 text-red-500">{{ error }}</div>

        <template v-else-if="data">
          <!-- Action buttons -->
          <div class="flex gap-3 mb-6" v-if="data.status === 'pending'">
            <button @click="doApprove" :disabled="acting" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Duyệt
            </button>
            <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              Từ chối
            </button>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
              <!-- Images -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden" v-if="allImages.length > 0">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Hình ảnh</h2></div>
                <div class="p-4">
                  <img :src="selectedImage" class="w-full h-80 object-cover rounded-lg mb-3" @error="$event.target.src='/images/placeholder.jpg'" />
                  <div class="flex gap-2 overflow-x-auto pb-2">
                    <img v-for="(img, i) in allImages" :key="i" :src="img" @click="selectedImage = img"
                         class="w-20 h-16 object-cover rounded cursor-pointer border-2 transition flex-shrink-0"
                         :class="selectedImage === img ? 'border-indigo-500' : 'border-transparent hover:border-gray-300'"
                         @error="$event.target.style.display='none'" />
                  </div>
                </div>
              </div>

              <!-- Basic Info -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Thông tin cơ bản</h2></div>
                <div class="p-4">
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 w-40">Tiêu đề</td><td class="py-2 px-3 font-semibold">{{ data.title || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Mã</td><td class="py-2 px-3">{{ data.order_number || data.id }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Giá</td><td class="py-2 px-3 text-lg font-bold text-indigo-600">{{ formatCurrency(data.price) }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">SEO URL</td><td class="py-2 px-3 font-mono text-sm">{{ data.seo_url || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Ngày thông báo</td><td class="py-2 px-3">{{ formatDate(data.notification_date) }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 align-top">Mô tả</td><td class="py-2 px-3 whitespace-pre-wrap">{{ data.description || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 align-top">Ghi chú</td><td class="py-2 px-3 whitespace-pre-wrap">{{ data.notes || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 align-top">Ghi chú nội bộ</td><td class="py-2 px-3 whitespace-pre-wrap">{{ data.internal_note || '—' }}</td></tr>
                      <tr><td class="py-2 px-3 text-sm text-gray-500">Từ khóa</td><td class="py-2 px-3">{{ data.keywords || '—' }}</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Land Details -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Thông tin đất</h2></div>
                <div class="p-4">
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 w-40">Loại</td><td class="py-2 px-3">{{ data.type || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Hướng</td><td class="py-2 px-3">{{ formatArray(data.land_directions) }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Loại đất</td><td class="py-2 px-3">{{ formatArray(data.land_types) }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Mặt đường</td><td class="py-2 px-3">{{ data.road_display || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Mặt tiền (m)</td><td class="py-2 px-3">{{ data.frontage_actual || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Mặt tiền (khoảng)</td><td class="py-2 px-3">{{ data.frontage_range || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Diện tích (khoảng)</td><td class="py-2 px-3">{{ data.area_range || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Kích thước</td><td class="py-2 px-3">{{ data.area_dimensions || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Diện tích sàn (m²)</td><td class="py-2 px-3">{{ data.floor_area || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Thổ cư (m²)</td><td class="py-2 px-3">{{ data.residential_area || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Loại thổ cư</td><td class="py-2 px-3">{{ data.residential_type || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Có nhà</td><td class="py-2 px-3">{{ data.has_house || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Đường</td><td class="py-2 px-3">{{ data.road || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Tờ số</td><td class="py-2 px-3">{{ data.sheet_number || '—' }}</td></tr>
                      <tr><td class="py-2 px-3 text-sm text-gray-500">Thửa số</td><td class="py-2 px-3">{{ data.parcel_number || '—' }}</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
              <!-- Location -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Vị trí</h2></div>
                <div class="p-4">
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 w-24">Tỉnh/TP</td><td class="py-2 px-3">{{ data.province || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Xã/Phường</td><td class="py-2 px-3">{{ data.ward || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Địa chỉ</td><td class="py-2 px-3">{{ data.address || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Lat</td><td class="py-2 px-3 font-mono text-sm">{{ data.latitude || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Long</td><td class="py-2 px-3 font-mono text-sm">{{ data.longitude || '—' }}</td></tr>
                      <tr v-if="data.google_map_link"><td class="py-2 px-3 text-sm text-gray-500">Maps</td><td class="py-2 px-3"><a :href="data.google_map_link" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm truncate block">Xem bản đồ</a></td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Consigner -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Người ký gửi</h2></div>
                <div class="p-4">
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 w-24">Tên</td><td class="py-2 px-3">{{ data.consigner_name || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">SĐT</td><td class="py-2 px-3">{{ data.consigner_phone || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Loại</td><td class="py-2 px-3">{{ data.consigner_type || '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Người đăng</td><td class="py-2 px-3">{{ data.user?.name || '—' }}</td></tr>
                      <tr><td class="py-2 px-3 text-sm text-gray-500">Email</td><td class="py-2 px-3">{{ data.user?.email || '—' }}</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Meta -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Thông tin khác</h2></div>
                <div class="p-4">
                  <table class="w-full">
                    <tbody>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500 w-24">Thứ tự</td><td class="py-2 px-3">{{ data.display_order ?? '—' }}</td></tr>
                      <tr class="border-b"><td class="py-2 px-3 text-sm text-gray-500">Ngày tạo</td><td class="py-2 px-3">{{ formatDate(data.created_at) }}</td></tr>
                      <tr><td class="py-2 px-3 text-sm text-gray-500">Cập nhật</td><td class="py-2 px-3">{{ formatDate(data.updated_at) }}</td></tr>
                    </tbody>
                  </table>
                  <div v-if="data.reject_reason" class="mt-3 p-3 bg-red-50 rounded-lg border border-red-200">
                    <span class="text-xs font-medium text-red-600">Lý do từ chối:</span>
                    <p class="text-sm text-red-700 mt-1">{{ data.reject_reason }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full">
            <h3 class="text-lg font-bold mb-3">Từ chối ký gửi</h3>
            <textarea v-model="rejectReason" rows="3" placeholder="Nhập lý do từ chối..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 mb-4"></textarea>
            <div class="flex justify-end gap-3">
              <button @click="showRejectModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
              <button @click="doReject" :disabled="acting" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ acting ? 'Đang xử lý...' : 'Từ chối' }}
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
import { adminApi } from '@/services/api'

const route = useRoute()
const data = ref(null)
const loading = ref(true)
const error = ref('')
const selectedImage = ref('')
const showRejectModal = ref(false)
const rejectReason = ref('')
const acting = ref(false)

const allImages = computed(() => {
  if (!data.value) return []
  let imgs = []
  if (data.value.featured_image) imgs.push(data.value.featured_image)
  let parsed = data.value.images
  if (typeof parsed === 'string') {
    try { parsed = JSON.parse(parsed) } catch { parsed = [] }
  }
  if (Array.isArray(parsed)) {
    parsed.forEach(img => { if (img && !imgs.includes(img)) imgs.push(img) })
  }
  return imgs
})

const formatCurrency = (v) => {
  if (!v && v !== 0) return '—'
  return new Intl.NumberFormat('vi-VN').format(v) + ' VNĐ'
}

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatArray = (val) => {
  if (!val) return '—'
  if (typeof val === 'string') {
    try { val = JSON.parse(val) } catch { return val }
  }
  return Array.isArray(val) && val.length ? val.join(', ') : '—'
}

const statusClass = (s) => {
  if (s === 'pending') return 'bg-yellow-100 text-yellow-800'
  if (s === 'approved') return 'bg-green-100 text-green-800'
  if (s === 'rejected') return 'bg-red-100 text-red-800'
  return 'bg-gray-100 text-gray-800'
}

const statusText = (s) => {
  if (s === 'pending') return 'Chờ duyệt'
  if (s === 'approved') return 'Đã duyệt'
  if (s === 'rejected') return 'Từ chối'
  return s
}

const loadData = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await adminApi.getConsignment(route.params.id)
    data.value = res.data.data || res.data
    if (allImages.value.length) selectedImage.value = allImages.value[0]
  } catch (e) {
    error.value = 'Không thể tải dữ liệu: ' + (e.response?.data?.message || e.message)
  } finally {
    loading.value = false
  }
}

const doApprove = async () => {
  acting.value = true
  try {
    await adminApi.approveConsignment(route.params.id)
    data.value.status = 'approved'
  } catch (e) {
    alert('Lỗi: ' + (e.response?.data?.message || e.message))
  } finally {
    acting.value = false
  }
}

const doReject = async () => {
  acting.value = true
  try {
    await adminApi.rejectConsignment(route.params.id, rejectReason.value)
    data.value.status = 'rejected'
    data.value.reject_reason = rejectReason.value
    showRejectModal.value = false
  } catch (e) {
    alert('Lỗi: ' + (e.response?.data?.message || e.message))
  } finally {
    acting.value = false
  }
}

onMounted(loadData)
</script>
