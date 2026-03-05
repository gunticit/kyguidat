<template>
  <div class="flex h-screen">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />
      <main class="p-3 sm:p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Quản lý Trang</h1>
          <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tạo trang
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex gap-4 items-center">
          <input v-model="search" @input="debouncedLoad" type="text" placeholder="Tìm kiếm trang..." 
                 class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
          <select v-model="statusFilter" @change="loadPages" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Tất cả</option>
            <option value="draft">Nháp</option>
            <option value="published">Đã xuất bản</option>
          </select>
        </div>

        <!-- Pages Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div v-if="loading" class="p-8 text-center text-gray-500">Đang tải...</div>
          <div v-else-if="pages.length === 0" class="p-8 text-center text-gray-500">Chưa có trang nào</div>
          <table v-else class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thứ tự</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="page in pages" :key="page.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900">{{ page.title }}</p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ page.slug }}</td>
                <td class="px-6 py-4">
                  <span :class="page.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                        class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ page.status === 'published' ? 'Đã xuất bản' : 'Nháp' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ page.display_order || 0 }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="openEdit(page)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Sửa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button @click="confirmDelete(page)" class="text-red-600 hover:text-red-900" title="Xóa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-4 flex justify-center gap-2">
          <button v-for="p in totalPages" :key="p" @click="page = p; loadPages()"
                  :class="p === page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                  class="px-3 py-1 rounded border text-sm">{{ p }}</button>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
              <h2 class="text-xl font-bold">{{ editingId ? 'Sửa trang' : 'Tạo trang mới' }}</h2>
              <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <form @submit.prevent="savePage" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề *</label>
                <input v-model="form.title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (tự động nếu để trống)</label>
                <div class="relative">
                  <input v-model="form.slug" @input="debouncedCheckSlug" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" :class="slugStatus === 'taken' ? 'border-red-400' : slugStatus === 'available' ? 'border-green-400' : 'border-gray-300'" placeholder="tu-dong-tu-tieu-de" />
                  <span v-if="slugStatus === 'checking'" class="absolute right-3 top-2.5 text-gray-400 text-sm">Đang kiểm tra...</span>
                  <span v-else-if="slugStatus === 'available'" class="absolute right-3 top-2.5 text-green-500 text-sm">✓ Khả dụng</span>
                  <span v-else-if="slugStatus === 'taken'" class="absolute right-3 top-2.5 text-red-500 text-sm">✗ Đã dùng bởi {{ slugUsedBy }}</span>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung *</label>
                <QuillEditor 
                  ref="contentEditor"
                  v-model:content="form.content" 
                  contentType="html"
                  theme="snow"
                  :toolbar="toolbarOptions"
                  style="min-height: 300px;"
                  @ready="setupQuillImageHandler"
                />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                  <select v-model="form.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="draft">Nháp</option>
                    <option value="published">Xuất bản</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự hiển thị</label>
                  <input v-model.number="form.display_order" type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="0" />
                </div>
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
            <p class="text-gray-600 mb-4">Bạn có chắc muốn xóa trang "<strong>{{ deleteTarget?.title }}</strong>"?</p>
            <div class="flex justify-end gap-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
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
import { ref, onMounted, watch } from 'vue'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
import { adminApi } from '@/services/api'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const toolbarOptions = [
  ['bold', 'italic', 'underline', 'strike'],
  ['blockquote', 'code-block'],
  [{ 'header': 1 }, { 'header': 2 }],
  [{ 'list': 'ordered' }, { 'list': 'bullet' }],
  [{ 'indent': '-1' }, { 'indent': '+1' }],
  [{ 'size': ['small', false, 'large', 'huge'] }],
  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
  [{ 'color': [] }, { 'background': [] }],
  [{ 'font': [] }],
  [{ 'align': [] }],
  ['link', 'image', 'video'],
  ['clean']
]

// Quill editor ref
const contentEditor = ref(null)

