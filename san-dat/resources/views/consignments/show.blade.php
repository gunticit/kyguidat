@extends('layouts.app')

@section('title', $consignment['title'] . ' - Sàn Đất')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-indigo-600">Trang chủ</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="{{ route('consignments.index') }}" class="text-gray-500 hover:text-indigo-600">Bất động sản</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900">{{ Str::limit($consignment['title'], 30) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Images -->
                <div class="bg-gray-200 rounded-lg overflow-hidden aspect-video mb-6">
                    @php
                        $images = is_string($consignment['images'] ?? '') ? json_decode($consignment['images'], true) : ($consignment['images'] ?? []);
                    @endphp

                    @if(count($images) > 0)
                        <img src="{{ $images[0] }}" alt="{{ $consignment['title'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Title & Price -->
                <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $consignment['title'] }}</h1>
                <p class="text-3xl font-bold text-indigo-600 mb-6">{{ number_format($consignment['price']) }} VNĐ</p>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    @if(isset($consignment['area']))
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-500">Diện tích</p>
                            <p class="font-semibold">{{ $consignment['area'] }} m²</p>
                        </div>
                    @endif
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-500">Tỉnh/TP</p>
                        <p class="font-semibold">{{ $consignment['province'] ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-500">Quận/Huyện</p>
                        <p class="font-semibold">{{ $consignment['district'] ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-gray-500">Mã tin</p>
                        <p class="font-semibold">{{ $consignment['code'] ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Description -->
                <div class="prose max-w-none">
                    <h2 class="text-xl font-semibold mb-4">Mô tả</h2>
                    <p class="text-gray-700 whitespace-pre-line">{{ $consignment['description'] ?? 'Không có mô tả' }}</p>
                </div>

                <!-- Address -->
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-4">Địa chỉ</h2>
                    <p class="text-gray-700">{{ $consignment['address'] ?? 'Không có địa chỉ' }}</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Contact Card -->
                <div class="bg-white shadow-lg rounded-lg p-6 sticky top-24">
                    <h3 class="font-semibold text-lg mb-4">Liên hệ người đăng</h3>

                    @if(isset($consignment['user']))
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-semibold text-lg">
                                    {{ substr($consignment['user']['name'] ?? 'U', 0, 1) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <p class="font-semibold">{{ $consignment['user']['name'] ?? 'Người đăng' }}</p>
                                <p class="text-sm text-gray-500">Thành viên</p>
                            </div>
                        </div>
                    @endif

                    <a href="tel:0123456789"
                        class="w-full block text-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition mb-3">
                        📞 Gọi ngay
                    </a>

                    <a href="http://localhost:3015"
                        class="w-full block text-center px-6 py-3 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 transition">
                        💬 Nhắn tin
                    </a>
                </div>
            </div>
        </div>

        <!-- Related -->
        @if(count($relatedConsignments) > 0)
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Bất động sản tương tự</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedConsignments as $item)
                        @if($item['id'] != $consignment['id'])
                            @include('components.consignment-card', ['consignment' => $item])
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection