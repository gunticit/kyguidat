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
                <p class="mt-3 text-gray-400 text-sm leading-relaxed">Nền tảng ký gửi bất động sản uy tín hàng đầu Việt
                    Nam</p>

                <!-- Social Media Icons -->
                @if($facebook || $tiktok || $youtube || $zalo)
                    <div class="flex items-center gap-3 mt-4 justify-center md:justify-start">
                        @if($facebook)
                            <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer"
                                class="w-9 h-9 rounded-full bg-navy-700 flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition-all duration-200"
                                title="Facebook">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                                </svg>
                            </a>
                        @endif
                        @if($tiktok)
                            <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer"
                                class="w-9 h-9 rounded-full bg-navy-700 flex items-center justify-center text-gray-400 hover:bg-black hover:text-white transition-all duration-200"
                                title="TikTok">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V9.27a8.16 8.16 0 004.76 1.52v-3.4a4.85 4.85 0 01-1-.7z" />
                                </svg>
                            </a>
                        @endif
                        @if($youtube)
                            <a href="{{ $youtube }}" target="_blank" rel="noopener noreferrer"
                                class="w-9 h-9 rounded-full bg-navy-700 flex items-center justify-center text-gray-400 hover:bg-red-600 hover:text-white transition-all duration-200"
                                title="YouTube">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                                </svg>
                            </a>
                        @endif
                        @if($zalo)
                            <a href="https://zalo.me/{{ $zalo }}" target="_blank" rel="noopener noreferrer"
                                class="w-9 h-9 rounded-full bg-navy-700 flex items-center justify-center text-gray-400 hover:bg-blue-500 hover:text-white transition-all duration-200"
                                title="Zalo">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 8.16c-.169-.044-.335-.088-.493-.122a12.5 12.5 0 00-.493-.097c-.147-.025-.285-.05-.413-.069a5.4 5.4 0 00-.656-.053c-.175 0-.35.009-.516.028a3.73 3.73 0 00-.475.078 2.88 2.88 0 00-.425.128 2.17 2.17 0 00-.372.178 1.94 1.94 0 00-.306.219 1.7 1.7 0 00-.247.259 1.72 1.72 0 00-.178.284 2.21 2.21 0 00-.131.325c-.038.113-.066.228-.088.35a3.3 3.3 0 00-.044.369c-.006.125-.009.253-.009.384v5.416h-1.6V9.34H8.528l-.003.003c-.1.128-.269.356-.403.553-.134.197-.237.366-.31.506s-.119.266-.159.378a3.6 3.6 0 00-.097.313c-.022.094-.038.175-.047.241a1.1 1.1 0 00-.013.122v.006h3.075V13.1H7.222c.025.175.053.35.091.519.038.169.084.331.137.484.053.153.113.3.184.437.072.138.15.266.241.381.09.116.191.222.3.316.109.094.228.175.356.244.128.069.263.125.406.166.144.041.297.069.456.081.159.013.328.006.503-.019v1.556c-.175.025-.35.038-.519.038-.169 0-.331-.013-.488-.038a3.3 3.3 0 01-.45-.109 3.1 3.1 0 01-.419-.175 2.8 2.8 0 01-.381-.241 2.6 2.6 0 01-.338-.3 2.97 2.97 0 01-.291-.356 3.3 3.3 0 01-.237-.406 3.87 3.87 0 01-.184-.456 4.7 4.7 0 01-.131-.497 5.9 5.9 0 01-.078-.525 6.9 6.9 0 01-.028-.544v-.003h-.003v-1.856h1.6v-.003c.003-.169.022-.338.056-.503.034-.166.081-.328.141-.481.059-.153.131-.3.216-.437.084-.138.178-.266.281-.381h-2.3V8.16h5.856v.003c.003.003.003.003.003.003h.003v5.416c0-.131.003-.259.009-.384a3.3 3.3 0 01.044-.369c.022-.122.05-.238.088-.35a2.21 2.21 0 01.131-.325c.05-.097.109-.194.178-.284a1.7 1.7 0 01.247-.259c.094-.084.197-.156.306-.219a2.17 2.17 0 01.372-.178c.131-.05.275-.094.425-.128a3.73 3.73 0 01.475-.078c.166-.019.341-.028.516-.028.225 0 .444.019.656.053.128.019.266.044.413.069.156.028.313.059.493.097.159.034.325.078.493.122v1.638z" />
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