<template>
  <div class="flex h-screen">
    <Sidebar ref="sidebar" />
    <div class="flex-1 overflow-auto">
      <Header @toggle-sidebar="$refs.sidebar?.open()" />
      <main class="p-3 sm:p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Quản lý Ký gửi</h1>
          <button v-if="canCreate" @click="openCreateModal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + Thêm mới
          </button>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3 items-center">
          <select v-model="filters.status" @change="filters.page = 1; fetchData()" class="px-3 py-2 border rounded-lg text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ duyệt</option>
            <option value="approved">Đã duyệt</option>
            <option value="rejected">Từ chối</option>
          </select>

          <select v-model="filters.province" @change="filters.page = 1; fetchData()" class="px-3 py-2 border rounded-lg text-sm">
            <option value="">Tỉnh / TP</option>
            <option v-for="prov in provinces" :key="prov.id" :value="prov.name">{{ prov.name }}</option>
          </select>

          <select v-model="filters.consigner_name" @change="filters.page = 1; fetchData()" class="px-3 py-2 border rounded-lg text-sm">
            <option value="">Người đăng</option>
            <option v-for="name in uniqueConsigners" :key="name" :value="name">{{ name }}</option>
          </select>

          <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Từ khóa tìm kiếm..."
                 class="flex-1 min-w-[200px] px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
        </div>
        
        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="w-full">
            <thead class="bg-gray-50 text-left text-sm text-gray-500">
              <tr>
                <th class="px-6 py-4">STT</th>
                <th class="px-6 py-4">Tiêu đề</th>
                <th class="px-6 py-4">Người đăng</th>
                <th class="px-6 py-4">Giá</th>
                <th class="px-6 py-4">Trạng thái</th>
                <th class="px-6 py-4">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <template v-if="loading">
                <tr v-for="n in 5" :key="'skeleton-'+n" class="border-t animate-pulse">
                  <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-12"></div></td>
                  <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-48"></div></td>
                  <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                  <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-28"></div></td>
                  <td class="px-6 py-4"><div class="h-5 bg-gray-200 rounded-full w-16"></div></td>
                  <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                </tr>
              </template>
              <tr v-else-if="consignments.length === 0" class="border-t">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Chưa có dữ liệu</td>
              </tr>
              <tr v-else v-for="item in consignments" :key="item.id" class="border-t hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-semibold text-indigo-600">{{ item.order_number || '—' }}</td>
                <td class="px-6 py-4">{{ item.title }}</td>
                <td class="px-6 py-4 text-sm">{{ item.user?.name || 'N/A' }}</td>
                <td class="px-6 py-4">{{ formatCurrency(item.price) }}</td>
                <td class="px-6 py-4">
                  <span :class="statusClass(item.status)" class="px-2 py-1 rounded-full text-xs">
                    {{ statusText(item.status) }}
                  </span>
                  <p v-if="item.status === 'rejected' && item.reject_reason" class="text-xs text-red-500 mt-1">
                    Lý do: {{ item.reject_reason }}
                  </p>
                </td>
                <td class="px-6 py-4 space-x-2">
                  <template v-if="canApprove && item.status === 'pending'">
                    <button @click="approve(item.id)" class="text-green-600 hover:underline text-sm">Duyệt</button>
                    <button @click="openRejectModal(item)" class="text-red-600 hover:underline text-sm">Từ chối</button>
                  </template>
                  <template v-if="canEdit(item)">
                    <button @click="openEditModal(item)" class="text-indigo-600 hover:underline text-sm">Sửa</button>
                  </template>
                  <template v-if="canDelete(item)">
                    <button @click="confirmDelete(item)" class="text-red-600 hover:underline text-sm">Xóa</button>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- Pagination -->
          <div v-if="!loading && totalPages > 1" class="px-6 py-4 bg-gray-50 border-t flex items-center justify-between">
            <p class="text-sm text-gray-600">
              Hiển thị <span class="font-medium">{{ (currentPage - 1) * 15 + 1 }}</span>–<span class="font-medium">{{ Math.min(currentPage * 15, totalItems) }}</span> / <span class="font-medium">{{ totalItems }}</span> kết quả
            </p>
            <div class="flex items-center gap-1">
              <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                      class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
                ‹ Trước
              </button>
              <template v-for="page in paginationPages" :key="page">
                <span v-if="page === '...'" class="px-2 py-1.5 text-sm text-gray-400">…</span>
                <button v-else @click="goToPage(page)"
                        :class="page === currentPage ? 'bg-indigo-600 text-white border-indigo-600' : 'hover:bg-gray-100'"
                        class="px-3 py-1.5 text-sm border rounded-lg min-w-[36px]">
                  {{ page }}
                </button>
              </template>
              <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                      class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed">
                Sau ›
              </button>
            </div>
          </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeModal">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[95vh] overflow-y-auto" @click.stop>
            <div class="p-6 border-b sticky top-0 bg-white z-10">
              <h2 class="text-xl font-bold">{{ editingId ? 'Cập nhật Ký gửi' : 'Thêm Ký gửi mới' }}</h2>
            </div>
            <form @submit.prevent="saveConsignment" class="p-6 space-y-6">
              
              <!-- Section 1: Thông tin cơ bản -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Thông tin cơ bản</h3>
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên sản phẩm *</label>
                    <input v-model="form.title" type="text" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục *</label>
                    <select v-model="form.category_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn danh mục --</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số thứ tự</label>
                    <input v-model.number="form.order_number" type="number" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>

                </div>
              </div>

              <!-- Section 2: Mô tả sản phẩm (Quill Editor) -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Mô tả sản phẩm</h3>
                <div class="mb-4" id="description-editor-wrapper">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả sản phẩm</label>
                  <QuillEditor 
                    ref="descriptionEditor"
                    v-model:content="form.description" 
                    contentType="html"
                    theme="snow"
                    :toolbar="toolbarOptions"
                    style="min-height: 200px;"
                    @ready="setupQuillImageHandler"
                  />
                </div>
              </div>

              <!-- Section 3: Hình ảnh -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Hình ảnh</h3>
                
                <!-- Gallery images with management -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Bộ sưu tập ảnh ({{ (form.images || []).length }} ảnh)
                  </label>
                  <div class="flex flex-wrap gap-2 mb-3" v-if="form.images && form.images.length > 0">
                    <div v-for="(img, index) in form.images" :key="index" class="w-32 h-24 border rounded overflow-hidden relative group">
                      <img :src="img" class="w-full h-full object-cover" :alt="'Ảnh ' + (index + 1)"
                           @error="$event.target.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22128%22 height=%2296%22%3E%3Crect fill=%22%23e2e8f0%22 width=%22128%22 height=%2296%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%2394a3b8%22 font-size=%2212%22%3ELỗi ảnh%3C/text%3E%3C/svg%3E'">
                      <!-- Magnifying glass / zoom button -->
                      <button type="button" @click="openGalleryLightbox(index)"
                              class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-80 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                        </svg>
                      </button>
                      <span class="absolute top-1 left-1 bg-black bg-opacity-50 text-white text-xs px-1 rounded z-10">{{ index + 1 }}</span>
                      <!-- Delete button -->
                      <button type="button" @click="removeGalleryImage(index)"
                              class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity z-10"
                              title="Xóa ảnh">✕</button>
                      <!-- Rotate button -->
                      <button type="button" @click="rotateGalleryImage(index)"
                              class="absolute bottom-1 left-1 bg-gray-700 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity z-10"
                              title="Xoay 90°">↻</button>
                      <!-- Set as featured button -->
                      <button type="button" @click="setAsFeatured(index)"
                              class="absolute bottom-1 right-1 bg-yellow-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity z-10"
                              title="Đặt làm ảnh đại diện">★</button>
                    </div>
                  </div>
                  <!-- Multi-image upload -->
                  <div class="flex items-center gap-2">
                    <label class="cursor-pointer bg-indigo-50 border-2 border-dashed border-indigo-300 rounded-lg px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-100 transition-colors">
                      <span v-if="!uploadingGallery">+ Thêm ảnh (tối đa 20)</span>
                      <span v-else class="animate-pulse">Đang tải lên...</span>
                      <input type="file" @change="handleGalleryUpload" accept="image/*" multiple class="hidden" :disabled="uploadingGallery">
                    </label>
                  </div>
                </div>

                <!-- Featured image -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh đại diện sản phẩm</label>
                  <div class="flex items-center gap-4">
                    <div v-if="form.featured_image" class="w-32 h-24 border-2 border-yellow-400 rounded overflow-hidden relative">
                      <img :src="form.featured_image" class="w-full h-full object-cover" alt="Featured">
                      <span class="absolute top-1 left-1 bg-yellow-500 text-white text-xs px-1 rounded">★ Đại diện</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <input type="file" @change="handleImageUpload" accept="image/*" class="px-4 py-2 border rounded-lg" :disabled="uploadingImage">
                      <span v-if="uploadingImage" class="text-sm text-indigo-600 animate-pulse">Đang tải lên...</span>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Hoặc hover ảnh gallery và bấm ★ để đặt làm đại diện</p>
                </div>
              </div>

              <!-- Section 4: Ghi chú (Quill Editor) -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Ghi chú</h3>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú (nếu có)</label>
                  <QuillEditor 
                    v-model:content="form.notes" 
                    contentType="html"
                    theme="snow"
                    :toolbar="simpleToolbar"
                    style="min-height: 100px;"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Thông báo nội bộ (nếu có)</label>
                  <QuillEditor 
                    v-model:content="form.internal_note" 
                    contentType="html"
                    theme="snow"
                    :toolbar="simpleToolbar"
                    style="min-height: 100px;"
                  />
                </div>
              </div>

              <!-- Section 5: Loại và hướng đất -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Phân loại đất</h3>

                <div class="grid grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hướng đất</label>
                    <div class="grid grid-cols-2 gap-2">
                      <label v-for="dir in landDirections" :key="dir.value" class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.land_directions" :value="dir.value" class="rounded">
                        <span class="text-sm">{{ dir.label }}</span>
                      </label>
                    </div>
                    <div class="mt-2 text-xs text-indigo-600">
                      <button type="button" @click="form.land_directions = landDirections.map(d => d.value)">all</button> /
                      <button type="button" @click="form.land_directions = []">none</button>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Loại đất</label>
                    <div class="grid grid-cols-1 gap-2">
                      <label v-for="lt in landTypes" :key="lt.value" class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.land_types" :value="lt.value" class="rounded">
                        <span class="text-sm">{{ lt.label }}</span>
                      </label>
                    </div>
                    <div class="mt-2 text-xs text-indigo-600">
                      <button type="button" @click="form.land_types = landTypes.map(t => t.value)">all</button> /
                      <button type="button" @click="form.land_types = []">none</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 6: Vị trí -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Vị trí</h3>
                <div class="grid grid-cols-3 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đường thể hiện</label>
                    <select v-model="form.road_display" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn --</option>
                      <option value="co">Có</option>
                      <option value="khong">Không</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố</label>
                    <select v-model="form.province" @change="onProvinceChange" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn tỉnh/TP --</option>
                      <option v-for="prov in provinces" :key="prov.id" :value="prov.name">{{ prov.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Xã/Phường</label>
                    <select v-model="form.ward" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn xã/phường --</option>
                      <option v-for="w in wards" :key="w.id" :value="w.name">{{ w.name }}</option>
                    </select>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chiều dài mặt tiền (Số thực tế)</label>
                    <input v-model.number="form.frontage_actual" type="number" step="0.1" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chiều dài mặt tiền</label>
                    <select v-model="form.frontage_range" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn --</option>
                      <option value="duoi_5m">Dưới 5 mét</option>
                      <option value="5_10m">Từ 5 - 10 mét</option>
                      <option value="10_20m">Từ 10 - 20 mét</option>
                      <option value="tren_20m">Trên 20 mét</option>
                    </select>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diện tích (khoảng)</label>
                    <select v-model="form.area_range" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn --</option>
                      <option value="duoi_100">Dưới 100 mét vuông</option>
                      <option value="100_200">Từ 100 - 200 mét vuông</option>
                      <option value="200_500">Từ 200 - 500 mét vuông</option>
                      <option value="500_1000">Từ 500 - 1000 mét vuông</option>
                      <option value="tren_1000">Trên 1000 mét vuông</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nhà trên đất</label>
                    <select v-model="form.has_house" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn --</option>
                      <option value="co">Có</option>
                      <option value="khong">Không</option>
                    </select>
                  </div>
                </div>
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                  <input v-model="form.address" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-4 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thổ cư (m)</label>
                    <input v-model.number="form.residential_area" type="number" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại thổ cư</label>
                    <select v-model="form.residential_type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Chọn --</option>
                      <option value="full">100% thổ cư</option>
                      <option value="partial">Một phần thổ cư</option>
                      <option value="none">Chưa có thổ cư</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diện tích sàn (m²)</label>
                    <input v-model.number="form.floor_area" type="number" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diện tích (VD: 10 x 24)</label>
                    <input v-model="form.area_dimensions" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                </div>
                <div class="grid grid-cols-4 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đường</label>
                    <input v-model="form.road" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                    <input v-model="form.latitude" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                    <input v-model="form.longitude" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link Google Map</label>
                    <input v-model="form.google_map_link" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                </div>
              </div>

              <!-- Section 7: Thông tin người ký gửi -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Thông tin người ký gửi</h3>
                <div class="grid grid-cols-5 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Người ký gửi</label>
                    <input v-model="form.consigner_name" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                    <input v-model="form.consigner_phone" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phân loại</label>
                    <select v-model="form.consigner_type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="">-- Phân loại --</option>
                      <option value="chinh_chu">Chính chủ</option>
                      <option value="moi_gioi">Môi giới</option>
                      <option value="khac">Khác</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số tờ</label>
                    <input v-model="form.sheet_number" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số thửa</label>
                    <input v-model="form.parcel_number" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                </div>
              </div>

              <!-- Section 8: SEO & Giá -->
              <div class="border-b pb-4">
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">SEO & Giá</h3>
                <div class="grid grid-cols-1 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Từ khóa sản phẩm</label>
                    <input v-model="form.keywords" type="text" placeholder="VD: 0364048679, p28158, p28157" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Giá *</label>
                      <input v-model.number="form.price" type="number" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <p v-if="form.price" class="text-xs mt-1">
                        <span class="text-indigo-600 font-medium">{{ formatCurrency(form.price) }}</span>
                        <span class="text-gray-500"> — </span>
                        <span class="text-green-600 font-medium italic">{{ priceInWords }}</span>
                      </p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">SEO URL link sản phẩm</label>
                      <input v-model="form.seo_url" @input="seoUrlManuallyEdited = true" type="text" placeholder="ban-dat-phuong-dong-xoai-1215" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <p v-if="form.seo_url" class="text-xs text-gray-500 mt-1">URL: khodat.com/bat-dong-san/<span class="text-indigo-600 font-medium">{{ form.seo_url }}</span></p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section 9: Trạng thái -->
              <div>
                <h3 class="text-lg font-semibold mb-4 text-indigo-700">Trạng thái</h3>
                <div class="grid grid-cols-2 gap-4">
                  <div v-if="authStore.isAdmin">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select v-model="form.status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                      <option value="pending">Chờ duyệt</option>
                      <option value="approved">Đã duyệt</option>
                      <option value="rejected">Từ chối</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự hiển thị</label>
                    <input v-model.number="form.display_order" type="number" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                  </div>
                </div>
              </div>

              <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
              <div class="flex justify-end gap-4 pt-4 border-t">
                <button type="button" @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                  {{ saving ? 'Đang lưu...' : (editingId ? 'Cập nhật' : 'Tạo mới') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Reject Reason Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Từ chối ký gửi</h2>
            <p class="text-gray-600 mb-4">Ký gửi: <strong>{{ rejectingItem?.title }}</strong></p>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Lý do từ chối *</label>
              <textarea v-model="rejectReason" rows="3" required placeholder="Nhập lý do từ chối..." 
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-end gap-4">
              <button @click="closeRejectModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
              <button @click="submitReject" :disabled="!rejectReason.trim() || rejecting" 
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ rejecting ? 'Đang xử lý...' : 'Từ chối' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Xác nhận xóa</h2>
            <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa ký gửi "{{ deletingItem?.title }}"?</p>
            <div class="flex justify-end gap-4">
              <button @click="showDeleteModal = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
              <button @click="deleteConsignment" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ deleting ? 'Đang xóa...' : 'Xóa' }}
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Gallery Lightbox -->
  <Teleport to="body">
    <div v-if="galleryLightboxOpen" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 backdrop-blur-sm" @click.self="closeGalleryLightbox">
      <button @click="closeGalleryLightbox" class="absolute top-4 right-4 z-50 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white text-2xl transition">&times;</button>
      <button v-if="form.images && form.images.length > 1" @click="galleryLightboxNav(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-50 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white text-2xl transition">&#8249;</button>
      <img v-if="form.images && form.images[galleryLightboxIndex]" :src="form.images[galleryLightboxIndex]" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-2xl select-none" />
      <button v-if="form.images && form.images.length > 1" @click="galleryLightboxNav(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-50 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white text-2xl transition">&#8250;</button>
      <div v-if="form.images && form.images.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/70 text-sm">{{ galleryLightboxIndex + 1 }} / {{ form.images.length }}</div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useConsignmentStore } from '@/store/consignment'
import { useAuthStore } from '@/store/auth'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { adminApi } from '@/services/api'

// Gallery Lightbox
const galleryLightboxOpen = ref(false)
const galleryLightboxIndex = ref(0)

const openGalleryLightbox = (index) => {
  galleryLightboxIndex.value = index
  galleryLightboxOpen.value = true
}

const closeGalleryLightbox = () => {
  galleryLightboxOpen.value = false
}

const galleryLightboxNav = (dir) => {
  if (!form.value.images || form.value.images.length === 0) return
  galleryLightboxIndex.value = (galleryLightboxIndex.value + dir + form.value.images.length) % form.value.images.length
}

const handleLightboxKeydown = (e) => {
  if (!galleryLightboxOpen.value) return
  if (e.key === 'Escape') closeGalleryLightbox()
  if (e.key === 'ArrowLeft') galleryLightboxNav(-1)
  if (e.key === 'ArrowRight') galleryLightboxNav(1)
}

const store = useConsignmentStore()
const authStore = useAuthStore()
const consignments = ref([])
const filters = ref({ status: '', province: '', consigner_name: '', search: '', page: 1 })
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)

