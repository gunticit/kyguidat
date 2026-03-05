@extends('layouts.app')

@section('title', 'Không tìm thấy trang - ' . ($settings['siteName'] ?? 'SànĐất'))

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center px-4">
        <div class="text-center max-w-md">
            <div class="relative mb-6">
                <span class="text-[120px] md:text-[160px] font-black text-navy-700 leading-none select-none">404</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-20 h-20 text-green-400 opacity-60 animate-bounce" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-100 mb-3">Trang không tồn tại</h1>
            <p class="text-gray-400 mb-6">Trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>

            <p class="text-sm text-gray-500 mb-6">
                Tự động chuyển hướng sau <span id="countdown" class="text-green-400 font-bold">4</span> giây...
            </p>

            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition-all shadow-lg hover:shadow-green-500/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Về trang chủ
            </a>
        </div>
    </div>

    <script>
        (function () {
            let seconds = 4;
            const el = document.getElementById('countdown');
            const timer = setInterval(function () {
                seconds--;
                if (el) el.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.href = '{{ route("home") }}';
                }
            }, 1000);
        })();
    </script>
@endsection