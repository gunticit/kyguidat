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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div>
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-12 w-auto object-contain mb-3">
                @endif
                <h3 class="text-xl font-bold text-green-400">{{ $siteName }}</h3>
                <p class="mt-4 text-gray-400">Nền tảng ký gửi bất động sản uy tín hàng đầu Việt Nam</p>
            </div>

            <!-- Links -->
            <div>
                <h4 class="font-semibold mb-4 text-gray-200">Liên kết</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('home') }}" class="hover:text-green-400 transition">Trang chủ</a></li>
                    <li><a href="{{ route('consignments.index') }}" class="hover:text-green-400 transition">Bất động
                            sản</a></li>
                    <li><a href="#" class="hover:text-green-400 transition">Tin tức</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="font-semibold mb-4 text-gray-200">Liên hệ</h4>
                <ul class="space-y-2 text-gray-400">
                    <li>📧 {{ $email }}</li>
                    <li>📞 {{ $phone }}</li>
                    <li>📍 {{ $address }}</li>
                </ul>
            </div>

            <!-- User Links -->
            <div>
                <h4 class="font-semibold mb-4 text-gray-200">Người dùng</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ env('APP_URL_SANDAT') }}" class="hover:text-green-400 transition">Đăng ký</a></li>
                    <li><a href="{{ env('APP_URL_SANDAT') }}" class="hover:text-green-400 transition">Đăng nhập</a></li>
                    <li><a href="{{ env('APP_URL_SANDAT') }}" class="hover:text-green-400 transition">Đăng tin</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-navy-600 mt-8 pt-8 text-center text-gray-500">
            <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
        </div>
    </div>
</footer>