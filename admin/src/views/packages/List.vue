<template>
  <div class="flex h-screen">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />
      <main class="p-3 sm:p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Quản lý Gói đăng bài</h1>
          <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tạo gói mới
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex gap-4 items-center">
          <input v-model="search" @input="debouncedLoad" type="text" placeholder="Tìm kiếm gói..."
                 class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
          <select v-model="activeFilter" @change="loadPackages" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Tất cả</option>
            <option value="1">Đang hoạt động</option>
            <option value="0">Đã tắt</option>
          </select>
        </div>

        <!-- Packages Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div v-if="loading" class="p-8 text-center text-gray-500">Đang tải...</div>
          <div v-else-if="packages.length === 0" class="p-8 text-center text-gray-500">Chưa có gói nào</div>
          <table v-else class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gói</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Giá</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thời hạn</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lượt đăng</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Phổ biến</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Người dùng</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900">{{ pkg.name }}</p>
                    <p class="text-sm text-gray-500">{{ pkg.slug }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900">{{ pkg.formatted_price }}</p>
                  <p v-if="pkg.original_price && pkg.original_price > pkg.price" class="text-sm text-gray-400 line-through">
                    {{ formatPrice(pkg.original_price) }}
                  </p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ pkg.duration_months }} tháng</td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ pkg.post_limit === -1 ? 'Không giới hạn' : pkg.post_limit }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="pkg.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ pkg.is_active ? 'Hoạt động' : 'Tắt' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span v-if="pkg.is_popular" class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">⭐ Phổ biến</span>
                  <span v-else class="text-gray-400 text-sm">—</span>
                </td>
                <td class="px-6 py-4 text-center text-sm text-gray-700">{{ pkg.subscribers || 0 }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="openEdit(pkg)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Sửa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button @click="confirmDelete(pkg)" class="text-red-600 hover:text-red-900" title="Xóa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
              <h2 class="text-xl font-bold">{{ editingId ? 'Sửa gói' : 'Tạo gói mới' }}</h2>
              <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <form @submit.prevent="savePackage" class="p-6 space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên gói *</label>
                <input v-model="form.name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="VD: Gói Cơ bản" />
              </div>

              <!-- Slug -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (tự động nếu để trống)</label>
                <input v-model="form.slug" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="goi-co-ban" />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                <textarea v-model="form.description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Mô tả ngắn về gói"></textarea>
              </div>

              <!-- Price row -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Giá bán (VNĐ) *</label>
                  <input v-model.number="form.price" type="number" required min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Giá gốc (VNĐ)</label>
                  <input v-model.number="form.original_price" type="number" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Để tạo giảm giá" />
                </div>
              </div>

              <!-- Duration & Post Limit -->
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Thời hạn (tháng) *</label>
                  <input v-model.number="form.duration_months" type="number" required min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Lượt đăng (-1 = ∞) *</label>
                  <input v-model.number="form.post_limit" type="number" required min="-1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Bài nổi bật</label>
                  <input v-model.number="form.featured_posts" type="number" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                </div>
              </div>

              <!-- Features -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tính năng (mỗi dòng 1 tính năng)</label>
                <textarea v-model="featuresText" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Đăng bài ký gửi&#10;Hỗ trợ qua chat&#10;Hiển thị ưu tiên"></textarea>
              </div>

              <!-- Sort Order -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự hiển thị</label>
                <input v-model.number="form.sort_order" type="number" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="0" />
              </div>

              <!-- Toggles -->
              <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.is_active" type="checkbox" class="w-4 h-4 text-indigo-600 rounded" />
                  <span class="text-sm text-gray-700">Hoạt động</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.is_popular" type="checkbox" class="w-4 h-4 text-yellow-500 rounded" />
                  <span class="text-sm text-gray-700">⭐ Phổ biến nhất</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="form.priority_support" type="checkbox" class="w-4 h-4 text-green-600 rounded" />
                  <span class="text-sm text-gray-700">Hỗ trợ ưu tiên</span>
                </label>
              </div>

              <div v-if="formError" class="text-red-600 text-sm">{{ formError }}</div>

              <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Hủy</button>
                <button type="submit" :disabled="saving" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50">
                  {{ saving ? 'Đang lưu...' : (editingId ? 'Cập nhật' : 'Tạo mới') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Delete Confirm Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full">
            <h3 class="text-lg font-bold mb-2">Xác nhận xóa</h3>
            <p class="text-gray-600 mb-4">Bạn có chắc muốn xóa gói "<strong>{{ deleteTarget?.name }}</strong>"?</p>
            <div v-if="deleteError" class="text-red-600 text-sm mb-3">{{ deleteError }}</div>
            <div class="flex justify-end gap-3">
              <button @click="showDeleteModal = false; deleteError = ''" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
              <button @click="doDelete" :disabled="saving" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ saving ? 'Đang xóa...' : 'Xóa' }}
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
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
import { adminApi } from '@/services/api'

const packages = ref([])
const loading = ref(false)
const search = ref('')
const activeFilter = ref('')

// Modal state
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')
const form = ref(getEmptyForm())

// Features as text (one per line)
const featuresText = ref('')

// Delete modal
const showDeleteModal = ref(false)
const deleteTarget = ref(null)
const deleteError = ref('')

function getEmptyForm() {
  return {
    name: '',
    slug: '',
    description: '',
    duration_months: 1,
    price: 0,
    original_price: null,
    post_limit: 10,
    featured_posts: 0,
    priority_support: false,
    features: [],
    is_active: true,
    is_popular: false,
    sort_order: 0,
  }
}

let debounceTimer = null
const debouncedLoad = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadPackages(), 300)
}

