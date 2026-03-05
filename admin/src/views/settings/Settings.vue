<template>
  <div class="flex h-screen">
    <Sidebar />
    
    <div class="flex-1 overflow-auto">
      <Header />
      
      <main class="p-6">
        <h1 class="text-2xl font-bold mb-6">Cài đặt</h1>
        
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="flex gap-4">
            <button
              @click="activeTab = 'contact'"
              :class="[
                'py-3 px-4 border-b-2 font-medium text-sm transition-colors',
                activeTab === 'contact' 
                  ? 'border-indigo-600 text-indigo-600' 
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              ]"
            >
              Thông tin liên hệ
            </button>
            <button
              @click="activeTab = 'seo'"
              :class="[
                'py-3 px-4 border-b-2 font-medium text-sm transition-colors',
                activeTab === 'seo' 
                  ? 'border-indigo-600 text-indigo-600' 
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              ]"
            >
              SEO
            </button>
            <button
              @click="activeTab = 'api'"
              :class="[
                'py-3 px-4 border-b-2 font-medium text-sm transition-colors',
                activeTab === 'api' 
                  ? 'border-indigo-600 text-indigo-600' 
                  : 'border-transparent text-gray-500 hover:text-gray-700'
              ]"
            >
              API Keys
            </button>
          </nav>
        </div>
        
        <!-- Contact Tab -->
        <div v-if="activeTab === 'contact'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Contact Information Form -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">Thông tin liên hệ</h2>
            <form @submit.prevent="saveSettings">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email liên hệ</label>
                <input v-model="settings.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="contact@example.com" />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                <input v-model="settings.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="0123 456 789" />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ</label>
                <input v-model="settings.address" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="TP. Hồ Chí Minh" />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Facebook URL</label>
                <input v-model="settings.facebook" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://facebook.com/..." />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Số Zalo</label>
                <input v-model="settings.zalo" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="0123456789" />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">TikTok URL</label>
                <input v-model="settings.tiktok" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://tiktok.com/@..." />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">YouTube URL</label>
                <input v-model="settings.youtube" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://youtube.com/@..." />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Instagram URL</label>
                <input v-model="settings.instagram" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://instagram.com/..." />
              </div>
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tên website</label>
                <input v-model="settings.siteName" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="SànĐất" />
              </div>
              <div class="flex items-center gap-4">
                <button type="submit" :disabled="saving" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                  <svg v-if="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  {{ saving ? 'Đang lưu...' : 'Lưu cài đặt' }}
                </button>
                <span v-if="message" :class="messageClass" class="text-sm">{{ message }}</span>
              </div>
            </form>
          </div>

          <!-- Logo & Favicon Upload -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">Logo & Favicon</h2>
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Logo website</label>
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-indigo-500 transition-colors">
                <div v-if="logoPreview || settings.logo" class="mb-4">
                  <img :src="logoPreview || resolveUrl(settings.logo)" alt="Logo" class="max-h-24 mx-auto object-contain" />
                </div>
                <div v-else class="mb-4">
                  <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <input type="file" @change="handleLogoUpload" accept="image/*" class="hidden" ref="logoInput" />
                <button type="button" @click="$refs.logoInput.click()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Chọn ảnh logo</button>
                <p class="text-xs text-gray-500 mt-2">PNG, JPG tối đa 2MB</p>
              </div>
            </div>
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-indigo-500 transition-colors">
                <div v-if="faviconPreview || settings.favicon" class="mb-4">
                  <img :src="faviconPreview || resolveUrl(settings.favicon)" alt="Favicon" class="w-16 h-16 mx-auto object-contain border border-gray-200 rounded" />
                </div>
                <div v-else class="mb-4">
                  <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <input type="file" @change="handleFaviconUpload" accept="image/*,.ico" class="hidden" ref="faviconInput" />
                <button type="button" @click="$refs.faviconInput.click()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Chọn favicon</button>
                <p class="text-xs text-gray-500 mt-2">ICO, PNG tối đa 1MB</p>
              </div>
            </div>
            <div v-if="uploadMessage" :class="uploadMessageClass" class="text-sm p-3 rounded-lg">{{ uploadMessage }}</div>
          </div>

          <!-- Bộ Công Thương Badge -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">Bộ Công Thương</h2>
            <div class="mb-4">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">Hiển thị hình Bộ Công Thương</label>
                <button type="button" @click="settings.show_bct_badge = !settings.show_bct_badge; saveSettings()"
                  :class="settings.show_bct_badge ? 'bg-indigo-600' : 'bg-gray-300'"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                  <span :class="settings.show_bct_badge ? 'translate-x-6' : 'translate-x-1'"
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" />
                </button>
              </div>
              <p class="text-xs text-gray-500 mt-1">Bật để hiển thị hình đã khai báo Bộ Công Thương dưới footer</p>
            </div>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-indigo-500 transition-colors">
              <div v-if="bctPreview || settings.bct_image" class="mb-4">
                <img :src="bctPreview || resolveUrl(settings.bct_image)" alt="BCT" class="max-h-24 mx-auto object-contain" />
              </div>
              <div v-else class="mb-4">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              </div>
              <input type="file" @change="handleBctUpload" accept="image/*" class="hidden" ref="bctInput" />
              <button type="button" @click="$refs.bctInput.click()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Chọn hình BCT</button>
              <p class="text-xs text-gray-500 mt-2">PNG, JPG tối đa 2MB</p>
            </div>
          </div>
        </div>

        <!-- SEO Tab -->
        <div v-if="activeTab === 'seo'" class="max-w-3xl">
          <form @submit.prevent="saveSeo">
            <!-- Meta Tags -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
              <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <h2 class="text-lg font-semibold">Meta Tags</h2>
              </div>
              <p class="text-sm text-gray-500 mb-4">Cấu hình các thẻ meta cho trang chủ website</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                  <input v-model="seo.metaTitle" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Kho Đất - Website bất động sản uy tín" />
                  <p class="text-xs text-gray-400 mt-1">Khuyến nghị: 50-60 ký tự</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                  <textarea v-model="seo.metaDescription" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Mô tả ngắn gọn về website..."></textarea>
                  <p class="text-xs text-gray-400 mt-1">Khuyến nghị: 150-160 ký tự</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                  <input v-model="seo.metaKeywords" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="bất động sản, nhà đất, mua bán đất" />
                  <p class="text-xs text-gray-400 mt-1">Cách nhau bằng dấu phẩy</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Canonical URL</label>
                  <input v-model="seo.canonicalUrl" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://khodat.com" />
                </div>
              </div>
            </div>

            <!-- Open Graph -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
              <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                <h2 class="text-lg font-semibold">Open Graph (Facebook)</h2>
              </div>
              <p class="text-sm text-gray-500 mb-4">Cấu hình hiển thị khi chia sẻ trên Facebook</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">OG Title</label>
                  <input v-model="seo.ogTitle" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Tiêu đề khi chia sẻ Facebook" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">OG Description</label>
                  <textarea v-model="seo.ogDescription" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Mô tả khi chia sẻ Facebook"></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">OG Image URL</label>
                  <input v-model="seo.ogImage" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://khodat.com/images/og-image.jpg" />
                  <p class="text-xs text-gray-400 mt-1">Khuyến nghị: 1200x630 pixels</p>
                </div>
              </div>
            </div>

            <!-- Twitter Cards -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
              <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-sky-500" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                <h2 class="text-lg font-semibold">Twitter Cards</h2>
              </div>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Twitter Title</label>
                  <input v-model="seo.twitterTitle" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Tiêu đề cho Twitter" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Twitter Description</label>
                  <textarea v-model="seo.twitterDescription" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Mô tả cho Twitter"></textarea>
                </div>
              </div>
            </div>

            <!-- Schema Markup -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
              <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                <h2 class="text-lg font-semibold">Schema Markup (JSON-LD)</h2>
              </div>
              <p class="text-sm text-gray-500 mb-4">Dữ liệu có cấu trúc giúp Google hiểu nội dung website</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Organization Name</label>
                  <input v-model="seo.schemaOrgName" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Công ty Kho Đất" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Organization Logo URL</label>
                  <input v-model="seo.schemaOrgLogo" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://khodat.com/logo.png" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Custom Schema JSON</label>
                  <textarea v-model="seo.schemaCustom" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder='{"@context": "https://schema.org", ...}'></textarea>
                  <p class="text-xs text-gray-400 mt-1">JSON-LD tùy chỉnh (tùy chọn)</p>
                </div>
              </div>
            </div>

            <!-- Robots & Sitemap -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
              <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                <h2 class="text-lg font-semibold">Robots & Sitemap</h2>
              </div>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Robots Meta</label>
                  <select v-model="seo.robotsMeta" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="index, follow">index, follow (Cho phép index)</option>
                    <option value="noindex, follow">noindex, follow (Không index)</option>
                    <option value="index, nofollow">index, nofollow (Index, không theo links)</option>
                    <option value="noindex, nofollow">noindex, nofollow (Không cho phép)</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Sitemap URL</label>
                  <input v-model="seo.sitemapUrl" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://khodat.com/sitemap.xml" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Google Site Verification</label>
                  <input v-model="seo.googleVerification" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="google-site-verification=..." />
                </div>
              </div>
            </div>

            <div class="flex items-center gap-4">
              <button type="submit" :disabled="savingSeo" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                <svg v-if="savingSeo" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                {{ savingSeo ? 'Đang lưu...' : 'Lưu SEO Settings' }}
              </button>
              <span v-if="seoMessage" :class="seoMessageClass" class="text-sm">{{ seoMessage }}</span>
            </div>
          </form>
        </div>

        <!-- API Keys Tab -->
        <div v-if="activeTab === 'api'" class="max-w-2xl">
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">API Keys</h2>
            <p class="text-sm text-gray-500 mb-6">Cấu hình các API keys cho Google Maps, Facebook và các dịch vụ khác.</p>
            
            <form @submit.prevent="saveApiKeys">
              <!-- Google Maps -->
              <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-2 mb-3">
                  <svg class="w-5 h-5 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                  <span class="font-medium">Google Maps</span>
                </div>
                <label class="block text-sm text-gray-600 mb-2">API Key</label>
                <input v-model="apiKeys.googleMapsKey" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="AIzaSy..." />
                <p class="text-xs text-gray-400 mt-2">Dùng để hiển thị bản đồ trong chi tiết bất động sản</p>
              </div>

              <!-- Facebook -->
              <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-2 mb-3">
                  <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                  <span class="font-medium">Facebook</span>
                </div>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm text-gray-600 mb-2">App ID</label>
                    <input v-model="apiKeys.facebookAppId" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="1234567890" />
                  </div>
                  <div>
                    <label class="block text-sm text-gray-600 mb-2">App Secret</label>
                    <div class="relative">
                      <input :type="showFbSecret ? 'text' : 'password'" v-model="apiKeys.facebookAppSecret" class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="••••••••••••" />
                      <button type="button" @click="showFbSecret = !showFbSecret" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg v-if="!showFbSecret" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                      </button>
                    </div>
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Dùng cho Facebook Login và chia sẻ</p>
              </div>

              <!-- Other APIs -->
              <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-2 mb-3">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                  <span class="font-medium">Khác</span>
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-2">Recaptcha Site Key</label>
                  <input v-model="apiKeys.recaptchaSiteKey" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="6Lc..." />
                </div>
              </div>

              <div class="flex items-center gap-4">
                <button type="submit" :disabled="savingApi" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center gap-2">
                  <svg v-if="savingApi" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  {{ savingApi ? 'Đang lưu...' : 'Lưu API Keys' }}
                </button>
                <span v-if="apiMessage" :class="apiMessageClass" class="text-sm">{{ apiMessage }}</span>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'

