<template>
  <div class="flex h-screen bg-gray-50">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto flex flex-col">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />

      <main class="flex-1 p-3 sm:p-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                Kiểm duyệt viên
              </span>
              <h1 class="text-2xl font-bold text-gray-900">Bàn làm việc Kiểm duyệt</h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
              Kiểm duyệt bài đăng của người dùng và quản lý các sản phẩm bạn tự đăng
            </p>
          </div>
          <button @click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + Đăng sản phẩm mới
          </button>
        </div>

        <!-- Alert Notification -->
        <transition name="fade">
          <div v-if="alertMessage" class="mb-4 p-4 rounded-lg flex items-center justify-between shadow-sm" :class="alertType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'">
            <div class="flex items-center gap-2 text-sm font-medium">
              <svg v-if="alertType === 'success'" class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <svg v-else class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>{{ alertMessage }}</span>
            </div>
            <button @click="alertMessage = ''" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
          </div>
        </transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
              <p class="text-xs uppercase tracking-wider font-semibold text-amber-600 mb-1">Chờ kiểm duyệt</p>
              <h3 class="text-3xl font-extrabold text-gray-900">{{ stats.pending_consignments || 0 }}</h3>
              <p class="text-xs text-gray-500 mt-1">Bài người dùng gửi đang chờ duyệt</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
              <p class="text-xs uppercase tracking-wider font-semibold text-indigo-600 mb-1">Sản phẩm tôi đăng</p>
              <h3 class="text-3xl font-extrabold text-gray-900">{{ stats.my_consignments || 0 }}</h3>
              <p class="text-xs text-gray-500 mt-1">Bài do chính bạn tạo trong hệ thống</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
              <p class="text-xs uppercase tracking-wider font-semibold text-emerald-600 mb-1">Đã kiểm duyệt</p>
              <h3 class="text-3xl font-extrabold text-gray-900">{{ stats.my_approved_consignments || 0 }}</h3>
              <p class="text-xs text-gray-500 mt-1">Số bài bạn đã duyệt thành công</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
          <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
            <!-- Tabs -->
            <div class="inline-flex rounded-lg bg-gray-100 p-1 text-sm font-medium">
              <button @click="setTab('all')"
                :class="activeTab === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                class="px-4 py-1.5 rounded-md transition">
                Tất cả hiển thị
              </button>
              <button @click="setTab('pending')"
                :class="activeTab === 'pending' ? 'bg-white text-amber-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                class="px-4 py-1.5 rounded-md transition flex items-center gap-1.5">
                Chờ duyệt
                <span v-if="stats.pending_consignments > 0" class="px-1.5 py-0.2 bg-amber-100 text-amber-800 text-xs rounded-full">
                  {{ stats.pending_consignments }}
                </span>
              </button>
              <button @click="setTab('mine')"
                :class="activeTab === 'mine' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                class="px-4 py-1.5 rounded-md transition">
                Bài của tôi
              </button>
            </div>

            <!-- Search box -->
            <div class="flex-1 max-w-md">
              <div class="relative">
                <input v-model="searchQuery" @input="debouncedFetch" type="text"
                  placeholder="Tìm theo tiêu đề, mã BĐS, SĐT..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500 border-b border-gray-100">
                <tr>
                  <th class="px-5 py-3.5 font-semibold">Mã / STT</th>
                  <th class="px-5 py-3.5 font-semibold">Sản phẩm</th>
                  <th class="px-5 py-3.5 font-semibold">Danh mục</th>
                  <th class="px-5 py-3.5 font-semibold">Người đăng</th>
                  <th class="px-5 py-3.5 font-semibold">Giá</th>
                  <th class="px-5 py-3.5 font-semibold">Trạng thái</th>
                  <th class="px-5 py-3.5 font-semibold text-right">Thao tác</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                <!-- Skeleton loader -->
                <tr v-if="loading" v-for="n in 5" :key="'skel-'+n" class="animate-pulse">
                  <td class="px-5 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                  <td class="px-5 py-4"><div class="h-4 bg-gray-200 rounded w-48"></div></td>
                  <td class="px-5 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                  <td class="px-5 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                  <td class="px-5 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                  <td class="px-5 py-4"><div class="h-5 bg-gray-200 rounded-full w-16"></div></td>
                  <td class="px-5 py-4 text-right"><div class="h-6 bg-gray-200 rounded w-28 ml-auto"></div></td>
                </tr>

                <!-- Empty state -->
                <tr v-else-if="filteredConsignments.length === 0">
                  <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                      </svg>
                      <p class="font-medium text-gray-700">Không có sản phẩm nào</p>
                      <p class="text-xs text-gray-400 mt-1">Các sản phẩm người dùng sau khi duyệt/từ chối sẽ tự động không hiển thị tại đây.</p>
                    </div>
                  </td>
                </tr>

                <!-- Data rows -->
                <tr v-else v-for="item in filteredConsignments" :key="item.id" class="hover:bg-gray-50/70 transition">
                  <td class="px-5 py-4 font-mono text-xs font-semibold text-indigo-600">
                    {{ item.code || ('#' + item.id) }}
                    <span v-if="item.order_number" class="block text-gray-400 font-normal text-[11px]">STT: {{ item.order_number }}</span>
                  </td>
                  <td class="px-5 py-4 max-w-xs">
                    <div class="font-medium text-gray-900 truncate" :title="item.title">{{ item.title }}</div>
                    <div class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                      <span>{{ item.province || 'Toàn quốc' }}</span>
                      <span v-if="item.created_at">• {{ formatDate(item.created_at) }}</span>
                    </div>
                  </td>
                  <td class="px-5 py-4 text-gray-600">{{ item.category || '—' }}</td>
                  <td class="px-5 py-4">
                    <span v-if="isMyItem(item)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                      Bài của tôi
                    </span>
                    <span v-else class="text-gray-700 font-medium">
                      {{ item.user?.name || item.consigner_name || 'Khách vãng lai' }}
                    </span>
                  </td>
                  <td class="px-5 py-4 font-semibold text-gray-900">{{ formatCurrency(item.price) }}</td>
                  <td class="px-5 py-4">
                    <span :class="statusBadgeClass(item.status)" class="inline-block px-2.5 py-1 rounded-full text-xs font-medium">
                      {{ statusText(item.status) }}
                    </span>
                    <p v-if="item.status === 'rejected' && item.reject_reason" class="text-xs text-red-500 mt-1">
                      {{ item.reject_reason }}
                    </p>
                  </td>
                  <td class="px-5 py-4 text-right space-x-1 whitespace-nowrap">
                    <!-- Actions for user pending item (Moderation) -->
                    <template v-if="!isMyItem(item) && item.status === 'pending'">
                      <button @click="approve(item)"
                        :disabled="actionLoading === item.id"
                        class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium transition shadow-sm disabled:opacity-50">
                        {{ actionLoading === item.id ? 'Đang duyệt...' : '✓ Duyệt' }}
                      </button>
                      <button @click="openRejectModal(item)"
                        class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded text-xs font-medium transition">
                        Từ chối
                      </button>
                      <button @click="viewDetail(item)"
                        class="px-2.5 py-1.5 text-gray-600 hover:text-indigo-600 text-xs font-medium transition">
                        Xem
                      </button>
                    </template>

                    <!-- Actions for my own items -->
                    <template v-else-if="isMyItem(item)">
                      <button @click="openEditModal(item)"
                        class="px-2.5 py-1.5 text-indigo-600 hover:bg-indigo-50 rounded text-xs font-medium transition">
                        Sửa
                      </button>
                      <button @click="confirmDelete(item)"
                        class="px-2.5 py-1.5 text-red-600 hover:bg-red-50 rounded text-xs font-medium transition">
                        Xóa
                      </button>
                      <button @click="viewDetail(item)"
                        class="px-2.5 py-1.5 text-gray-600 hover:text-indigo-600 text-xs font-medium transition">
                        Xem
                      </button>
                    </template>

                    <!-- Fallback for other items -->
                    <template v-else>
                      <button @click="viewDetail(item)"
                        class="px-2.5 py-1.5 text-gray-600 hover:text-indigo-600 text-xs font-medium transition">
                        Xem
                      </button>
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="!loading && totalPages > 1" class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <span class="text-xs text-gray-500">
              Trang {{ currentPage }} / {{ totalPages }} (Tổng {{ totalItems }} sản phẩm)
            </span>
            <div class="flex items-center gap-1">
              <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                class="px-3 py-1 border rounded text-xs font-medium bg-white hover:bg-gray-50 disabled:opacity-40">
                Trước
              </button>
              <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                class="px-3 py-1 border rounded text-xs font-medium bg-white hover:bg-gray-50 disabled:opacity-40">
                Sau
              </button>
            </div>
          </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Từ chối duyệt bài đăng</h3>
            <p class="text-sm text-gray-500 mb-4">
              Nhập lý do từ chối để thông báo cho người đăng bài biết nguyên nhân:
            </p>
            <textarea v-model="rejectReason" rows="3"
              placeholder="Ví dụ: Thiếu hình ảnh thực tế, thông tin giá chưa chính xác..."
              class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:outline-none mb-4"></textarea>
            <div class="flex justify-end gap-2">
              <button @click="showRejectModal = false" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">
                Hủy
              </button>
              <button @click="submitReject" :disabled="rejecting"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                {{ rejecting ? 'Đang xử lý...' : 'Xác nhận từ chối' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Create / Edit Consignment Modal -->
        <div v-if="showFormModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 overflow-y-auto">
          <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
            <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-100">
              <h3 class="text-lg font-bold text-gray-900">
                {{ editingId ? 'Chỉnh sửa sản phẩm' : 'Đăng sản phẩm mới (Kiểm duyệt viên)' }}
              </h3>
              <button @click="showFormModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
            </div>

            <form @submit.prevent="saveConsignment" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Tiêu đề sản phẩm *</label>
                <input v-model="formData.title" type="text" required
                  placeholder="Bán đất mặt tiền đường..., diện tích..."
                  class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Giá bán (VNĐ) *</label>
                  <input v-model.number="formData.price" type="number" required min="0" step="1000"
                    placeholder="1500000000"
                    class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
                  <p class="text-xs text-indigo-600 mt-1 font-medium">{{ formatCurrency(formData.price) }}</p>
                </div>

                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Danh mục</label>
                  <select v-model="formData.category" class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="Đất thổ cư">Đất thổ cư</option>
                    <option value="Đất nông nghiệp">Đất nông nghiệp</option>
                    <option value="Đất nền dự án">Đất nền dự án</option>
                    <option value="Nhà đất">Nhà đất</option>
                    <option value="Biệt thự - Nghỉ dưỡng">Biệt thự - Nghỉ dưỡng</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Tỉnh / Thành phố</label>
                  <input v-model="formData.province" type="text" placeholder="Bình Dương, TP.HCM..."
                    class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Phường / Xã</label>
                  <input v-model="formData.ward" type="text" placeholder="Phường..."
                    class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Địa chỉ cụ thể</label>
                <input v-model="formData.address" type="text" placeholder="Số nhà, tên đường, khu vực..."
                  class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Mặt tiền (m)</label>
                  <input v-model.number="formData.frontage_actual" type="number" step="0.1"
                    class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Diện tích (m²)</label>
                  <input v-model.number="formData.residential_area" type="number" step="0.1"
                    class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">SĐT liên hệ</label>
                  <input v-model="formData.seller_phone" type="text" placeholder="09xxxxxxxx"
                    class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Link Google Maps</label>
                <input v-model="formData.google_map_link" type="text" placeholder="https://maps.google.com/..."
                  class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Link Ảnh đại diện</label>
                <input v-model="formData.featured_image" type="text" placeholder="https://..."
                  class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Mô tả chi tiết</label>
                <textarea v-model="formData.description" rows="4" placeholder="Mô tả thông tin chi tiết về bất động sản..."
                  class="w-full px-3.5 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
              </div>

              <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" @click="showFormModal = false" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">
                  Hủy
                </button>
                <button type="submit" :disabled="saving"
                  class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                  {{ saving ? 'Đang lưu...' : (editingId ? 'Cập nhật' : 'Đăng bài') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Detail Modal -->
        <div v-if="showDetailModal && selectedItem" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b mb-4">
              <h3 class="font-bold text-lg text-gray-900">Chi tiết sản phẩm</h3>
              <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="space-y-3 text-sm">
              <img v-if="selectedItem.featured_image" :src="selectedItem.featured_image" alt="Featured" class="w-full h-48 object-cover rounded-lg mb-3" />
              <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Tiêu đề:</span>
                <p class="font-semibold text-gray-900 text-base">{{ selectedItem.title }}</p>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <span class="text-xs text-gray-400 uppercase font-semibold">Giá:</span>
                  <p class="font-bold text-indigo-600 text-base">{{ formatCurrency(selectedItem.price) }}</p>
                </div>
                <div>
                  <span class="text-xs text-gray-400 uppercase font-semibold">Trạng thái:</span>
                  <p><span :class="statusBadgeClass(selectedItem.status)" class="px-2 py-0.5 rounded text-xs">{{ statusText(selectedItem.status) }}</span></p>
                </div>
              </div>
              <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Người đăng:</span>
                <p class="text-gray-800">{{ selectedItem.user?.name || selectedItem.consigner_name || '—' }} ({{ selectedItem.user?.email || 'N/A' }})</p>
              </div>
              <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Địa chỉ:</span>
                <p class="text-gray-800">{{ selectedItem.address || selectedItem.ward || selectedItem.province || '—' }}</p>
              </div>
              <div v-if="selectedItem.description">
                <span class="text-xs text-gray-400 uppercase font-semibold">Mô tả:</span>
                <p class="text-gray-600 text-xs whitespace-pre-line mt-1 bg-gray-50 p-2.5 rounded">{{ selectedItem.description }}</p>
              </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
              <button @click="showDetailModal = false" class="px-4 py-2 border rounded-lg text-sm">Đóng</button>
              <button v-if="!isMyItem(selectedItem) && selectedItem.status === 'pending'" @click="approve(selectedItem); showDetailModal = false"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium">
                Duyệt sản phẩm này
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
import { useAuthStore } from '@/store/auth'
import { adminApi } from '@/services/api'

const authStore = useAuthStore()

const loading = ref(false)
const consignments = ref([])
const activeTab = ref('all') // 'all' | 'pending' | 'mine'
const searchQuery = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const actionLoading = ref(null)

const stats = ref({
  pending_consignments: 0,
  my_consignments: 0,
  my_approved_consignments: 0
})

const alertMessage = ref('')
const alertType = ref('success')

// Reject Modal State
const showRejectModal = ref(false)
const rejectingItem = ref(null)
const rejectReason = ref('')
const rejecting = ref(false)

// Detail Modal
const showDetailModal = ref(false)
const selectedItem = ref(null)

// Form Modal (Create / Edit)
const showFormModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formData = ref({
  title: '',
  price: 0,
  category: 'Đất thổ cư',
  province: '',
  ward: '',
  address: '',
  frontage_actual: null,
  residential_area: null,
  seller_phone: '',
  google_map_link: '',
  featured_image: '',
  description: '',
  status: 'pending'
})

const isMyItem = (item) => {
  return item.user_id === authStore.userId
}

const filteredConsignments = computed(() => {
  if (activeTab.value === 'pending') {
    return consignments.value.filter(c => !isMyItem(c) && c.status === 'pending')
  }
  if (activeTab.value === 'mine') {
    return consignments.value.filter(c => isMyItem(c))
  }
  return consignments.value
})

const showAlert = (msg, type = 'success') => {
  alertMessage.value = msg
  alertType.value = type
  setTimeout(() => {
    if (alertMessage.value === msg) alertMessage.value = ''
  }, 4000)
}

const fetchDashboardStats = async () => {
  try {
    const res = await adminApi.getDashboard()
    if (res.data?.data) {
      stats.value = res.data.data
    }
  } catch (err) {
    console.error('Error fetching dashboard stats:', err)
  }
}

const fetchConsignments = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: 20,
      search: searchQuery.value || undefined
    }
    if (activeTab.value === 'pending') {
      params.status = 'pending'
    }

    const res = await adminApi.getConsignments(params)
    consignments.value = res.data?.data || []
    if (res.data?.meta) {
      totalPages.value = res.data.meta.last_page || 1
      totalItems.value = res.data.meta.total || 0
    }
  } catch (err) {
    console.error('Error fetching consignments:', err)
  } finally {
    loading.value = false
  }
}

let debounceTimer = null
const debouncedFetch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    currentPage.value = 1
    fetchConsignments()
  }, 400)
}