// Pagination pages array (with ellipsis)
const paginationPages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
      pages.push(i)
    }
    if (current < total - 2) pages.push('...')
    pages.push(total)
  }
  return pages
})

const goToPage = (page) => {
  if (page < 1 || page > totalPages.value || page === currentPage.value) return
  filters.value.page = page
  fetchData()
}

// Unique consigner names from loaded data
const uniqueConsigners = computed(() => {
  const names = new Set()
  consignments.value.forEach(c => {
    if (c.consigner_name) names.add(c.consigner_name)
    if (c.user?.name) names.add(c.user.name)
  })
  return [...names].sort()
})

// Debounced search
let searchTimer = null
const debouncedSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    filters.value.page = 1
    fetchData()
  }, 400)
}
const loading = ref(false)
const error = ref('')
const seoUrlManuallyEdited = ref(false)

// Price in Vietnamese words
const priceInWords = computed(() => {
  const n = form.value?.price
  if (!n || n <= 0) return ''
  return numberToVietnamese(n)
})

function numberToVietnamese(n) {
  if (!n || n <= 0) return ''
  const units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ']
  const digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín']
  
  // Shorthand for common real estate prices
  if (n >= 1000000000 && n % 1000000000 === 0 && n / 1000000000 <= 9) return digits[n / 1000000000] + ' tỷ'
  if (n >= 1000000 && n % 1000000 === 0 && n / 1000000 <= 9) return digits[n / 1000000] + ' triệu'
  if (n >= 1000 && n % 1000 === 0 && n < 1000000 && n / 1000 <= 9) return digits[n / 1000] + ' nghìn'
  
  // General formatting
  const parts = []
  let remainder = n
  let unitIndex = 0
  while (remainder > 0) {
    const chunk = remainder % 1000
    if (chunk > 0) {
      const chunkText = readThreeDigits(chunk, digits, unitIndex > 0)
      parts.unshift(chunkText + (units[unitIndex] ? ' ' + units[unitIndex] : ''))
    }
    remainder = Math.floor(remainder / 1000)
    unitIndex++
  }
  return parts.join(' ').trim()
}

