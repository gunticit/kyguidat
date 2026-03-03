@php
    use Illuminate\Support\Facades\Storage;
    $settings = [];
    if (Storage::exists('settings.json')) {
        $settings = json_decode(Storage::get('settings.json'), true) ?? [];
    }
    $logo = isset($settings['logo']) ? preg_replace('#^https?://[^/]+#', '', $settings['logo']) : '';
    $favicon = isset($settings['favicon']) ? preg_replace('#^https?://[^/]+#', '', $settings['favicon']) : '';
    $siteName = $settings['siteName'] ?? 'SànĐất';
@endphp
<header class="bg-navy-800 border-b border-navy-600 sticky top-0 z-50" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-10 w-auto object-contain">
                @else
                    <span class="text-2xl font-bold text-green-400">{{ $siteName }}</span>
                @endif
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-gray-300 hover:text-green-400 transition">Trang chủ</a>
                <a href="{{ route('consignments.index') }}" class="text-gray-300 hover:text-green-400 transition">Bất
                    động sản</a>
                <a href="{{ route('articles.index') }}" class="text-gray-300 hover:text-green-400 transition">Tin
                    tức</a>
                <a href="{{ route('contact') }}" class="text-gray-300 hover:text-green-400 transition">Liên hệ</a>
            </nav>

            <!-- CTA + Theme Toggle -->
            <div class="flex items-center space-x-3">
                <!-- Theme Toggle Button -->
                <button onclick="toggleTheme()"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg bg-navy-700 hover:bg-navy-600 border border-navy-600 text-gray-300 transition"
                    title="Chuyển đổi sáng/tối">
                    <svg id="sun-icon" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg id="moon-icon" class="w-5 h-5 text-blue-300 hidden" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <a href="{{ env('APP_URL_SANDAT') }}"
                    class="hidden sm:inline-flex items-center px-5 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold shadow-lg shadow-green-500/25">
                    Đăng tin
                </a>

                <!-- Mobile menu button -->
                <button class="md:hidden p-2 text-gray-300" @click="mobileOpen = !mobileOpen">
                    <!-- Hamburger icon -->
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <!-- Close icon -->
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" x-cloak
        class="md:hidden bg-navy-800 border-t border-navy-600">
        <nav class="max-w-7xl mx-auto px-4 py-4 space-y-1">
            <a href="{{ route('home') }}"
                class="block px-3 py-2.5 rounded-lg text-gray-300 hover:text-green-400 hover:bg-navy-700 transition font-medium">
                Trang chủ
            </a>
            <a href="{{ route('articles.index') }}"
                class="block px-3 py-2.5 rounded-lg text-gray-300 hover:text-green-400 hover:bg-navy-700 transition font-medium">
                Tin tức
            </a>
            <a href="{{ route('contact') }}"
                class="block px-3 py-2.5 rounded-lg text-gray-300 hover:text-green-400 hover:bg-navy-700 transition font-medium">
                Liên hệ
            </a>
            <div class="pt-2 border-t border-navy-600 mt-2">
                <a href="{{ env('APP_URL_SANDAT') }}"
                    class="block w-full text-center px-4 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                    Đăng tin
                </a>
            </div>
        </nav>
    </div>
</header>