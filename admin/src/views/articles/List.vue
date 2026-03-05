<template>
  <div class="flex h-screen">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />
      <main class="p-3 sm:p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Quản lý Tin tức</h1>
          <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tạo bài viết
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex gap-4 items-center">
          <input v-model="search" @input="debouncedLoad" type="text" placeholder="Tìm kiếm bài viết..." 
                 class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" />
          <select v-model="statusFilter" @change="loadArticles" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Tất cả</option>
            <option value="draft">Nháp</option>
            <option value="published">Đã xuất bản</option>
          </select>
        </div>

        <!-- Articles Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div v-if="loading" class="p-8 text-center text-gray-500">Đang tải...</div>
          <div v-else-if="articles.length === 0" class="p-8 text-center text-gray-500">Chưa có bài viết nào</div>
          <table v-else class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bài viết</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày tạo</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="article in articles" :key="article.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img v-if="article.featured_image" :src="article.featured_image" class="w-16 h-10 object-cover rounded" />
                    <div class="w-16 h-10 bg-gray-200 rounded flex items-center justify-center" v-else>
                      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ article.title }}</p>
                      <p class="text-sm text-gray-500 truncate max-w-xs">{{ article.excerpt || '(Chưa có mô tả)' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="article.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                        class="px-2 py-1 rounded-full text-xs font-medium">
                    {{ article.status === 'published' ? 'Đã xuất bản' : 'Nháp' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(article.created_at) }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="openEdit(article)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Sửa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button @click="confirmDelete(article)" class="text-red-600 hover:text-red-900" title="Xóa">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-4 flex justify-center gap-2">
          <button v-for="p in totalPages" :key="p" @click="page = p; loadArticles()"
                  :class="p === page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                  class="px-3 py-1 rounded border text-sm">{{ p }}</button>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
              <h2 class="text-xl font-bold">{{ editingId ? 'Sửa bài viết' : 'Tạo bài viết mới' }}</h2>
              <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <form @submit.prevent="saveArticle" class="p-6 space-y-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả ngắn</label>
                <textarea v-model="form.excerpt" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" maxlength="500"></textarea>
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
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-indigo-500 transition-colors">
                  <div v-if="imagePreview || form.featured_image" class="mb-3">
                    <img :src="imagePreview || form.featured_image" alt="Preview" class="max-h-40 mx-auto object-contain rounded" />
                  </div>
                  <div v-else class="mb-3">
                    <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  </div>
                  <input type="file" @change="handleImageUpload" accept="image/*" class="hidden" ref="imageInput" />
                  <button type="button" @click="$refs.imageInput.click()" :disabled="uploadingImage" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                    {{ uploadingImage ? 'Đang tải lên...' : 'Chọn ảnh' }}
                  </button>
                  <p v-if="imageError" class="text-red-500 text-xs mt-2">{{ imageError }}</p>
                  <p class="text-xs text-gray-500 mt-2">PNG, JPG, WebP tối đa 5MB</p>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select v-model="form.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                  <option value="draft">Nháp</option>
                  <option value="published">Xuất bản</option>
                </select>
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
            <p class="text-gray-600 mb-4">Bạn có chắc muốn xóa bài viết "<strong>{{ deleteTarget?.title }}</strong>"?</p>
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

// Quill toolbar options
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
        formData.append('directory', 'articles/content')

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

const articles = ref([])
const loading = ref(false)
const search = ref('')
const statusFilter = ref('')
const page = ref(1)
const totalPages = ref(1)

// Modal state
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')
const form = ref({ title: '', slug: '', excerpt: '', content: '', featured_image: '', status: 'draft' })
const slugStatus = ref('') // '', 'checking', 'available', 'taken'
const slugUsedBy = ref('')
const slugManuallyEdited = ref(false)

// Delete modal
const showDeleteModal = ref(false)
const deleteTarget = ref(null)

// Image upload
const imagePreview = ref('')
const uploadingImage = ref(false)
const imageError = ref('')

const handleImageUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    imageError.value = 'Ảnh quá lớn (tối đa 5MB)'
    return
  }
  imageError.value = ''
  imagePreview.value = URL.createObjectURL(file)
  uploadingImage.value = true
  try {
    const res = await adminApi.uploadOptimizedImage(file, 'articles')
    if (res.data?.url) {
      form.value.featured_image = res.data.url
    } else if (res.data?.data?.url) {
      form.value.featured_image = res.data.data.url
    }
  } catch (e) {
    imageError.value = 'Lỗi tải ảnh: ' + (e.response?.data?.message || e.message)
    imagePreview.value = ''
  } finally {
    uploadingImage.value = false
  }
}

let debounceTimer = null
const debouncedLoad = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; loadArticles() }, 300)
}

const loadArticles = async () => {
  loading.value = true
  try {
    const res = await adminApi.getArticles({ search: search.value, status: statusFilter.value, page: page.value, per_page: 15 })
    articles.value = res.data.data || []
    totalPages.value = res.data.last_page || 1
  } catch (e) {
    console.error('Lỗi tải bài viết:', e)
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editingId.value = null
  form.value = { title: '', slug: '', excerpt: '', content: '', featured_image: '', status: 'draft' }
  formError.value = ''
  slugStatus.value = ''
  slugUsedBy.value = ''
  slugManuallyEdited.value = false
  imagePreview.value = ''
  imageError.value = ''
  showModal.value = true
}

const openEdit = (article) => {
  editingId.value = article.id
  form.value = { title: article.title, slug: article.slug, excerpt: article.excerpt || '', content: article.content || '', featured_image: article.featured_image || '', status: article.status }
  formError.value = ''
  slugStatus.value = ''
  slugUsedBy.value = ''
  slugManuallyEdited.value = true
  imagePreview.value = ''
  imageError.value = ''
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
      const res = await adminApi.checkSlug({ slug: form.value.slug, type: 'article', exclude_id: editingId.value || '' })
      slugStatus.value = res.data.available ? 'available' : 'taken'
      slugUsedBy.value = res.data.used_by || ''
    } catch { slugStatus.value = '' }
  }, 400)
}

// Auto-generate slug from title if user hasn't manually edited slug
watch(() => form.value.title, (newTitle) => {
  if (!slugManuallyEdited.value && newTitle) {
    form.value.slug = toSlug(newTitle)
    debouncedCheckSlug()
  }
})

const saveArticle = async () => {
  saving.value = true
  formError.value = ''
  try {
    if (editingId.value) {
      await adminApi.updateArticle(editingId.value, form.value)
    } else {
      await adminApi.createArticle(form.value)
    }
    showModal.value = false
    loadArticles()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Có lỗi xảy ra'
  } finally {
    saving.value = false
  }
}

const confirmDelete = (article) => {
  deleteTarget.value = article
  showDeleteModal.value = true
}

const doDelete = async () => {
  saving.value = true
  try {
    await adminApi.deleteArticle(deleteTarget.value.id)
    showDeleteModal.value = false
    loadArticles()
  } catch (e) {
    alert('Lỗi xóa: ' + (e.response?.data?.message || e.message))
  } finally {
    saving.value = false
  }
}

const formatDate = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(loadArticles)
</script>

<style>
/* Override Quill editor styles for articles */
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