function readThreeDigits(n, digits, hasHigherUnit) {
  const h = Math.floor(n / 100)
  const t = Math.floor((n % 100) / 10)
  const u = n % 10
  let result = ''
  if (h > 0) result += digits[h] + ' trăm'
  else if (hasHigherUnit && (t > 0 || u > 0)) result += 'không trăm'
  if (t > 1) result += ' ' + digits[t] + ' mươi'
  else if (t === 1) result += ' mười'
  else if (t === 0 && h > 0 && u > 0) result += ' lẻ'
  if (u > 0) {
    if (t >= 2 && u === 1) result += ' mốt'
    else if (t >= 1 && u === 5) result += ' lăm'
    else if (t >= 2 && u === 4) result += ' tư'
    else result += ' ' + digits[u]
  }
  return result.trim()
}

// Vietnamese-to-ASCII slug generator
function slugify(str) {
  if (!str) return ''
  const map = {
    'à':'a','á':'a','ả':'a','ã':'a','ạ':'a','ă':'a','ằ':'a','ắ':'a','ẳ':'a','ẵ':'a','ặ':'a','â':'a','ầ':'a','ấ':'a','ẩ':'a','ẫ':'a','ậ':'a',
    'è':'e','é':'e','ẻ':'e','ẽ':'e','ẹ':'e','ê':'e','ề':'e','ế':'e','ể':'e','ễ':'e','ệ':'e',
    'ì':'i','í':'i','ỉ':'i','ĩ':'i','ị':'i',
    'ò':'o','ó':'o','ỏ':'o','õ':'o','ọ':'o','ô':'o','ồ':'o','ố':'o','ổ':'o','ỗ':'o','ộ':'o','ơ':'o','ờ':'o','ớ':'o','ở':'o','ỡ':'o','ợ':'o',
    'ù':'u','ú':'u','ủ':'u','ũ':'u','ụ':'u','ư':'u','ừ':'u','ứ':'u','ử':'u','ữ':'u','ự':'u',
    'ỳ':'y','ý':'y','ỷ':'y','ỹ':'y','ỵ':'y',
    'đ':'d',
    'À':'A','Á':'A','Ả':'A','Ã':'A','Ạ':'A','Ă':'A','Ằ':'A','Ắ':'A','Ẳ':'A','Ẵ':'A','Ặ':'A','Â':'A','Ầ':'A','Ấ':'A','Ẩ':'A','Ẫ':'A','Ậ':'A',
    'È':'E','É':'E','Ẻ':'E','Ẽ':'E','Ẹ':'E','Ê':'E','Ề':'E','Ế':'E','Ể':'E','Ễ':'E','Ệ':'E',
    'Ì':'I','Í':'I','Ỉ':'I','Ĩ':'I','Ị':'I',
    'Ò':'O','Ó':'O','Ỏ':'O','Õ':'O','Ọ':'O','Ô':'O','Ồ':'O','Ố':'O','Ổ':'O','Ỗ':'O','Ộ':'O','Ơ':'O','Ờ':'O','Ớ':'O','Ở':'O','Ỡ':'O','Ợ':'O',
    'Ù':'U','Ú':'U','Ủ':'U','Ũ':'U','Ụ':'U','Ư':'U','Ừ':'U','Ứ':'U','Ử':'U','Ữ':'U','Ự':'U',
    'Ỳ':'Y','Ý':'Y','Ỷ':'Y','Ỹ':'Y','Ỵ':'Y',
    'Đ':'D'
  }
  let result = str.split('').map(c => map[c] || c).join('')
  return result.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s_]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-+|-+$/g, '')
}

