<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
      <h1 class="text-2xl font-bold text-center mb-6">Khodat Admin</h1>
      
      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input v-model="form.email" type="email" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
          <input v-model="form.password" type="password" required
                 class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        
        <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>
        
        <button type="submit" :disabled="loading"
                class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50">
          {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { login as apiLogin } from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await apiLogin(form.value.email, form.value.password)
    
    // API returns: { success: true, data: { user, token } }
    // Axios wraps in data, so: response.data = { success, data }
    if (response.data && response.data.success) {
      const { user, token } = response.data.data
      authStore.login(user, token)
      router.push('/')
    } else {
      error.value = 'Đăng nhập thất bại'
    }
  } catch (e) {
    if (e.response?.status === 401) {
      error.value = 'Email hoặc mật khẩu không đúng'
    } else {
      error.value = 'Đã có lỗi xảy ra: ' + (e.message || 'Unknown error')
    }
  } finally {
    loading.value = false
  }
}
</script>