// Custom image handler for Quill — resize + upload to server
const setupQuillImageHandler = (editorInstance) => {
  const quill = editorInstance.__quill || editorInstance.getQuill?.() || editorInstance
  const toolbar = quill.getModule('toolbar')
  if (!toolbar) return

  toolbar.addHandler('image', () => {
    const input = document.createElement('input')
    input.setAttribute('type', 'file')
    input.setAttribute('accept', 'image/*')
    input.click()

    input.onchange = async () => {
      const file = input.files?.[0]
      if (!file) return

      const range = quill.getSelection(true) || { index: quill.getLength() - 1 }
      quill.insertText(range.index, '⏳ Đang upload ảnh...', { bold: true, color: '#6366f1' })
      const placeholderLength = '⏳ Đang upload ảnh...'.length

      try {
        const resizedBlob = await resizeImageFile(file, 1600, 0.85)
        const token = localStorage.getItem('admin_token')
        const apiBase = (import.meta.env.VITE_API_URL || 'http://localhost:8080/api').replace(/\/admin\/?$/, '')
        const formData = new FormData()
        formData.append('image', resizedBlob, file.name.replace(/\.\w+$/, '.jpg'))
        formData.append('directory', 'pages/content')

        const response = await fetch(`${apiBase}/upload/image-optimized`, {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${token}` },
          body: formData
        })
        const data = await response.json()
        quill.deleteText(range.index, placeholderLength)

        if (data.success && data.data?.url) {
          quill.insertEmbed(range.index, 'image', data.data.url)
          quill.setSelection(range.index + 1)
        } else {
          quill.insertText(range.index, '[Upload thất bại]', { color: 'red' })
          alert('Upload ảnh thất bại: ' + (data.message || 'Lỗi không xác định'))
        }
      } catch (err) {
        quill.deleteText(range.index, placeholderLength)
        quill.insertText(range.index, '[Upload lỗi]', { color: 'red' })
        alert('Upload ảnh thất bại: ' + err.message)
      }
    }
  })
}

// Resize image file client-side
const resizeImageFile = (file, maxDimension = 1600, quality = 0.85) => {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const objectUrl = URL.createObjectURL(file)
    img.onload = () => {
      URL.revokeObjectURL(objectUrl)
      let { width, height } = img
      if (width > maxDimension || height > maxDimension) {
        const ratio = Math.min(maxDimension / width, maxDimension / height)
        width = Math.round(width * ratio)
        height = Math.round(height * ratio)
      }
      const canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, width, height)
      canvas.toBlob(
        (blob) => blob ? resolve(blob) : reject(new Error('Canvas toBlob failed')),
        'image/jpeg',
        quality
      )
    }
    img.onerror = () => {
      URL.revokeObjectURL(objectUrl)
      reject(new Error('Failed to load image'))
    }
    img.src = objectUrl
  })
}

const pages = ref([])
const loading = ref(false)
const search = ref('')
const statusFilter = ref('')
const page = ref(1)
const totalPages = ref(1)

// Modal
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')
const form = ref({ title: '', slug: '', content: '', status: 'draft', display_order: 0 })
const slugStatus = ref('')
const slugUsedBy = ref('')
const slugManuallyEdited = ref(false)

// Delete modal
const showDeleteModal = ref(false)
const deleteTarget = ref(null)

let debounceTimer = null
const debouncedLoad = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; loadPages() }, 300)
}

const loadPages = async () => {
  loading.value = true
  try {
    const res = await adminApi.getPages({ search: search.value, status: statusFilter.value, page: page.value, per_page: 20 })
    pages.value = res.data.data || []
    totalPages.value = res.data.last_page || 1
  } catch (e) {
    console.error('Lỗi tải trang:', e)
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editingId.value = null
  form.value = { title: '', slug: '', content: '', status: 'draft', display_order: 0 }
  formError.value = ''
  slugStatus.value = ''
  slugUsedBy.value = ''
  slugManuallyEdited.value = false
  showModal.value = true
}

const openEdit = (p) => {
  editingId.value = p.id
  form.value = { title: p.title, slug: p.slug, content: p.content || '', status: p.status, display_order: p.display_order || 0 }
  formError.value = ''
  slugStatus.value = ''
  slugUsedBy.value = ''
  slugManuallyEdited.value = true
  showModal.value = true
}

// Slug helpers
const toSlug = (str) => {
  return str.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd').replace(/Đ/g, 'd')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s_]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
}

let slugCheckTimer = null
const debouncedCheckSlug = () => {
  slugManuallyEdited.value = true
  clearTimeout(slugCheckTimer)
  if (!form.value.slug) { slugStatus.value = ''; return }
  slugStatus.value = 'checking'
  slugCheckTimer = setTimeout(async () => {
    try {
      const res = await adminApi.checkSlug({ slug: form.value.slug, type: 'page', exclude_id: editingId.value || '' })
      slugStatus.value = res.data.available ? 'available' : 'taken'
      slugUsedBy.value = res.data.used_by || ''
    } catch { slugStatus.value = '' }
  }, 400)
}

watch(() => form.value.title, (newTitle) => {
  if (!slugManuallyEdited.value && newTitle) {
    form.value.slug = toSlug(newTitle)
    debouncedCheckSlug()
  }
})

const savePage = async () => {
  saving.value = true
  formError.value = ''
  try {
    if (editingId.value) {
      await adminApi.updatePage(editingId.value, form.value)
    } else {
      await adminApi.createPage(form.value)
    }
    showModal.value = false
    loadPages()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Có lỗi xảy ra'
  } finally {
    saving.value = false
  }
}

const confirmDelete = (p) => {
  deleteTarget.value = p
  showDeleteModal.value = true
}

const doDelete = async () => {
  saving.value = true
  try {
    await adminApi.deletePage(deleteTarget.value.id)
    showDeleteModal.value = false
    loadPages()
  } catch (e) {
    alert('Lỗi xóa: ' + (e.response?.data?.message || e.message))
  } finally {
    saving.value = false
  }
}

onMounted(loadPages)
</script>

<style>
.ql-container {
  min-height: 250px;
  font-size: 14px;
}
.ql-editor {
  min-height: 250px;
}
.ql-toolbar.ql-snow {
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}
.ql-container.ql-snow {
  border-bottom-left-radius: 8px;
  border-bottom-right-radius: 8px;
}
</style>