// Auto-generate seo_url from title (only when creating new, not editing)
watch(() => form.value?.title, (newTitle) => {
  if (!editingId.value && !seoUrlManuallyEdited.value) {
    form.value.seo_url = slugify(newTitle)
  }
})

// Quill toolbar options (similar to Summernote)
const toolbarOptions = [
  ['bold', 'italic', 'underline', 'strike'],
  ['blockquote', 'code-block'],
  [{ 'header': 1 }, { 'header': 2 }],
  [{ 'list': 'ordered' }, { 'list': 'bullet' }],
  [{ 'script': 'sub' }, { 'script': 'super' }],
  [{ 'indent': '-1' }, { 'indent': '+1' }],
  [{ 'direction': 'rtl' }],
  [{ 'size': ['small', false, 'large', 'huge'] }],
  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
  [{ 'color': [] }, { 'background': [] }],
  [{ 'font': [] }],
  [{ 'align': [] }],
  ['link', 'image', 'video'],
  ['clean']
]

// Simple toolbar for notes
const simpleToolbar = [
  ['bold', 'italic', 'underline'],
  [{ 'list': 'ordered' }, { 'list': 'bullet' }],
  ['link'],
  ['clean']
]

// Quill editor ref
const descriptionEditor = ref(null)

// Custom image handler for Quill — resize + upload to server as WebP
const setupQuillImageHandler = (editorInstance) => {
  const quill = editorInstance.__quill || editorInstance.getQuill?.() || editorInstance
  const toolbar = quill.getModule('toolbar')
  if (!toolbar) {
    console.warn('[Quill] toolbar module not found')
    return
  }
  console.log('[Quill] Custom image handler registered')

  toolbar.addHandler('image', () => {
    const input = document.createElement('input')
    input.setAttribute('type', 'file')
    input.setAttribute('accept', 'image/*')
    input.click()

    input.onchange = async () => {
      const file = input.files?.[0]
      if (!file) return

      console.log('[Quill] Selected file:', file.name, file.size, 'bytes', file.type)

      // Insert uploading placeholder
      const range = quill.getSelection(true) || { index: quill.getLength() - 1 }
      quill.insertText(range.index, '⏳ Đang upload ảnh...', { bold: true, color: '#6366f1' })
      const placeholderLength = '⏳ Đang upload ảnh...'.length

      try {
        // Client-side resize: max 1600px, compress to JPEG 0.85
        console.log('[Quill] Resizing image...')
        const resizedBlob = await resizeImageFile(file, 1600, 0.85)
        console.log('[Quill] Resized to:', resizedBlob.size, 'bytes')

        const token = localStorage.getItem('admin_token')
        const apiBase = (import.meta.env.VITE_API_URL || 'http://localhost:8080/api').replace(/\/admin\/?$/, '')
        const formData = new FormData()
        formData.append('image', resizedBlob, file.name.replace(/\.\w+$/, '.jpg'))
        formData.append('directory', 'consignments/content')

        console.log('[Quill] Uploading to:', `${apiBase}/upload/image-optimized`)
        const response = await fetch(`${apiBase}/upload/image-optimized`, {
          method: 'POST',
          headers: { 'Authorization': `Bearer ${token}` },
          body: formData
        })
        const data = await response.json()
        console.log('[Quill] Upload response:', data)

        // Remove placeholder
        quill.deleteText(range.index, placeholderLength)

        if (data.success && data.data?.url) {
          quill.insertEmbed(range.index, 'image', data.data.url)
          quill.setSelection(range.index + 1)
          console.log('[Quill] Image inserted:', data.data.url, data.data.width + 'x' + data.data.height)
        } else {
          quill.insertText(range.index, '[Upload thất bại]', { color: 'red' })
          alert('Upload ảnh thất bại: ' + (data.message || 'Lỗi không xác định'))
        }
      } catch (err) {
        // Remove placeholder on error
        quill.deleteText(range.index, placeholderLength)
        quill.insertText(range.index, '[Upload lỗi]', { color: 'red' })
        console.error('[Quill] Image upload error:', err)
        alert('Upload ảnh thất bại: ' + err.message)
      }
    }
  })
}