const sandatBaseUrl = import.meta.env.VITE_SANDAT_API_URL?.replace(/\/api$/, '') || ''
const apiUrl = import.meta.env.VITE_SANDAT_API_URL + '/settings'

// Resolve relative storage URLs to the san-dat domain
const resolveUrl = (url) => {
  if (!url) return ''
  // Already absolute URL
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('blob:')) return url
  // Relative path — prefix with san-dat base URL
  return sandatBaseUrl + url
}
const activeTab = ref('contact')

// Contact settings
const settings = ref({
  email: '', phone: '', address: '', facebook: '', zalo: '', tiktok: '', youtube: '', instagram: '', siteName: '', logo: '', favicon: '',
  show_bct_badge: false, bct_image: ''
})
const saving = ref(false)
const message = ref('')
const messageType = ref('success')
const logoPreview = ref('')
const faviconPreview = ref('')
const uploadMessage = ref('')
const uploadMessageType = ref('success')
const bctPreview = ref('')

// API Keys
const apiKeys = ref({
  googleMapsKey: '', facebookAppId: '', facebookAppSecret: '', recaptchaSiteKey: ''
})
const savingApi = ref(false)
const apiMessage = ref('')
const apiMessageType = ref('success')
const showFbSecret = ref(false)

// SEO
const seo = ref({
  metaTitle: '', metaDescription: '', metaKeywords: '', canonicalUrl: '',
  ogTitle: '', ogDescription: '', ogImage: '',
  twitterTitle: '', twitterDescription: '',
  schemaOrgName: '', schemaOrgLogo: '', schemaCustom: '',
  robotsMeta: 'index, follow', sitemapUrl: '', googleVerification: ''
})
const savingSeo = ref(false)
const seoMessage = ref('')
const seoMessageType = ref('success')

