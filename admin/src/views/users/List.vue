<template>
  <div class="flex h-screen">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />
      <main class="p-3 sm:p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Quản lý Người dùng</h1>
        </div>
        
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="flex space-x-8">
            <button @click="activeTab = 'users'" 
              :class="['py-4 px-1 border-b-2 font-medium text-sm', 
                activeTab === 'users' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
              Người dùng
            </button>
            <button @click="activeTab = 'roles'" 
              :class="['py-4 px-1 border-b-2 font-medium text-sm', 
                activeTab === 'roles' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
              Nhóm người dùng
            </button>
          </nav>
        </div>

        <!-- Users Tab -->
        <div v-if="activeTab === 'users'">
          <div class="flex justify-between items-center mb-4">
            <div class="flex gap-4">
              <select v-model="userFilters.role" @change="fetchUsers" class="px-4 py-2 border rounded-lg">
                <option value="">Tất cả nhóm</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
              </select>
              <input v-model="userFilters.search" @input="fetchUsers" type="text" placeholder="Tìm kiếm..." 
                class="px-4 py-2 border rounded-lg">
            </div>
            <button @click="openUserModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              + Thêm người dùng
            </button>
          </div>

          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
              <thead class="bg-gray-50 text-left text-sm text-gray-500">
                <tr>
                  <th class="px-6 py-4">ID</th>
                  <th class="px-6 py-4">Tên</th>
                  <th class="px-6 py-4">Email</th>
                  <th class="px-6 py-4">Nhóm</th>
                  <th class="px-6 py-4">Ngày tạo</th>
                  <th class="px-6 py-4">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingUsers" class="border-t">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">Đang tải...</td>
                </tr>
                <tr v-else-if="users.length === 0" class="border-t">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">Chưa có dữ liệu</td>
                </tr>
                <tr v-else v-for="user in users" :key="user.id" class="border-t hover:bg-gray-50">
                  <td class="px-6 py-4">{{ user.id }}</td>
                  <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                  <td class="px-6 py-4">{{ user.email }}</td>
                  <td class="px-6 py-4">
                    <span v-for="role in user.roles" :key="role.id" 
                      class="inline-block px-2 py-1 text-xs rounded-full mr-1"
                      :class="getRoleBadgeClass(role.name)">
                      {{ role.name }}
                    </span>
                    <span v-if="!user.roles || user.roles.length === 0" class="text-gray-400 text-sm">-</span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                  <td class="px-6 py-4 space-x-2">
                    <button @click="openUserModal(user)" class="text-indigo-600 hover:underline text-sm">Sửa</button>
                    <button @click="confirmDeleteUser(user)" class="text-red-600 hover:underline text-sm">Xóa</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Roles Tab -->
        <div v-if="activeTab === 'roles'">
          <div class="flex justify-end mb-4">
            <button @click="openRoleModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              + Thêm nhóm
            </button>
          </div>

          <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
              <thead class="bg-gray-50 text-left text-sm text-gray-500">
                <tr>
                  <th class="px-6 py-4">ID</th>
                  <th class="px-6 py-4">Tên nhóm</th>
                  <th class="px-6 py-4">Mô tả</th>
                  <th class="px-6 py-4">Số người dùng</th>
                  <th class="px-6 py-4">Ngày tạo</th>
                  <th class="px-6 py-4">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingRoles" class="border-t">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">Đang tải...</td>
                </tr>
                <tr v-else-if="roles.length === 0" class="border-t">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">Chưa có dữ liệu</td>
                </tr>
                <tr v-else v-for="role in roles" :key="role.id" class="border-t hover:bg-gray-50">
                  <td class="px-6 py-4">{{ role.id }}</td>
                  <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 text-xs rounded-full" :class="getRoleBadgeClass(role.name)">
                      {{ role.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ role.description || '-' }}</td>
                  <td class="px-6 py-4">{{ role.users_count || 0 }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(role.created_at) }}</td>
                  <td class="px-6 py-4 space-x-2">
                    <button @click="openRoleModal(role)" class="text-indigo-600 hover:underline text-sm">Sửa</button>
                    <button v-if="!isSystemRole(role.name)" @click="confirmDeleteRole(role)" class="text-red-600 hover:underline text-sm">Xóa</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- User Modal -->
        <div v-if="showUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold mb-6">{{ editingUserId ? 'Cập nhật người dùng' : 'Thêm người dùng mới' }}</h2>
            <form @submit.prevent="saveUser" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên *</label>
                <input v-model="userForm.name" type="text" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input v-model="userForm.email" type="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ editingUserId ? 'Mật khẩu (để trống nếu không đổi)' : 'Mật khẩu *' }}</label>
                <input v-model="userForm.password" type="password" :required="!editingUserId" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                <input v-model="userForm.phone" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nhóm người dùng</label>
                <div class="space-y-2 max-h-40 overflow-y-auto border rounded-lg p-3">
                  <label v-for="role in roles" :key="role.id" class="flex items-center gap-2">
                    <input type="checkbox" v-model="userForm.roles" :value="role.id" class="rounded">
                    <span class="text-sm">{{ role.name }}</span>
                  </label>
                </div>
              </div>
              <p v-if="userError" class="text-red-500 text-sm">{{ userError }}</p>
              <div class="flex justify-end gap-4 pt-4">
                <button type="button" @click="showUserModal = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" :disabled="savingUser" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                  {{ savingUser ? 'Đang lưu...' : (editingUserId ? 'Cập nhật' : 'Tạo mới') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Role Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold mb-6">{{ editingRoleId ? 'Cập nhật nhóm' : 'Thêm nhóm mới' }}</h2>
            <form @submit.prevent="saveRole" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên nhóm *</label>
                <input v-model="roleForm.name" type="text" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                  :disabled="editingRoleId && isSystemRole(roleForm.name)">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                <textarea v-model="roleForm.description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
              </div>
              <p v-if="roleError" class="text-red-500 text-sm">{{ roleError }}</p>
              <div class="flex justify-end gap-4 pt-4">
                <button type="button" @click="showRoleModal = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" :disabled="savingRole" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                  {{ savingRole ? 'Đang lưu...' : (editingRoleId ? 'Cập nhật' : 'Tạo mới') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Delete User Modal -->
        <div v-if="showDeleteUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Xác nhận xóa</h2>
            <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa người dùng "{{ deletingUser?.name }}"?</p>
            <div class="flex justify-end gap-4">
              <button @click="showDeleteUserModal = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
              <button @click="deleteUser" :disabled="deletingUserLoading" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ deletingUserLoading ? 'Đang xóa...' : 'Xóa' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Delete Role Modal -->
        <div v-if="showDeleteRoleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Xác nhận xóa</h2>
            <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa nhóm "{{ deletingRole?.name }}"?</p>
            <div class="flex justify-end gap-4">
              <button @click="showDeleteRoleModal = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
              <button @click="deleteRole" :disabled="deletingRoleLoading" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ deletingRoleLoading ? 'Đang xóa...' : 'Xóa' }}
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
import { adminApi } from '@/services/api'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'

const activeTab = ref('users')

// Users state
const users = ref([])
const loadingUsers = ref(false)
const userFilters = ref({ role: '', search: '' })
const showUserModal = ref(false)
const editingUserId = ref(null)
const savingUser = ref(false)
const userError = ref('')
const userForm = ref({
  name: '',
  email: '',
  password: '',
  phone: '',
  roles: [],
  balance: 0
})
const showDeleteUserModal = ref(false)
const deletingUser = ref(null)
const deletingUserLoading = ref(false)

// Roles state
const roles = ref([])
const loadingRoles = ref(false)
const showRoleModal = ref(false)
const editingRoleId = ref(null)
const savingRole = ref(false)
const roleError = ref('')
const roleForm = ref({
  name: '',
  description: ''
})
const showDeleteRoleModal = ref(false)
const deletingRole = ref(null)
const deletingRoleLoading = ref(false)

// System roles that cannot be deleted
const systemRoles = ['admin', 'moderator', 'publisher']
const isSystemRole = (name) => systemRoles.includes(name?.toLowerCase())

// Fetch users
const fetchUsers = async () => {
  loadingUsers.value = true
  try {
    const res = await adminApi.getUsers({
      page: 1,
      role: userFilters.value.role,
      search: userFilters.value.search
    })
    users.value = res.data.data || []
  } catch (e) {
    console.error('Error fetching users:', e)
  } finally {
    loadingUsers.value = false
  }
}

// Fetch roles
const fetchRoles = async () => {
  loadingRoles.value = true
  try {
    const res = await adminApi.getRoles()
    roles.value = res.data.data || res.data || []
  } catch (e) {
    console.error('Error fetching roles:', e)
    // Fallback to hardcoded roles if API fails
    roles.value = [
      { id: 1, name: 'admin', description: 'Quản trị viên' },
      { id: 2, name: 'moderator', description: 'Người kiểm duyệt' },
      { id: 3, name: 'publisher', description: 'Người đăng tin' }
    ]
  } finally {
    loadingRoles.value = false
  }
}

// User CRUD
const openUserModal = (user = null) => {
  editingUserId.value = user?.id || null
  userForm.value = user ? {
    name: user.name || '',
    email: user.email || '',
    password: '',
    phone: user.phone || '',
    roles: user.roles?.map(r => r.id) || [],
    balance: user.balance || 0
  } : {
    name: '',
    email: '',
    password: '',
    phone: '',
    roles: [],
    balance: 0
  }
  userError.value = ''
  showUserModal.value = true
}

const saveUser = async () => {
  savingUser.value = true
  userError.value = ''
  try {
    const data = { ...userForm.value }
    if (!data.password) delete data.password
    
    if (editingUserId.value) {
      await adminApi.updateUser(editingUserId.value, data)
    } else {
      await adminApi.createUser(data)
    }
    showUserModal.value = false
    fetchUsers()
  } catch (e) {
    userError.value = e.response?.data?.message || 'Có lỗi xảy ra'
  } finally {
    savingUser.value = false
  }
}

const confirmDeleteUser = (user) => {
  deletingUser.value = user
  showDeleteUserModal.value = true
}

const deleteUser = async () => {
  deletingUserLoading.value = true
  try {
    await adminApi.deleteUser(deletingUser.value.id)
    showDeleteUserModal.value = false
    deletingUser.value = null
    fetchUsers()
  } catch (e) {
    console.error('Error deleting user:', e)
  } finally {
    deletingUserLoading.value = false
  }
}

// Role CRUD
const openRoleModal = (role = null) => {
  editingRoleId.value = role?.id || null
  roleForm.value = role ? {
    name: role.name || '',
    description: role.description || ''
  } : {
    name: '',
    description: ''
  }
  roleError.value = ''
  showRoleModal.value = true
}

const saveRole = async () => {
  savingRole.value = true
  roleError.value = ''
  try {
    if (editingRoleId.value) {
      await adminApi.updateRole(editingRoleId.value, roleForm.value)
    } else {
      await adminApi.createRole(roleForm.value)
    }
    showRoleModal.value = false
    fetchRoles()
  } catch (e) {
    roleError.value = e.response?.data?.message || 'Có lỗi xảy ra'
  } finally {
    savingRole.value = false
  }
}

const confirmDeleteRole = (role) => {
  deletingRole.value = role
  showDeleteRoleModal.value = true
}

const deleteRole = async () => {
  deletingRoleLoading.value = true
  try {
    await adminApi.deleteRole(deletingRole.value.id)
    showDeleteRoleModal.value = false
    deletingRole.value = null
    fetchRoles()
  } catch (e) {
    console.error('Error deleting role:', e)
  } finally {
    deletingRoleLoading.value = false
  }
}

// Helpers
const formatCurrency = (v) => v ? new Intl.NumberFormat('vi-VN').format(v) + ' đ' : '0 đ'
const formatDate = (d) => d ? new Date(d).toLocaleDateString('vi-VN') : ''
const getRoleBadgeClass = (name) => {
  const n = name?.toLowerCase()
  if (n === 'admin') return 'bg-red-100 text-red-800'
  if (n === 'moderator') return 'bg-blue-100 text-blue-800'
  if (n === 'publisher') return 'bg-green-100 text-green-800'
  return 'bg-gray-100 text-gray-800'
}

// Watch tab changes
watch(activeTab, (tab) => {
  if (tab === 'users') fetchUsers()
  if (tab === 'roles') fetchRoles()
})

onMounted(() => {
  fetchUsers()
  fetchRoles()
})
</script>