// Resize an image file client-side using Canvas
const resizeImageFile = (file, maxDimension = 1600, quality = 0.85) => {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const objectUrl = URL.createObjectURL(file)
    img.onload = () => {
      URL.revokeObjectURL(objectUrl)
      let { width, height } = img
      // Only resize if larger than maxDimension
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

// Categories (should be fetched from API)
const categories = ref([
  { id: 1, name: 'Đất nền' },
  { id: 2, name: 'Đất nông nghiệp' },
  { id: 3, name: 'Đất thổ cư' },
  { id: 4, name: 'Bất động sản' },
  { id: 5, name: 'Căn hộ/Chung cư' }
])

// Provinces & Wards — loaded from admin locations API
const provinces = ref([])
const wards = ref([])

async function loadProvinces() {
  try {
    const { data } = await adminApi.getProvinces()
    provinces.value = data.data || []
  } catch (e) {
    console.error('Failed to load provinces:', e)
  }
}

async function onProvinceChange() {
  form.value.ward = ''
  wards.value = []
  const selected = provinces.value.find(p => p.name === form.value.province)
  if (selected) {
    try {
      const { data } = await adminApi.getWards({ province_id: selected.id })
      wards.value = data.data || []
    } catch (e) {
      console.error('Failed to load wards:', e)
    }
  }
}

async function loadWardsForEdit(provinceName) {
  const selected = provinces.value.find(p => p.name === provinceName)
  if (selected) {
    try {
      const { data } = await adminApi.getWards({ province_id: selected.id })
      wards.value = data.data || []
    } catch (e) {
      console.error('Failed to load wards for edit:', e)
    }
  }
}

// Land directions
const landDirections = [
  { value: 'dong', label: 'Hướng Đông' },
  { value: 'dong_bac', label: 'Đông Bắc' },
  { value: 'dong_nam', label: 'Đông Nam' },
  { value: 'tay', label: 'Hướng Tây' },
  { value: 'tay_bac', label: 'Tây Bắc' },
  { value: 'tay_nam', label: 'Tây Nam' },
  { value: 'bac', label: 'Hướng Bắc' },
  { value: 'nam', label: 'Hướng Nam' }
]

// Land types
const landTypes = [
  { value: 'dat_nen', label: 'Đất nền' },
  { value: 'dat_tai_dinh_cu', label: 'Đất tái định cư' },
  { value: 'dat_sao', label: 'Đất sào' },
  { value: 'dat_ray', label: 'Đất rẫy' },
  { value: 'bat_dong_san_nghi_duong', label: 'Bất động sản nghỉ dưỡng' },
  { value: 'dat_phan_lo_du_an', label: 'Đất phân lô dự án' },
  { value: 'chung_cu', label: 'Chung cư' },
  { value: 'dang_su_dung_kinh_doanh', label: 'Đang sử dụng kinh doanh' },
  { value: 'khac', label: 'Khác' }
]

// Role-based permissions
const canCreate = computed(() => authStore.isAdmin || authStore.isPublisher)
const canApprove = computed(() => authStore.isAdmin || authStore.isModerator)

const canEdit = (item) => {
  if (authStore.isAdmin) return true
  if (authStore.isPublisher && item.user_id === authStore.userId) return true
  return false
}

const canDelete = (item) => {
  if (authStore.isAdmin) return true
  if (authStore.isPublisher && item.user_id === authStore.userId) return true
  return false
}

// Parse images field (may come as JSON string or array)
const parseImages = (images) => {
  if (!images) return []
  if (Array.isArray(images)) return images
  if (typeof images === 'string') {
    try { return JSON.parse(images) } catch { return [] }
  }
  return []
}

// Modal states
const showModal = ref(false)
const showDeleteModal = ref(false)
const showRejectModal = ref(false)
const editingId = ref(null)
const deletingItem = ref(null)
const rejectingItem = ref(null)
const rejectReason = ref('')
const saving = ref(false)
const deleting = ref(false)
const rejecting = ref(false)

// Default form with all fields
const defaultForm = {
  title: '',
  category_id: '',
  order_number: '',
  notification_date: '',
  description: '',
  featured_image: '',
  images: [],
  notes: '',
  internal_note: '',
  type: '',
  land_directions: [],
  land_types: [],
  road_display: '',
  province: '',
  ward: '',
  frontage_actual: '',
  frontage_range: '',
  area_range: '',
  has_house: '',
  address: '',
  residential_area: '',
  residential_type: '',
  road: '',
  area_dimensions: '',
  floor_area: '',
  latitude: '',
  longitude: '',
  google_map_link: '',
  consigner_name: '',
  consigner_phone: '',
  consigner_type: '',
  sheet_number: '',
  parcel_number: '',
  keywords: '',
  price: '',
  seo_url: '',
  status: 'pending',
  display_order: 1
}
const form = ref({ ...defaultForm })

const uploadingImage = ref(false)
const uploadingGallery = ref(false)

const handleImageUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  uploadingImage.value = true
  try {
    const { adminApi } = await import('@/services/api.js')
    const response = await adminApi.uploadOptimizedImage(file, 'consignments/featured')
    if (response.data?.success && response.data?.data?.url) {
      form.value.featured_image = response.data.data.url
    } else {
      console.error('Upload failed:', response.data?.message)
      alert('Upload ảnh thất bại: ' + (response.data?.message || 'Unknown error'))
    }
  } catch (err) {
    console.error('Upload error:', err)
    alert('Upload ảnh thất bại: ' + (err.response?.data?.message || err.message))
  } finally {
    uploadingImage.value = false
  }
}

const handleGalleryUpload = async (event) => {
  const files = Array.from(event.target.files || [])
  if (files.length === 0) return

  // Ensure images array exists
  if (!form.value.images) form.value.images = []

  // Limit total images to 20
  const remaining = 20 - form.value.images.length
  if (remaining <= 0) {
    alert('Đã đạt tối đa 20 ảnh')
    return
  }
  const filesToUpload = files.slice(0, remaining)

  uploadingGallery.value = true
  try {
    const { adminApi } = await import('@/services/api.js')
    const response = await adminApi.uploadMultipleOptimizedImages(filesToUpload, 'consignments/gallery')
    if (response.data?.success && response.data?.data) {
      const urls = response.data.data.map(item => item.url)
      form.value.images = [...form.value.images, ...urls]
      // Auto-set featured if none
      if (!form.value.featured_image && urls.length > 0) {
        form.value.featured_image = urls[0]
      }
    } else {
      alert('Upload ảnh thất bại: ' + (response.data?.message || 'Unknown error'))
    }
  } catch (err) {
    console.error('Gallery upload error:', err)
    alert('Upload ảnh thất bại: ' + (err.response?.data?.message || err.message))
  } finally {
    uploadingGallery.value = false
    event.target.value = '' // Reset input
  }
}

const removeGalleryImage = (index) => {
  if (!form.value.images) return
  const removedUrl = form.value.images[index]
  form.value.images = form.value.images.filter((_, i) => i !== index)
  // If removed image was featured, clear it
  if (form.value.featured_image === removedUrl) {
    form.value.featured_image = form.value.images.length > 0 ? form.value.images[0] : ''
  }
}

const setAsFeatured = (index) => {
  if (!form.value.images || !form.value.images[index]) return
  form.value.featured_image = form.value.images[index]
}

const rotateGalleryImage = async (index) => {
  if (!form.value.images || !form.value.images[index]) return
  const imageUrl = form.value.images[index]

  try {
    // Fetch the image
    const response = await fetch(imageUrl)
    const blob = await response.blob()

    // Load into Image element
    const img = new Image()
    img.crossOrigin = 'anonymous'
    const objectUrl = URL.createObjectURL(blob)
    img.src = objectUrl
    await new Promise((resolve) => { img.onload = resolve })

    // Draw rotated on canvas
    const canvas = document.createElement('canvas')
    canvas.width = img.height
    canvas.height = img.width
    const ctx = canvas.getContext('2d')
    ctx.translate(canvas.width / 2, canvas.height / 2)
    ctx.rotate(Math.PI / 2)
    ctx.drawImage(img, -img.width / 2, -img.height / 2)
    URL.revokeObjectURL(objectUrl)

    // Export to blob
    const rotatedBlob = await new Promise((resolve) => {
      canvas.toBlob((b) => resolve(b), 'image/jpeg', 0.92)
    })

    // Upload rotated image
    const file = new File([rotatedBlob], `rotated_${Date.now()}.jpg`, { type: 'image/jpeg' })
    const { adminApi: api } = await import('@/services/api.js')
    const uploadRes = await api.uploadOptimizedImage(file, 'consignments/gallery')
    if (uploadRes.data?.success && uploadRes.data?.data?.url) {
      const newUrl = uploadRes.data.data.url
      // Update gallery
      form.value.images = form.value.images.map((u, i) => i === index ? newUrl : u)
      // Update featured if it was this image
      if (form.value.featured_image === imageUrl) {
        form.value.featured_image = newUrl
      }
    } else {
      alert('Xoay ảnh thất bại: upload lỗi')
    }
  } catch (err) {
    console.error('Rotate error:', err)
    alert('Xoay ảnh thất bại: ' + (err.message || 'Unknown error'))
  }
}

const fetchData = async () => {
  loading.value = true
  await store.fetchConsignments(filters.value)
  consignments.value = store.consignments
  // Update pagination state from store
  const meta = store.meta || {}
  currentPage.value = meta.current_page || 1
  totalPages.value = meta.last_page || 1
  totalItems.value = meta.total || 0
  loading.value = false
}

const openCreateModal = () => {
  editingId.value = null
  form.value = { ...defaultForm, land_directions: [], land_types: [] }
  error.value = ''
  showModal.value = true
}

const openEditModal = async (item) => {
  editingId.value = item.id
  error.value = ''
  loading.value = true
  
  // Fetch full consignment details
  const fullItem = await store.fetchConsignment(item.id)
  const data = fullItem || item
  
  console.log('Edit Modal Data:', data) // Debug log
  
  // Parse land_directions and land_types if they are strings
  let landDirs = data.land_directions || []
  let landTps = data.land_types || []
  
  if (typeof landDirs === 'string') {
    try { landDirs = JSON.parse(landDirs) } catch { landDirs = [] }
  }
  if (typeof landTps === 'string') {
    try { landTps = JSON.parse(landTps) } catch { landTps = [] }
  }
  
  // Convert category_id to number to match select option values
  const categoryId = data.category_id ? parseInt(data.category_id, 10) : ''
  
  form.value = {
    title: data.title || '',
    category_id: categoryId,
    order_number: data.order_number || '',
  notification_date: data.notification_date ? data.notification_date.substring(0, 10) : '',
    description: data.description || '',
    featured_image: data.featured_image || '',
    images: parseImages(data.images),
    notes: data.notes || '',
    internal_note: data.internal_note || '',
    type: data.type || '',
    land_directions: Array.isArray(landDirs) ? landDirs : [],
    land_types: Array.isArray(landTps) ? landTps : [],
    road_display: data.road_display || '',
    province: data.province || '',
    ward: data.ward || '',
    frontage_actual: data.frontage_actual || '',
    frontage_range: data.frontage_range || '',
    area_range: data.area_range || '',
    has_house: data.has_house || '',
    address: data.address || '',
    residential_area: data.residential_area || '',
    residential_type: data.residential_type || '',
    road: data.road || '',
    area_dimensions: data.area_dimensions || '',
    floor_area: data.floor_area || '',
    latitude: data.latitude || '',
    longitude: data.longitude || '',
    google_map_link: data.google_map_link || '',
    consigner_name: data.consigner_name || '',
    consigner_phone: data.consigner_phone || '',
    consigner_type: data.consigner_type || '',
    sheet_number: data.sheet_number || '',
    parcel_number: data.parcel_number || '',
    keywords: data.keywords || '',
    price: data.price || '',
    seo_url: data.seo_url || '',
    status: data.status || 'pending',
    display_order: data.display_order || 1
  }
  
  console.log('Form after assignment:', form.value) // Debug log
  
  // Preload wards for existing province
  if (form.value.province) {
    await loadWardsForEdit(form.value.province)
  }
  
  loading.value = false
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingId.value = null
  seoUrlManuallyEdited.value = false
  form.value = { ...defaultForm, land_directions: [], land_types: [] }
}

const saveConsignment = async () => {
  saving.value = true
  error.value = ''
  
  // Create a non-reactive copy of form data to avoid v-model overriding description
  const payload = JSON.parse(JSON.stringify(form.value))
  
  // Sync Quill editor HTML into payload.description (NOT form.value)
  // v-model:content does not capture programmatic insertEmbed changes reliably
  const qlEditor = document.querySelector('#description-editor-wrapper .ql-editor')
  if (qlEditor) {
    const html = qlEditor.innerHTML
    payload.description = (html === '<p><br></p>' || html === '<p></p>') ? '' : html
    console.log('[Save] Description payload:', payload.description?.substring(0, 300))
  } else {
    console.warn('[Save] Could not find .ql-editor in DOM')
  }
  
  try {
    if (editingId.value) {
      const result = await store.updateConsignment(editingId.value, payload)
      if (result) {
        closeModal()
        fetchData()
      } else {
        error.value = store.error || 'Có lỗi xảy ra'
      }
    } else {
      const result = await store.createConsignment(payload)
      if (result) {
        closeModal()
        fetchData()
      } else {
        error.value = store.error || 'Có lỗi xảy ra'
      }
    }
  } catch (e) {
    error.value = e.message
  } finally {
    saving.value = false
  }
}

const confirmDelete = (item) => {
  deletingItem.value = item
  showDeleteModal.value = true
}

const deleteConsignment = async () => {
  deleting.value = true
  const success = await store.deleteConsignment(deletingItem.value.id)
  if (success) {
    showDeleteModal.value = false
    deletingItem.value = null
    fetchData()
  }
  deleting.value = false
}

const approve = async (id) => {
  if (await store.approveConsignment(id)) fetchData()
}

const openRejectModal = (item) => {
  rejectingItem.value = item
  rejectReason.value = ''
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectingItem.value = null
  rejectReason.value = ''
}

const submitReject = async () => {
  if (!rejectReason.value.trim()) return
  rejecting.value = true
  const success = await store.rejectConsignment(rejectingItem.value.id, rejectReason.value)
  if (success) {
    closeRejectModal()
    fetchData()
  }
  rejecting.value = false
}

const formatCurrency = (v) => v ? new Intl.NumberFormat('vi-VN').format(v) + ' đ' : '0 đ'
const statusText = (s) => ({ pending: 'Chờ duyệt', approved: 'Đã duyệt', rejected: 'Từ chối' }[s] || s)
const statusClass = (s) => ({
  pending: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800'
}[s] || 'bg-gray-100')

onMounted(async () => {
  document.addEventListener('keydown', handleLightboxKeydown)
  await Promise.all([fetchData(), loadProvinces()])
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleLightboxKeydown)
})
</script>

<style>
/* Override Quill editor styles */
.ql-container {
  min-height: 150px;
  font-size: 14px;
}
.ql-editor {
  min-height: 150px;
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
