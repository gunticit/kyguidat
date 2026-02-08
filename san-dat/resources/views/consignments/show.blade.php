@extends('layouts.app')

@section('title', $consignment['title'] . ' - Sàn Đất')

@section('content')
    @php
        // Parse images safely
        $images = [];
        if (!empty($consignment['images'])) {
            if (is_string($consignment['images'])) {
                $decoded = json_decode($consignment['images'], true);
                $images = is_array($decoded) ? $decoded : [];
            } elseif (is_array($consignment['images'])) {
                $images = $consignment['images'];
            }
        }

        // Calculate price per m2
        $pricePerM2 = null;
        if (!empty($consignment['area']) && !empty($consignment['price']) && $consignment['area'] > 0) {
            $pricePerM2 = $consignment['price'] / $consignment['area'];
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm flex items-center">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-indigo-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Trang chủ
            </a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="{{ route('consignments.index') }}" class="text-gray-500 hover:text-indigo-600">Bất động sản</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900 font-medium">{{ Str::limit($consignment['title'], 40) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Image Gallery -->
                <div class="mb-6">
                    <!-- Main Image -->
                    <div class="bg-gray-100 rounded-xl overflow-hidden aspect-video mb-3 shadow-lg">
                        @if(count($images) > 0)
                            <img id="mainImage" src="{{ $images[0] }}" alt="{{ $consignment['title'] }}" 
                                class="w-full h-full object-cover transition-all duration-300">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gradient-to-br from-gray-100 to-gray-200">
                                <svg class="w-20 h-20 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm">Chưa có hình ảnh</span>
                            </div>
                        @endif
                    </div>

                    <!-- Thumbnail Gallery -->
                    @if(count($images) > 1)
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            @foreach($images as $index => $img)
                                <img src="{{ $img }}" alt="Ảnh {{ $index + 1 }}" 
                                    onclick="document.getElementById('mainImage').src='{{ $img }}'"
                                    class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-indigo-500 transition flex-shrink-0">
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Title & Status -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        @if(isset($consignment['category']))
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-sm font-medium rounded-full">
                                {{ $consignment['category']['name'] ?? $consignment['category'] }}
                            </span>
                        @endif
                        @if($consignment['status'] == 'approved')
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">
                                ✓ Đã xác minh
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight">{{ $consignment['title'] }}</h1>

                    <!-- Location -->
                    <div class="flex items-center text-gray-600 mt-3">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>
                            {{ $consignment['ward'] ?? '' }}{{ !empty($consignment['ward']) ? ', ' : '' }}
                            {{ $consignment['district'] ?? '' }}{{ !empty($consignment['district']) ? ', ' : '' }}
                            {{ $consignment['province'] ?? 'Chưa xác định' }}
                        </span>
                    </div>
                </div>

                <!-- Price Highlight -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 mb-8 text-white shadow-lg">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-indigo-200 text-sm mb-1">Giá bán</p>
                            <p class="text-3xl md:text-4xl font-bold">{{ number_format($consignment['price']) }} VNĐ</p>
                        </div>
                        @if($pricePerM2)
                            <div class="mt-4 md:mt-0 md:text-right">
                                <p class="text-indigo-200 text-sm mb-1">Đơn giá</p>
                                <p class="text-xl font-semibold">{{ number_format($pricePerM2) }} VNĐ/m²</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Property Details Cards -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Thông tin chi tiết
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <!-- Area -->
                        @if(!empty($consignment['area']))
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Diện tích</p>
                                        <p class="font-bold text-gray-900">{{ $consignment['area'] }} m²</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Width -->
                        @if(!empty($consignment['width']))
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Mặt tiền</p>
                                        <p class="font-bold text-gray-900">{{ $consignment['width'] }} m</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Length -->
                        @if(!empty($consignment['length']))
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Chiều dài</p>
                                        <p class="font-bold text-gray-900">{{ $consignment['length'] }} m</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Province -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Tỉnh/Thành phố</p>
                                    <p class="font-bold text-gray-900">{{ $consignment['province'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- District -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Quận/Huyện</p>
                                    <p class="font-bold text-gray-900">{{ $consignment['district'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Code -->
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Mã tin</p>
                                    <p class="font-bold text-gray-900">{{ $consignment['code'] ?? 'BDS-' . $consignment['id'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        Mô tả chi tiết
                    </h2>
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        {!! nl2br(e($consignment['description'] ?? 'Chưa có mô tả chi tiết cho bất động sản này.')) !!}
                    </div>
                </div>

                <!-- Full Address -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Địa chỉ
                    </h2>
                    <p class="text-gray-700">
                        {{ $consignment['address'] ?? '' }}
                        {{ !empty($consignment['address']) && !empty($consignment['ward']) ? ', ' : '' }}
                        {{ $consignment['ward'] ?? '' }}
                        {{ !empty($consignment['ward']) && !empty($consignment['district']) ? ', ' : '' }}
                        {{ $consignment['district'] ?? '' }}
                        {{ !empty($consignment['district']) && !empty($consignment['province']) ? ', ' : '' }}
                        {{ $consignment['province'] ?? '' }}
                    </p>

                    <!-- Map Placeholder -->
                    <div class="mt-4 bg-gray-100 rounded-lg h-48 flex items-center justify-center text-gray-400">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            <span class="text-sm">Bản đồ vị trí</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Contact Card -->
                <div class="bg-white shadow-xl rounded-xl p-6 sticky top-24 border border-gray-100">
                    <h3 class="font-bold text-lg mb-5 text-gray-900">Liên hệ tư vấn</h3>

                    @if(isset($consignment['user']))
                        <div class="flex items-center mb-5 p-4 bg-gray-50 rounded-xl">
                            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-xl">
                                    {{ strtoupper(substr($consignment['user']['name'] ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <p class="font-bold text-gray-900">{{ $consignment['user']['name'] ?? 'Người đăng' }}</p>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Thành viên xác thực
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <a href="tel:{{ $consignment['user']['phone'] ?? '0123456789' }}"
                            class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition shadow-lg font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Gọi ngay
                        </a>

                        <a href="https://zalo.me/{{ $consignment['user']['phone'] ?? '0123456789' }}" target="_blank"
                            class="w-full flex items-center justify-center px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition shadow-lg font-semibold">
                            <span class="mr-2 text-lg">💬</span>
                            Chat Zalo
                        </a>

                        <button onclick="copyLink()" 
                            class="w-full flex items-center justify-center px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            Chia sẻ
                        </button>
                    </div>

                    <!-- Post Date -->
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Ngày đăng:</span>
                            <span class="font-medium">{{ isset($consignment['created_at']) ? \Carbon\Carbon::parse($consignment['created_at'])->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        @if(isset($consignment['updated_at']))
                            <div class="flex justify-between text-sm text-gray-500 mt-2">
                                <span>Cập nhật:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($consignment['updated_at'])->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Properties -->
        @if(isset($relatedConsignments) && is_array($relatedConsignments) && count($relatedConsignments) > 0)
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-7 h-7 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Bất động sản tương tự
                </h2>
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

    <script>
        function copyLink() {
            navigator.clipboard.writeText(window.location.href);
            alert('Đã sao chép link!');
        }
    </script>
@endsection