const setTab = (tab) => {
  activeTab.value = tab
  currentPage.value = 1
  fetchConsignments()
}

const goToPage = (p) => {
  currentPage.value = p
  fetchConsignments()
}

// Moderation Actions
const approve = async (item) => {
  actionLoading.value = item.id
  try {
    await adminApi.approveConsignment(item.id)
    showAlert(`Đã duyệt thành công sản phẩm "${item.title}". Sản phẩm đã được gỡ khỏi danh sách chờ duyệt.`)
    // Remove immediately from current view (sau khi kiểm duyệt thì các sản phẩm không hiển thị nữa)
    consignments.value = consignments.value.filter(c => c.id !== item.id)
    fetchDashboardStats()
  } catch (err) {
    console.error('Approve failed:', err)
    showAlert('Duyệt thất bại: ' + (err.response?.data?.message || err.message), 'error')
  } finally {
    actionLoading.value = null
  }
}

const openRejectModal = (item) => {
  rejectingItem.value = item
  rejectReason.value = ''
  showRejectModal.value = true
}

const submitReject = async () => {
  if (!rejectingItem.value) return
  rejecting.value = true
  try {
    await adminApi.rejectConsignment(rejectingItem.value.id, rejectReason.value)
    showAlert(`Đã từ chối sản phẩm "${rejectingItem.value.title}". Sản phẩm đã được gỡ khỏi danh sách.`)
    // Remove immediately from current view (sau khi kiểm duyệt thì các sản phẩm không hiển thị nữa)
    consignments.value = consignments.value.filter(c => c.id !== rejectingItem.value.id)
    showRejectModal.value = false
    fetchDashboardStats()
  } catch (err) {
    console.error('Reject failed:', err)
    showAlert('Từ chối thất bại: ' + (err.response?.data?.message || err.message), 'error')
  } finally {
    rejecting.value = false
  }
}

