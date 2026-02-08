<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <span class="text-2xl font-bold text-indigo-600">SànĐất</span>
            </a>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-indigo-600 transition">Trang chủ</a>
                <a href="{{ route('consignments.index') }}" class="text-gray-700 hover:text-indigo-600 transition">Bất
                    động sản</a>
                <a href="#" class="text-gray-700 hover:text-indigo-600 transition">Tin tức</a>
                <a href="#" class="text-gray-700 hover:text-indigo-600 transition">Liên hệ</a>
            </nav>

            <!-- CTA -->
            <div class="flex items-center space-x-4">
                <a href="http://localhost:3015"
                    class="hidden sm:inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Đăng tin
                </a>

                <!-- Mobile menu button -->
                <button class="md:hidden p-2" x-data @click="$dispatch('toggle-mobile-menu')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>