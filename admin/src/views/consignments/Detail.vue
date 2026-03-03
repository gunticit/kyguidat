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
            <button @click="approve" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Duyệt
            </button>
            <button @click="showRejectModal = true" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              Từ chối
            </button>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Images + Main Info -->
            <div class="lg:col-span-2 space-y-6">
              <!-- Images -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden" v-if="allImages.length > 0">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Hình ảnh</h2></div>
                <div class="p-4">
                  <!-- Main Image -->
                  <img :src="selectedImage" class="w-full h-80 object-cover rounded-lg mb-3" @error="$event.target.src='/images/placeholder.jpg'" />
                  <!-- Thumbnails -->
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
                <div class="p-4 space-y-3">
                  <InfoRow label="Tiêu đề" :value="data.title" bold />
                  <InfoRow label="Mã" :value="'#' + (data.order_number || data.id)" />
                  <InfoRow label="Danh mục" :value="data.category?.name || getCategoryName(data.category_id)" />
                  <InfoRow label="Giá" :value="formatCurrency(data.price)" highlight />
                  <InfoRow label="SEO URL" :value="data.seo_url" mono />
                  <InfoRow label="Ngày thông báo" :value="formatDate(data.notification_date)" />
                  <InfoRow label="Mô tả" :value="data.description" multiline />
                  <InfoRow label="Ghi chú" :value="data.notes" multiline />
                  <InfoRow label="Ghi chú nội bộ" :value="data.internal_note" multiline />
                  <InfoRow label="Từ khóa" :value="data.keywords" />
                </div>
              </div>

              <!-- Land Details -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Thông tin đất</h2></div>
                <div class="p-4">
                  <div class="grid grid-cols-2 gap-3">
                    <InfoRow label="Loại" :value="data.type" />
                    <InfoRow label="Hướng" :value="formatArray(data.land_directions)" />
                    <InfoRow label="Loại đất" :value="formatArray(data.land_types)" />
                    <InfoRow label="Mặt đường" :value="data.road_display" />
                    <InfoRow label="Mặt tiền (m)" :value="data.frontage_actual" />
                    <InfoRow label="Mặt tiền (khoảng)" :value="data.frontage_range" />
                    <InfoRow label="Diện tích (khoảng)" :value="data.area_range" />
                    <InfoRow label="Kích thước" :value="data.area_dimensions" />
                    <InfoRow label="Diện tích sàn (m²)" :value="data.floor_area" />
                    <InfoRow label="Thổ cư (m²)" :value="data.residential_area" />
                    <InfoRow label="Loại thổ cư" :value="data.residential_type" />
                    <InfoRow label="Có nhà" :value="data.has_house" />
                    <InfoRow label="Đường" :value="data.road" />
                    <InfoRow label="Tờ số" :value="data.sheet_number" />
                    <InfoRow label="Thửa số" :value="data.parcel_number" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column: Location + Consigner -->
            <div class="space-y-6">
              <!-- Location -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Vị trí</h2></div>
                <div class="p-4 space-y-3">
                  <InfoRow label="Tỉnh/TP" :value="data.province" />
                  <InfoRow label="Xã/Phường" :value="data.ward" />
                  <InfoRow label="Địa chỉ" :value="data.address" />
                  <InfoRow label="Lat" :value="data.latitude" mono />
                  <InfoRow label="Long" :value="data.longitude" mono />
                  <div v-if="data.google_map_link">
                    <span class="text-xs text-gray-500">Google Maps</span>
                    <a :href="data.google_map_link" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm block truncate">{{ data.google_map_link }}</a>
                  </div>
                </div>
              </div>

              <!-- Consigner -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Người ký gửi</h2></div>
                <div class="p-4 space-y-3">
                  <InfoRow label="Tên" :value="data.consigner_name" />
                  <InfoRow label="SĐT" :value="data.consigner_phone" />
                  <InfoRow label="Loại" :value="data.consigner_type" />
                  <InfoRow label="Người đăng" :value="data.user?.name" />
                  <InfoRow label="Email" :value="data.user?.email" />
                </div>
              </div>

              <!-- Meta -->
              <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-4 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Thông tin khác</h2></div>
                <div class="p-4 space-y-3">
                  <InfoRow label="Thứ tự" :value="data.display_order" />
                  <InfoRow label="Ngày tạo" :value="formatDate(data.created_at)" />
                  <InfoRow label="Cập nhật" :value="formatDate(data.updated_at)" />
                  <div v-if="data.reject_reason" class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
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
              <button @click="reject" :disabled="acting" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
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
import { useRoute, useRouter } from 'vue-router'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
import { adminApi } from '@/services/api'

const route = useRoute()
const router = useRouter()
const data = ref(null)
const loading = ref(true)
const error = ref('')
const selectedImage = ref('')
const showRejectModal = ref(false)
const rejectReason = ref('')
const acting = ref(false)

const categories = [
  { id: 1, name: 'Đất nền' },
  { id: 2, name: 'Nhà phố' },
  { id: 3, name: 'Biệt thự' },
  { id: 4, name: 'Căn hộ' },
  { id: 5, name: 'Đất nông nghiệp' },
  { id: 6, name: 'Khác' }
]

const getCategoryName = (id) => categories.find(c => c.id == id)?.name || `ID: ${id}`

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
  if (!v) return '—'
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

const statusClass = (s) => ({
  'bg-yellow-100 text-yellow-800': s === 'pending',
  'bg-green-100 text-green-800': s === 'approved',
  'bg-red-100 text-red-800': s === 'rejected',
}[s] || 'bg-gray-100 text-gray-800')

const statusText = (s) => ({ pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' }[s] || s)

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

const approve = async () => {
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

const reject = async () => {
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

// InfoRow component inline
const InfoRow = {
  props: {
    label: String,
    value: [String, Number],
    bold: Boolean,
    highlight: Boolean,
    mono: Boolean,
    multiline: Boolean,
  },
  template: `
    <div v-if="value && value !== '—'" class="flex flex-col">
      <span class="text-xs text-gray-500 mb-0.5">{{ label }}</span>
      <p :class="[
        bold ? 'font-semibold text-gray-900' : 'text-gray-700',
        highlight ? 'text-lg font-bold text-indigo-600' : '',
        mono ? 'font-mono text-sm' : '',
        multiline ? 'whitespace-pre-wrap' : ''
      ]" class="text-sm">{{ value }}</p>
    </div>
  `
}
</script>
