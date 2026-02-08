<footer class="bg-gray-900 text-white mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div>
                <h3 class="text-2xl font-bold text-indigo-400">SànĐất</h3>
                <p class="mt-4 text-gray-400">Nền tảng ký gửi bất động sản uy tín hàng đầu Việt Nam</p>
            </div>

            <!-- Links -->
            <div>
                <h4 class="font-semibold mb-4">Liên kết</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Trang chủ</a></li>
                    <li><a href="{{ route('consignments.index') }}" class="hover:text-white">Bất động sản</a></li>
                    <li><a href="#" class="hover:text-white">Tin tức</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="font-semibold mb-4">Liên hệ</h4>
                <ul class="space-y-2 text-gray-400">
                    <li>📧 contact@sandat.vn</li>
                    <li>📞 0123 456 789</li>
                    <li>📍 TP. Hồ Chí Minh</li>
                </ul>
            </div>

            <!-- User Links -->
            <div>
                <h4 class="font-semibold mb-4">Người dùng</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="http://localhost:3015" class="hover:text-white">Đăng ký</a></li>
                    <li><a href="http://localhost:3015" class="hover:text-white">Đăng nhập</a></li>
                    <li><a href="http://localhost:3015" class="hover:text-white">Đăng tin</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; {{ date('Y') }} SànĐất. All rights reserved.</p>
        </div>
    </div>
</footer>