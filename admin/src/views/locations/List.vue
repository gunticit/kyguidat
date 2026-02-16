<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-white mb-6">Quản lý Địa giới hành chính</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Provinces Panel -->
      <div class="bg-gray-800 rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-white">Tỉnh / Thành phố</h2>
          <button @click="showProvinceModal = true; editingProvince = null; provinceForm = { name: '', sort_order: 0, is_active: true }"
                  class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700">
            + Thêm
          </button>
        </div>

        <div class="space-y-2">
          <div v-for="province in provinces" :key="province.id"
               @click="selectProvince(province)"
               class="flex items-center justify-between p-3 rounded-lg cursor-pointer transition"
               :class="selectedProvince?.id === province.id ? 'bg-blue-900 border border-blue-500' : 'bg-gray-700 hover:bg-gray-600'">
            <div>
              <span class="text-white font-medium">{{ province.name }}</span>
              <span class="text-gray-400 text-sm ml-2">({{ province.wards_count }} đơn vị)</span>
              <span v-if="!province.is_active" class="ml-2 text-xs bg-red-600 text-white px-1.5 py-0.5 rounded">Ẩn</span>
            </div>
            <div class="flex gap-1">
              <button @click.stop="editProvince(province)" class="text-yellow-400 hover:text-yellow-300 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button @click.stop="deleteProvince(province)" class="text-red-400 hover:text-red-300 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
          <p v-if="provinces.length === 0" class="text-gray-500 text-center py-4">Chưa có tỉnh/TP nào</p>
        </div>
      </div>

      <!-- Wards Panel -->
      <div class="bg-gray-800 rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-white">
            Xã / Phường / Đặc khu
            <span v-if="selectedProvince" class="text-blue-400 text-sm ml-1">({{ selectedProvince.name }})</span>
          </h2>
          <button v-if="selectedProvince" @click="showWardModal = true; editingWard = null; wardForm = { province_id: selectedProvince.id, name: '', type: 'phuong', sort_order: 0, is_active: true }"
                  class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-green-700">
            + Thêm
          </button>
        </div>

        <!-- Filter -->
        <div v-if="selectedProvince" class="flex gap-2 mb-3">
          <input v-model="wardSearch" placeholder="Tìm kiếm..." class="flex-1 bg-gray-700 text-white px-3 py-1.5 rounded-lg text-sm border border-gray-600 focus:border-blue-500 focus:outline-none"/>
          <select v-model="wardTypeFilter" class="bg-gray-700 text-white px-3 py-1.5 rounded-lg text-sm border border-gray-600">
            <option value="">Tất cả</option>
            <option value="phuong">Phường</option>
            <option value="xa">Xã</option>
            <option value="dac_khu">Đặc khu</option>
          </select>
        </div>

        <div class="space-y-1 max-h-[500px] overflow-y-auto">
          <div v-for="ward in filteredWards" :key="ward.id"
               class="flex items-center justify-between p-2 bg-gray-700 rounded hover:bg-gray-600">
            <div>
              <span class="text-white text-sm">{{ ward.name }}</span>
              <span class="ml-2 text-xs px-1.5 py-0.5 rounded" :class="typeClass(ward.type)">{{ typeLabel(ward.type) }}</span>
              <span v-if="!ward.is_active" class="ml-1 text-xs bg-red-600 text-white px-1.5 py-0.5 rounded">Ẩn</span>
            </div>
            <div class="flex gap-1">
              <button @click="editWard(ward)" class="text-yellow-400 hover:text-yellow-300 p-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button @click="deleteWard(ward)" class="text-red-400 hover:text-red-300 p-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
          <p v-if="!selectedProvince" class="text-gray-500 text-center py-8">← Chọn tỉnh/TP để xem danh sách</p>
          <p v-else-if="filteredWards.length === 0" class="text-gray-500 text-center py-4">Không có kết quả</p>
        </div>
      </div>
    </div>

    <!-- Province Modal -->
    <div v-if="showProvinceModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-white mb-4">{{ editingProvince ? 'Sửa' : 'Thêm' }} Tỉnh/TP</h3>
        <div class="space-y-3">
          <div>
            <label class="text-sm text-gray-300 block mb-1">Tên</label>
            <input v-model="provinceForm.name" class="w-full bg-gray-700 text-white px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"/>
          </div>
          <div>
            <label class="text-sm text-gray-300 block mb-1">Thứ tự</label>
            <input v-model.number="provinceForm.sort_order" type="number" class="w-full bg-gray-700 text-white px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"/>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="provinceForm.is_active" type="checkbox" id="province_active" class="rounded"/>
            <label for="province_active" class="text-sm text-gray-300">Hiển thị</label>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-5">
          <button @click="showProvinceModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500">Hủy</button>
          <button @click="saveProvince" :disabled="saving" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ saving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Ward Modal -->
    <div v-if="showWardModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-white mb-4">{{ editingWard ? 'Sửa' : 'Thêm' }} Xã/Phường</h3>
        <div class="space-y-3">
          <div>
            <label class="text-sm text-gray-300 block mb-1">Tỉnh/TP</label>
            <select v-model="wardForm.province_id" class="w-full bg-gray-700 text-white px-3 py-2 rounded-lg border border-gray-600">
              <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="text-sm text-gray-300 block mb-1">Tên</label>
            <input v-model="wardForm.name" class="w-full bg-gray-700 text-white px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"/>
          </div>
          <div>
            <label class="text-sm text-gray-300 block mb-1">Loại</label>
            <select v-model="wardForm.type" class="w-full bg-gray-700 text-white px-3 py-2 rounded-lg border border-gray-600">
              <option value="phuong">Phường</option>
              <option value="xa">Xã</option>
              <option value="dac_khu">Đặc khu</option>
            </select>
          </div>
          <div>
            <label class="text-sm text-gray-300 block mb-1">Thứ tự</label>
            <input v-model.number="wardForm.sort_order" type="number" class="w-full bg-gray-700 text-white px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:outline-none"/>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="wardForm.is_active" type="checkbox" id="ward_active" class="rounded"/>
            <label for="ward_active" class="text-sm text-gray-300">Hiển thị</label>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-5">
          <button @click="showWardModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500">Hủy</button>
          <button @click="saveWard" :disabled="saving" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
            {{ saving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirm -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold text-white mb-2">Xác nhận xóa</h3>
        <p class="text-gray-300 mb-4">{{ deleteMessage }}</p>
        <div class="flex justify-end gap-2">
          <button @click="showDeleteConfirm = false" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500">Hủy</button>
          <button @click="confirmDelete" :disabled="saving" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">Xóa</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { adminApi } from '@/services/api'

const provinces = ref([])
const wards = ref([])
const selectedProvince = ref(null)
const saving = ref(false)

// Province modal
const showProvinceModal = ref(false)
const editingProvince = ref(null)
const provinceForm = ref({ name: '', sort_order: 0, is_active: true })

// Ward modal
const showWardModal = ref(false)
const editingWard = ref(null)
const wardForm = ref({ province_id: null, name: '', type: 'phuong', sort_order: 0, is_active: true })

// Ward filters
const wardSearch = ref('')
const wardTypeFilter = ref('')

// Delete
const showDeleteConfirm = ref(false)
const deleteMessage = ref('')
const deleteCallback = ref(null)

const filteredWards = computed(() => {
  let result = wards.value
  if (wardSearch.value) {
    const s = wardSearch.value.toLowerCase()
    result = result.filter(w => w.name.toLowerCase().includes(s))
  }
  if (wardTypeFilter.value) {
    result = result.filter(w => w.type === wardTypeFilter.value)
  }
  return result
})

function typeLabel(type) {
  return { phuong: 'Phường', xa: 'Xã', dac_khu: 'Đặc khu' }[type] || type
}

function typeClass(type) {
  return {
    phuong: 'bg-blue-600/30 text-blue-300',
    xa: 'bg-green-600/30 text-green-300',
    dac_khu: 'bg-purple-600/30 text-purple-300'
  }[type] || 'bg-gray-600 text-gray-300'
}

async function loadProvinces() {
  try {
    const { data } = await adminApi.getProvinces()
    provinces.value = data.data
  } catch (e) {
    console.error('Failed to load provinces:', e)
  }
}

async function selectProvince(province) {
  selectedProvince.value = province
  wardSearch.value = ''
  wardTypeFilter.value = ''
  try {
    const { data } = await adminApi.getWards({ province_id: province.id })
    wards.value = data.data
  } catch (e) {
    console.error('Failed to load wards:', e)
  }
}

function editProvince(province) {
  editingProvince.value = province
  provinceForm.value = { name: province.name, sort_order: province.sort_order, is_active: province.is_active }
  showProvinceModal.value = true
}

async function saveProvince() {
  saving.value = true
  try {
    if (editingProvince.value) {
      await adminApi.updateProvince(editingProvince.value.id, provinceForm.value)
    } else {
      await adminApi.createProvince(provinceForm.value)
    }
    showProvinceModal.value = false
    await loadProvinces()
  } catch (e) {
    alert('Lỗi: ' + (e.response?.data?.message || e.message))
  } finally {
    saving.value = false
  }
}

function deleteProvince(province) {
  deleteMessage.value = `Xóa "${province.name}" và tất cả ${province.wards_count} xã/phường bên trong?`
  deleteCallback.value = async () => {
    await adminApi.deleteProvince(province.id)
    if (selectedProvince.value?.id === province.id) {
      selectedProvince.value = null
      wards.value = []
    }
    await loadProvinces()
  }
  showDeleteConfirm.value = true
}

function editWard(ward) {
  editingWard.value = ward
  wardForm.value = {
    province_id: ward.province_id,
    name: ward.name,
    type: ward.type,
    sort_order: ward.sort_order,
    is_active: ward.is_active
  }
  showWardModal.value = true
}

async function saveWard() {
  saving.value = true
  try {
    if (editingWard.value) {
      await adminApi.updateWard(editingWard.value.id, wardForm.value)
    } else {
      await adminApi.createWard(wardForm.value)
    }
    showWardModal.value = false
    if (selectedProvince.value) {
      await selectProvince(selectedProvince.value)
      await loadProvinces() // refresh ward counts
    }
  } catch (e) {
    alert('Lỗi: ' + (e.response?.data?.message || e.message))
  } finally {
    saving.value = false
  }
}

function deleteWard(ward) {
  deleteMessage.value = `Xóa "${ward.name}"?`
  deleteCallback.value = async () => {
    await adminApi.deleteWard(ward.id)
    if (selectedProvince.value) {
      await selectProvince(selectedProvince.value)
      await loadProvinces()
    }
  }
  showDeleteConfirm.value = true
}

async function confirmDelete() {
  saving.value = true
  try {
    await deleteCallback.value()
    showDeleteConfirm.value = false
  } catch (e) {
    alert('Lỗi: ' + (e.response?.data?.message || e.message))
  } finally {
    saving.value = false
  }
}

onMounted(loadProvinces)
</script>
