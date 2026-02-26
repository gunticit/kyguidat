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
@endphp
<footer class="bg-navy-900 border-t border-navy-600 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Brand -->
            <div class="text-center md:text-left">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-12 w-auto object-contain mb-3 mx-auto md:mx-0">
                @endif
                <h3 class="text-xl font-bold text-green-400">{{ $siteName }}</h3>
                <p class="mt-3 text-gray-400 text-sm leading-relaxed">Nền tảng ký gửi bất động sản uy tín hàng đầu Việt
                    Nam</p>
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
        </div>

        <div class="border-t border-navy-600 mt-10 pt-6 text-center text-gray-500 text-sm">
            <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
        </div>
    </div>
</footer>