const messageClass = computed(() => messageType.value === 'success' ? 'text-green-600' : 'text-red-600')
const uploadMessageClass = computed(() => uploadMessageType.value === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700')
const apiMessageClass = computed(() => apiMessageType.value === 'success' ? 'text-green-600' : 'text-red-600')
const seoMessageClass = computed(() => seoMessageType.value === 'success' ? 'text-green-600' : 'text-red-600')

const loadSettings = async () => {
  try {
    const response = await axios.get(apiUrl)
    if (response.data) {
      settings.value = { ...settings.value, ...response.data }
      if (response.data.apiKeys) {
        apiKeys.value = { ...apiKeys.value, ...response.data.apiKeys }
      }
      if (response.data.seo) {
        seo.value = { ...seo.value, ...response.data.seo }
      }
    }
  } catch (error) {
    console.error('Failed to load settings:', error)
  }
}

const saveSettings = async () => {
  saving.value = true
  message.value = ''
  try {
    await axios.post(apiUrl, settings.value)
    message.value = '✓ Đã lưu thành công!'
    messageType.value = 'success'
  } catch (error) {
    message.value = 'Lỗi: Không thể lưu'
    messageType.value = 'error'
  } finally {
    saving.value = false
    setTimeout(() => { message.value = '' }, 3000)
  }
}

const saveApiKeys = async () => {
  savingApi.value = true
  apiMessage.value = ''
  try {
    await axios.post(`${apiUrl}/api-keys`, apiKeys.value)
    apiMessage.value = '✓ Đã lưu API Keys!'
    apiMessageType.value = 'success'
  } catch (error) {
    apiMessage.value = 'Lỗi: Không thể lưu'
    apiMessageType.value = 'error'
  } finally {
    savingApi.value = false
    setTimeout(() => { apiMessage.value = '' }, 3000)
  }
}

const saveSeo = async () => {
  savingSeo.value = true
  seoMessage.value = ''
  try {
    await axios.post(`${apiUrl}/seo`, seo.value)
    seoMessage.value = '✓ Đã lưu SEO Settings!'
    seoMessageType.value = 'success'
  } catch (error) {
    seoMessage.value = 'Lỗi: Không thể lưu'
    seoMessageType.value = 'error'
  } finally {
    savingSeo.value = false
    setTimeout(() => { seoMessage.value = '' }, 3000)
  }
}

const handleLogoUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) {
    uploadMessage.value = 'Logo quá lớn!'
    uploadMessageType.value = 'error'
    return
  }
  logoPreview.value = URL.createObjectURL(file)
  await uploadFile(file, 'logo')
}

const handleFaviconUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 1024 * 1024) {
    uploadMessage.value = 'Favicon quá lớn!'
    uploadMessageType.value = 'error'
    return
  }
  faviconPreview.value = URL.createObjectURL(file)
  await uploadFile(file, 'favicon')
}

const uploadFile = async (file, type) => {
  const formData = new FormData()
  formData.append('file', file)
  formData.append('type', type)
  try {
    const response = await axios.post(`${apiUrl}/upload`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    if (response.data.url) {
      settings.value[type] = response.data.url
      uploadMessage.value = `✓ Đã tải lên ${type}!`
      uploadMessageType.value = 'success'
      await saveSettings()
    }
  } catch (error) {
    uploadMessage.value = `Lỗi tải ${type}`
    uploadMessageType.value = 'error'
  }
  setTimeout(() => { uploadMessage.value = '' }, 3000)
}

const handleBctUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) {
    uploadMessage.value = 'Hình quá lớn!'
    uploadMessageType.value = 'error'
    return
  }
  bctPreview.value = URL.createObjectURL(file)
  await uploadFile(file, 'bct_image')
}

onMounted(() => { loadSettings() })
</script>
