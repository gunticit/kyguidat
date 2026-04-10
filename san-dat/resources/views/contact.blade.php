@extends('layouts.app')

@section('title', 'Liên hệ - ' . ($settings['siteName'] ?? 'SànĐất'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-100 mb-2">Liên hệ với chúng tôi</h1>
            <p class="text-gray-400 max-w-2xl mx-auto">Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua các
                kênh bên dưới</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-full mx-auto">
            <!-- Contact Info -->
            <div class="space-y-6">
                <div class="bg-navy-700 rounded-lg shadow-md p-6 border border-navy-600">
                    <h2 class="text-xl font-bold text-gray-100 mb-6">Thông tin liên hệ</h2>

                    <div class="space-y-5">
                        @if(!empty($settings['email']))
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Email</p>
                                    <a href="mailto:{{ $settings['email'] }}"
                                        class="text-gray-200 hover:text-green-400 transition">{{ $settings['email'] }}</a>
                                </div>
                            </div>
                        @endif

                        @if(!empty($settings['phone']))
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Điện thoại</p>
                                    <a href="tel:{{ $settings['phone'] }}"
                                        class="text-gray-200 hover:text-green-400 transition">{{ $settings['phone'] }}</a>
                                </div>
                            </div>
                        @endif

                        @if(!empty($settings['address']))
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Địa chỉ</p>
                                    <p class="text-gray-200">{{ $settings['address'] }}</p>
                                </div>
                            </div>
                        @endif

                        @if(!empty($settings['zalo']))
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="text-blue-400 font-bold text-sm">Zalo</span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400 mb-1">Zalo</p>
                                    <p class="text-gray-200">{{ $settings['zalo'] }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Social Links -->
                @if(!empty($settings['facebook']))
                    <div class="bg-navy-700 rounded-lg shadow-md p-6 border border-navy-600">
                        <h3 class="text-lg font-bold text-gray-100 mb-4">Mạng xã hội</h3>
                        <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                            </svg>
                            Facebook
                        </a>
                    </div>
                @endif

                <!-- Company Legal Info -->
                <div class="bg-navy-700 rounded-lg shadow-md p-6 border border-navy-600">
                    <h3 class="text-lg font-bold text-gray-100 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Thông tin công ty
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Tên công ty</p>
                            <p class="text-gray-200 font-semibold">CÔNG TY TNHH KHO ĐẤT</p>
                            <p class="text-gray-400 text-xs">KHO DAT COMPANY LIMITED</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Mã số doanh nghiệp</p>
                            <p class="text-green-400 font-bold text-base tracking-wide">0319338160</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Đăng ký lần đầu</p>
                            <p class="text-gray-200">06/01/2026</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Loại hình</p>
                            <p class="text-gray-200">Công ty Trách nhiệm Hữu hạn Một Thành Viên</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-navy-700 rounded-lg shadow-md p-6 border border-navy-600">
                <h2 class="text-xl font-bold text-gray-100 mb-6">Gửi tin nhắn</h2>

                <form id="contactForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Họ tên *</label>
                        <input type="text" name="name" required
                            class="w-full px-4 py-2 bg-navy-800 border border-navy-600 rounded-lg text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="Nhập họ tên">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Email *</label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-2 bg-navy-800 border border-navy-600 rounded-lg text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="Nhập email">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Số điện thoại</label>
                        <input type="tel" name="phone"
                            class="w-full px-4 py-2 bg-navy-800 border border-navy-600 rounded-lg text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="Nhập số điện thoại">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nội dung *</label>
                        <textarea name="message" rows="5" required
                            class="w-full px-4 py-2 bg-navy-800 border border-navy-600 rounded-lg text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                            placeholder="Nhập nội dung tin nhắn"></textarea>
                    </div>
                    <div id="contactMessage" class="hidden text-sm p-3 rounded-lg"></div>
                    <button type="submit"
                        class="w-full px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold shadow-lg shadow-green-500/25">
                        Gửi tin nhắn
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const msg = document.getElementById('contactMessage');
            const form = this;

            // Simple mailto fallback since we don't have a backend endpoint for contact form
            const email = '{{ $settings["email"] ?? "" }}';
            const name = form.name.value;
            const userEmail = form.email.value;
            const phone = form.phone.value;
            const message = form.message.value;

            const subject = encodeURIComponent('Liên hệ từ ' + name);
            const body = encodeURIComponent('Họ tên: ' + name + '\nEmail: ' + userEmail + '\nSĐT: ' + phone + '\n\nNội dung:\n' + message);

            window.location.href = 'mailto:' + email + '?subject=' + subject + '&body=' + body;

            msg.textContent = 'Đang mở ứng dụng email...';
            msg.className = 'text-sm p-3 rounded-lg bg-green-500/20 text-green-400';
        });
    </script>
@endsection