const loadPackages = async () => {
  loading.value = true
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (activeFilter.value !== '') params.is_active = activeFilter.value
    const res = await adminApi.getPostingPackages(params)
    packages.value = res.data.data || []
  } catch (e) {
    console.error('Error loading packages:', e)
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editingId.value = null
  form.value = getEmptyForm()
  featuresText.value = ''
  formError.value = ''
  showModal.value = true
}

const openEdit = (pkg) => {
  editingId.value = pkg.id
  form.value = {
    name: pkg.name,
    slug: pkg.slug,
    description: pkg.description || '',
    duration_months: pkg.duration_months,
    price: Number(pkg.price),
    original_price: pkg.original_price ? Number(pkg.original_price) : null,
    post_limit: pkg.post_limit,
    featured_posts: pkg.featured_posts || 0,
    priority_support: pkg.priority_support || false,
    features: pkg.features || [],
    is_active: pkg.is_active,
    is_popular: pkg.is_popular,
    sort_order: pkg.sort_order || 0,
  }
  featuresText.value = (pkg.features || []).join('\n')
  formError.value = ''
  showModal.value = true
}

const savePackage = async () => {
  saving.value = true
  formError.value = ''
  try {
    // Parse features from text
    const features = featuresText.value
      .split('\n')
      .map(f => f.trim())
      .filter(f => f.length > 0)

    const data = { ...form.value, features }

    if (editingId.value) {
      await adminApi.updatePostingPackage(editingId.value, data)
    } else {
      await adminApi.createPostingPackage(data)
    }
    showModal.value = false
    loadPackages()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Có lỗi xảy ra'
  } finally {
    saving.value = false
  }
}

const confirmDelete = (pkg) => {
  deleteTarget.value = pkg
  deleteError.value = ''
  showDeleteModal.value = true
}

const doDelete = async () => {
  saving.value = true
  deleteError.value = ''
  try {
    await adminApi.deletePostingPackage(deleteTarget.value.id)
    showDeleteModal.value = false
    loadPackages()
  } catch (e) {
    deleteError.value = e.response?.data?.message || 'Lỗi xóa gói'
  } finally {
    saving.value = false
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price) + ' đ'
}

onMounted(loadPackages)
</script>