const viewDetail = (item) => {
  selectedItem.value = item
  showDetailModal.value = true
}

// My Item Actions
const openCreateModal = () => {
  editingId.value = null
  formData.value = {
    title: '',
    price: 0,
    category: 'Đất thổ cư',
    province: '',
    ward: '',
    address: '',
    frontage_actual: null,
    residential_area: null,
    seller_phone: '',
    google_map_link: '',
    featured_image: '',
    description: '',
    status: 'pending'
  }
  showFormModal.value = true
}

const openEditModal = (item) => {
  editingId.value = item.id
  formData.value = {
    title: item.title || '',
    price: item.price || 0,
    category: item.category || 'Đất thổ cư',
    province: item.province || '',
    ward: item.ward || '',
    address: item.address || '',
    frontage_actual: item.frontage_actual || null,
    residential_area: item.residential_area || null,
    seller_phone: item.seller_phone || '',
    google_map_link: item.google_map_link || '',
    featured_image: item.featured_image || '',
    description: item.description || '',
    status: item.status || 'pending'
  }
  showFormModal.value = true
}

const saveConsignment = async () => {
  saving.value = true
  try {
    if (editingId.value) {
      await adminApi.updateConsignment(editingId.value, formData.value)
      showAlert('Cập nhật sản phẩm thành công!')
    } else {
      await adminApi.createConsignment(formData.value)
      showAlert('Đăng sản phẩm mới thành công!')
    }
    showFormModal.value = false
    fetchConsignments()
    fetchDashboardStats()
  } catch (err) {
    console.error('Save failed:', err)
    showAlert('Lỗi khi lưu sản phẩm: ' + (err.response?.data?.message || err.message), 'error')
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (item) => {
  if (!confirm(`Bạn có chắc muốn xóa sản phẩm "${item.title}"?`)) return
  try {
    await adminApi.deleteConsignment(item.id)
    showAlert('Đã xóa sản phẩm thành công!')
    consignments.value = consignments.value.filter(c => c.id !== item.id)
    fetchDashboardStats()
  } catch (err) {
    showAlert('Xóa thất bại: ' + (err.response?.data?.message || err.message), 'error')
  }
}

// Helpers
const formatCurrency = (val) => {
  if (!val) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN')
}

const statusText = (st) => {
  const map = {
    pending: 'Chờ duyệt',
    approved: 'Đã duyệt',
    selling: 'Đang bán',
    rejected: 'Từ chối',
    sold: 'Đã bán',
    deactivated: 'Đã tắt',
    cancelled: 'Đã hủy'
  }
  return map[st] || st
}

const statusBadgeClass = (st) => {
  const map = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-emerald-100 text-emerald-800',
    selling: 'bg-blue-100 text-blue-800',
    rejected: 'bg-red-100 text-red-800',
    sold: 'bg-purple-100 text-purple-800',
    deactivated: 'bg-gray-100 text-gray-700',
    cancelled: 'bg-gray-100 text-gray-500'
  }
  return map[st] || 'bg-gray-100 text-gray-700'
}

onMounted(() => {
  fetchDashboardStats()
  fetchConsignments()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
