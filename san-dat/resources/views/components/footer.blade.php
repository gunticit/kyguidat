@php
    use Illuminate\Support\Facades\Storage;
    $settings = [];
    if (Storage::exists('settings.json')) {
        $settings = json_decode(Storage::get('settings.json'), true) ?? [];
    }
    $email = $settings['email'] ?? 'contact@sandat.vn';
    $phone = $settings['phone'] ?? '0123 456 789';
    $address = $settings['address'] ?? 'TP. Hồ Chí Minh';
    $siteName = $settings['siteName'] ?? 'SànĐất';
    $logo = isset($settings['logo']) ? preg_replace('#^https?://[^/]+#', '', $settings['logo']) : '';
    $showBctBadge = $settings['show_bct_badge'] ?? false;
    $bctImage = $settings['bct_image'] ?? '';
    $facebook = $settings['facebook'] ?? '';
    $tiktok = $settings['tiktok'] ?? '';
    $youtube = $settings['youtube'] ?? '';
    $instagram = $settings['instagram'] ?? '';
    $zalo = $settings['zalo'] ?? '';
@endphp
<footer class="bg-navy-900 border-t border-navy-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 sm:gap-10 gap-4">
            <!-- Brand -->
            <div class="text-center md:text-left">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="object-contain"
                        style="width: 100%; height: 80px; margin: 0;">
                @endif

                <!-- Social Media Icons -->
                @if($facebook || $tiktok || $youtube || $instagram || $zalo)
                    <div class="flex items-center gap-3 mt-4 justify-center md:justify-start">
                        @if($youtube)
                            <a href="{{ $youtube }}" target="_blank" rel="noopener noreferrer"
                                class="w-10 h-10 rounded-xl bg-navy-700 border border-navy-600 flex items-center justify-center text-gray-400 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-200"
                                title="YouTube">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <rect x="2" y="4" width="20" height="16" rx="4" />
                                    <path d="M10 9l5 3-5 3V9z" fill="currentColor" stroke="none" />
                                </svg>
                            </a>
                        @endif
                        @if($facebook)
                            <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer"
                                class="w-10 h-10 rounded-xl bg-navy-700 border border-navy-600 flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all duration-200"
                                title="Facebook">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13.397 20.997v-8.196h2.765l.411-3.209h-3.176V7.548c0-.926.258-1.56 1.587-1.56h1.684V3.127A22.336 22.336 0 0014.201 3c-2.444 0-4.122 1.492-4.122 4.231v2.355H7.332v3.209h2.753v8.202h3.312z" />
                                </svg>
                            </a>
                        @endif
                        @if($instagram)
                            <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer"
                                class="w-10 h-10 rounded-xl bg-navy-700 border border-navy-600 flex items-center justify-center text-gray-400 hover:bg-gradient-to-br hover:from-purple-600 hover:via-pink-500 hover:to-orange-400 hover:text-white hover:border-pink-500 transition-all duration-200"
                                title="Instagram">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <rect x="2" y="2" width="20" height="20" rx="5" />
                                    <circle cx="12" cy="12" r="5" />
                                    <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none" />
                                </svg>
                            </a>
                        @endif
                        @if($tiktok)
                            <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer"
                                class="w-10 h-10 rounded-xl bg-navy-700 border border-navy-600 flex items-center justify-center text-gray-400 hover:bg-black hover:text-white hover:border-black transition-all duration-200"
                                title="TikTok">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16.6 5.82A4.278 4.278 0 0113.81 3h-3.09v12.4a2.592 2.592 0 01-2.59 2.5c-1.42 0-2.6-1.16-2.6-2.6 0-1.72 1.66-3.01 3.37-2.48V9.66c-3.45-.46-6.47 2.22-6.47 5.64 0 3.33 2.76 5.7 5.69 5.7 3.14 0 5.69-2.55 5.69-5.7V9.01a7.35 7.35 0 004.3 1.38V7.3s-1.88.09-3.51-1.48z" />
                                </svg>
                            </a>
                        @endif
                        @if($zalo)
                            <a href="https://zalo.me/{{ $zalo }}" target="_blank" rel="noopener noreferrer"
                                class="w-10 h-10 rounded-xl bg-navy-700 border border-navy-600 flex items-center justify-center text-gray-400 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-all duration-200"
                                title="Zalo">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 48 48">
                                    <path d="M12.5 6C8.91 6 6 8.91 6 12.5v23C6 39.09 8.91 42 12.5 42h23c3.59 0 6.5-2.91 6.5-6.5v-23C42 8.91 39.09 6 35.5 6h-23zm2.14 11h18.72c.46 0 .83.37.83.83 0 .23-.09.44-.26.59L21.6 29.5h10.9c.55 0 1 .45 1 1s-.45 1-1 1H13.78c-.46 0-.83-.37-.83-.83 0-.23.09-.44.26-.59L25.54 19H14.64c-.55 0-1-.45-1-1s.45-1 1-1z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Navigation -->
            <div class="hidden md:block">
                <h4 class="font-semibold mb-4 text-gray-200">Liên kết</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="{{ route('home') }}" class="hover:text-green-400 transition">Trang chủ</a></li>
                    <li><a href="{{ route('consignments.index') }}" class="hover:text-green-400 transition">Bất động
                            sản</a></li>
                    <li><a href="{{ route('articles.index') }}" class="hover:text-green-400 transition">Tin tức</a></li>
                    <li><a href="{{ env('APP_URL_SANDAT') }}" class="hover:text-green-400 transition">Đăng tin</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="text-center md:text-left">
                <h4 class="font-semibold mb-4 text-gray-200">Liên hệ</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li class="flex items-start gap-2 justify-center md:justify-start">
                        <svg class="w-4 h-4 mt-0.5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ $email }}
                    </li>
                    <li class="flex items-start gap-2 justify-center md:justify-start">
                        <svg class="w-4 h-4 mt-0.5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $phone }}
                    </li>
                    <li class="flex items-start gap-2 justify-center md:justify-start">
                        <svg class="w-4 h-4 mt-0.5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $address }}
                    </li>
                </ul>
            </div>

            <!-- Bộ Công Thương Badge -->
            @if($showBctBadge && $bctImage)
                <div class="text-center md:text-left">
                    <h4 class="font-semibold mb-4 text-gray-200">Chứng nhận</h4>
                    <div class="inline-block bg-white rounded-lg p-2">
                        <img src="{{ $bctImage }}" alt="Đã đăng ký Bộ Công Thương" class="h-20 object-contain">
                    </div>
                </div>
            @endif
        </div>

        <div class="border-t border-navy-600 mt-8 pt-6 text-center text-gray-500 text-sm">
            <div class="flex flex-wrap justify-center gap-4 mb-1">
                <a href="{{ route('privacy-policy') }}" class="hover:text-green-400 transition">Chính sách bảo mật</a>
                <span class="hidden md:inline">|</span>
                <a href="{{ route('terms') }}" class="hover:text-green-400 transition">Điều khoản sử dụng</a>
            </div>
            <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
        </div>
    </div>
</footer>