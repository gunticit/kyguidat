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
        // Filter out base64 data URIs (keep only real URLs)
        $images = array_values(array_filter($images, fn($img) => !empty($img) && !str_starts_with($img, 'data:')));

        // Fallback to featured_image if no images
        if (empty($images) && !empty($consignment['featured_image'])) {
            $fi = $consignment['featured_image'];
            $images = [str_starts_with($fi, 'data:') ? '' : $fi];
            $images = array_values(array_filter($images));
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
            <a href="{{ route('home') }}" class="text-gray-400 hover:text-green-400 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Trang chủ
            </a>
            <span class="mx-2 text-gray-600">/</span>
            <span class="text-gray-200 font-medium">{{ Str::limit($consignment['title'], 40) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Image Gallery -->
                <div class="mb-6">
                    <!-- Main Image -->
                    <div class="bg-navy-700 rounded-xl overflow-hidden aspect-video mb-3 shadow-lg border border-navy-600">
                        @if(count($images) > 0)
                            <img id="mainImage" src="{{ $images[0] }}" alt="{{ $consignment['title'] }}"
                                class="w-full h-full object-cover transition-all duration-300"
                                onerror="this.onerror=null;this.style.display='none';this.parentElement.innerHTML='<div class=\'w-full h-full flex flex-col items-center justify-center text-gray-500 bg-gradient-to-br from-navy-700 to-navy-800\'><svg class=\'w-20 h-20 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg><span class=\'text-sm\'>Chưa có hình ảnh</span></div>'">
                        @else
                            <div
                                class="w-full h-full flex flex-col items-center justify-center text-gray-500 bg-gradient-to-br from-navy-700 to-navy-800">
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
                                    class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-green-500 transition flex-shrink-0"
                                    onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22%3E%3Crect fill=%22%23334155%22 width=%2280%22 height=%2280%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%2394a3b8%22 font-size=%2210%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Title & Status -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        @if(isset($consignment['category']))
                            <span
                                class="px-3 py-1 bg-green-500/20 text-green-400 text-sm font-medium rounded-full border border-green-500/30">
                                {{ $consignment['category']['name'] ?? $consignment['category'] }}
                            </span>
                        @endif
                        @if($consignment['status'] == 'approved')
                            <span
                                class="px-3 py-1 bg-green-500/20 text-green-400 text-sm font-medium rounded-full border border-green-500/30">
                                ✓ Đã xác minh
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-100 leading-tight">{{ $consignment['title'] }}</h1>

                    <!-- Location -->
                    <div class="flex items-center text-gray-400 mt-3">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>
                            {{ $consignment['address'] ?? '' }}{{ !empty($consignment['address']) && (!empty($consignment['ward']) || !empty($consignment['district']) || !empty($consignment['province'])) ? ', ' : '' }}
                            {{ $consignment['ward'] ?? '' }}{{ !empty($consignment['ward']) && (!empty($consignment['district']) || !empty($consignment['province'])) ? ', ' : '' }}
                            {{ $consignment['district'] ?? '' }}{{ !empty($consignment['district']) && !empty($consignment['province']) ? ', ' : '' }}
                            {{ $consignment['province'] ?? 'Chưa xác định' }}
                        </span>
                    </div>

                    <!-- Quick Info Summary Bar -->
                    @php
                        $areaDimensions = $consignment['area_dimensions'] ?? null;
                        $residentialArea = $consignment['residential_area'] ?? null;
                        $_directions = $consignment['land_directions'] ?? null;
                        $directions = is_string($_directions) ? (json_decode($_directions, true) ?? []) : (is_array($_directions) ? $_directions : []);
                        $dirMap = ['dong' => 'Đông', 'tay' => 'Tây', 'nam' => 'Nam', 'bac' => 'Bắc', 'dong-nam' => 'Đông Nam', 'dong_nam' => 'Đông Nam', 'dong-bac' => 'Đông Bắc', 'dong_bac' => 'Đông Bắc', 'tay-nam' => 'Tây Nam', 'tay_nam' => 'Tây Nam', 'tay-bac' => 'Tây Bắc', 'tay_bac' => 'Tây Bắc'];
                        $directions = array_map(fn($d) => $dirMap[$d] ?? $d, $directions);
                        $directionStr = !empty($directions) ? implode(', ', $directions) : null;
                        $roadType = $consignment['road'] ?? null;
                    @endphp
                    @if($areaDimensions || $residentialArea || $directionStr || $roadType)
                        <div
                            class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-300 bg-navy-700/50 rounded-lg px-4 py-3 border border-navy-600">
                            @if($areaDimensions)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" />
                                    </svg>
                                    <span class="text-gray-500">Diện tích:</span>
                                    <span class="font-medium text-gray-200">{{ $areaDimensions }}</span>
                                </div>
                                <span class="text-navy-500">|</span>
                            @endif
                            @if($residentialArea)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    <span class="text-gray-500">Thổ cư:</span>
                                    <span class="font-medium text-gray-200">{{ $residentialArea }} m²</span>
                                </div>
                                <span class="text-navy-500">|</span>
                            @endif
                            @if($directionStr)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                    </svg>
                                    <span class="text-gray-500">Hướng:</span>
                                    <span class="font-medium text-gray-200">{{ $directionStr }}</span>
                                </div>
                                <span class="text-navy-500">|</span>
                            @endif
                            @if($roadType)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    <span class="text-gray-500">Đường:</span>
                                    <span class="font-medium text-gray-200">{{ $roadType }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Price Highlight -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl p-6 mb-8 text-white shadow-lg">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-green-200 text-sm mb-1">Giá bán</p>
                            @php
                                $price = $consignment['price'] ?? 0;
                                if ($price >= 1000000000) {
                                    $priceFormatted = rtrim(rtrim(number_format($price / 1000000000, 2), '0'), '.') . ' tỷ';
                                } elseif ($price >= 1000000) {
                                    $priceFormatted = rtrim(rtrim(number_format($price / 1000000, 1), '0'), '.') . ' triệu';
                                } else {
                                    $priceFormatted = number_format($price) . ' đ';
                                }
                            @endphp
                            <p class="text-3xl md:text-4xl font-bold">{{ $priceFormatted }}</p>
                        </div>
                        @if($pricePerM2)
                            <div class="mt-4 md:mt-0 md:text-right">
                                <p class="text-green-200 text-sm mb-1">Đơn giá</p>
                                @php
                                    if ($pricePerM2 >= 1000000) {
                                        $perM2Formatted = rtrim(rtrim(number_format($pricePerM2 / 1000000, 1), '0'), '.') . ' triệu/m²';
                                    } else {
                                        $perM2Formatted = number_format($pricePerM2) . ' đ/m²';
                                    }
                                @endphp
                                <p class="text-xl font-semibold">{{ $perM2Formatted }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Property Details Cards -->
                <div class="bg-navy-800 rounded-xl shadow-md p-6 mb-8 border border-navy-600">
                    <h2 class="text-xl font-bold text-gray-100 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Thông tin chi tiết
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <!-- Area -->
                        @if(!empty($consignment['area']))
                            <div class="bg-navy-700 p-4 rounded-xl border border-navy-600">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Diện tích</p>
                                        <p class="font-bold text-gray-100">{{ $consignment['area'] }} m²</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Width -->
                        @if(!empty($consignment['width']))
                            <div class="bg-navy-700 p-4 rounded-xl border border-navy-600">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Mặt tiền</p>
                                        <p class="font-bold text-gray-100">{{ floatval($consignment['width']) }} m</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Length -->
                        @if(!empty($consignment['length']))
                            <div class="bg-navy-700 p-4 rounded-xl border border-navy-600">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400">Chiều dài</p>
                                        <p class="font-bold text-gray-100">{{ floatval($consignment['length']) }} m</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Province -->
                        <div class="bg-navy-700 p-4 rounded-xl border border-navy-600">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Tỉnh/Thành phố</p>
                                    <p class="font-bold text-gray-100">{{ $consignment['province'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- District -->
                        <div class="bg-navy-700 p-4 rounded-xl border border-navy-600">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Quận/Huyện</p>
                                    <p class="font-bold text-gray-100">{{ $consignment['ward'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Code -->
                        <div class="bg-navy-700 p-4 rounded-xl border border-navy-600">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Số thứ tự</p>
                                    <p class="font-bold text-gray-100">
                                        {{ $consignment['order_number'] ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-navy-800 rounded-xl shadow-md p-6 mb-8 border border-navy-600">
                    <h2 class="text-xl font-bold text-gray-100 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Mô tả chi tiết
                    </h2>
                    <div class="description-content text-gray-300 leading-relaxed">
                        {!! $consignment['description'] ?? 'Chưa có mô tả chi tiết cho bất động sản này.' !!}
                    </div>
                    <style>
                        .description-content {
                            font-size: 15px;
                            line-height: 1.8;
                        }

                        .description-content p {
                            margin-bottom: 12px;
                        }

                        .description-content h1,
                        .description-content h2,
                        .description-content h3 {
                            color: var(--gray-100);
                            font-weight: 700;
                            margin: 16px 0 8px;
                        }

                        .description-content h1 {
                            font-size: 1.5em;
                        }

                        .description-content h2 {
                            font-size: 1.3em;
                        }

                        .description-content h3 {
                            font-size: 1.15em;
                        }

                        .description-content ul,
                        .description-content ol {
                            padding-left: 24px;
                            margin-bottom: 12px;
                        }

                        .description-content ul {
                            list-style: disc;
                        }

                        .description-content ol {
                            list-style: decimal;
                        }

                        .description-content li {
                            margin-bottom: 4px;
                        }

                        .description-content a {
                            color: #4ade80;
                            text-decoration: underline;
                        }

                        .description-content strong,
                        .description-content b {
                            color: var(--gray-100);
                            font-weight: 600;
                        }

                        .description-content img {
                            max-width: 100%;
                            height: auto;
                            border-radius: 8px;
                            margin: 12px 0;
                        }

                        .description-content blockquote {
                            border-left: 3px solid #4ade80;
                            padding-left: 16px;
                            margin: 12px 0;
                            color: var(--gray-400);
                            font-style: italic;
                        }

                        .description-content table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 12px 0;
                        }

                        .description-content th,
                        .description-content td {
                            border: 1px solid var(--navy-600);
                            padding: 8px 12px;
                            text-align: left;
                        }

                        .description-content th {
                            background: var(--navy-700);
                            color: var(--gray-100);
                            font-weight: 600;
                        }
                    </style>
                </div>

                <!-- Full Address -->
                <div class="bg-navy-800 rounded-xl shadow-md p-6 mb-8 border border-navy-600">
                    <h2 class="text-xl font-bold text-gray-100 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Địa chỉ
                    </h2>
                    <p class="text-gray-300">
                        {{ $consignment['address'] ?? '' }}
                        {{ !empty($consignment['address']) && !empty($consignment['ward']) ? ', ' : '' }}
                        {{ $consignment['ward'] ?? '' }}
                        {{ !empty($consignment['ward']) && !empty($consignment['district']) ? ', ' : '' }}
                        {{ $consignment['district'] ?? '' }}
                        {{ !empty($consignment['district']) && !empty($consignment['province']) ? ', ' : '' }}
                        {{ $consignment['province'] ?? '' }}
                    </p>

                    <!-- Map -->
                    @if(!empty($consignment['latitude']) && !empty($consignment['longitude']))
                        <div class="mt-4 rounded-lg overflow-hidden border border-navy-600">
                            <iframe width="100%" height="300" style="border:0" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q={{ $consignment['latitude'] }},{{ $consignment['longitude'] }}&z=15&output=embed"
                                allowfullscreen>
                            </iframe>
                        </div>
                    @else
                        <div
                            class="mt-4 bg-navy-700 rounded-lg h-48 flex items-center justify-center text-gray-500 border border-navy-600">
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                <span class="text-sm">Bản đồ vị trí</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Contact Card -->
                <div class="bg-navy-800 shadow-xl rounded-xl p-6 sticky top-24 border border-navy-600">
                    <h3 class="font-bold text-lg mb-5 text-gray-100">Liên hệ tư vấn</h3>

                    @if(isset($consignment['user']))
                        <div class="flex items-center mb-5 p-4 bg-navy-700 rounded-xl border border-navy-600">
                            <div
                                class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-700 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-xl">
                                    {{ strtoupper(substr($consignment['user']['name'] ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <p class="font-bold text-gray-100">{{ $consignment['user']['name'] ?? 'Người đăng' }}</p>
                                <p class="text-sm text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Thành viên xác thực
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <a href="tel:{{ $consignment['user']['phone'] ?? '0123456789' }}"
                            class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition shadow-lg shadow-green-500/25 font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Gọi ngay
                        </a>

                        <a href="https://zalo.me/{{ $consignment['user']['phone'] ?? '0123456789' }}" target="_blank"
                            class="w-full flex items-center justify-center px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition shadow-lg font-semibold">
                            <span class="mr-2 text-lg">💬</span>
                            Chat Zalo
                        </a>

                        <button onclick="copyLink()"
                            class="w-full flex items-center justify-center px-6 py-3 border-2 border-navy-600 text-gray-300 rounded-xl hover:bg-navy-700 transition font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            Chia sẻ
                        </button>
                    </div>

                    <!-- Post Date -->
                    <div class="mt-6 pt-5 border-t border-navy-600">
                        <div class="flex justify-between text-sm text-gray-400">
                            <span>Ngày đăng:</span>
                            <span
                                class="font-medium text-gray-300">{{ isset($consignment['created_at']) ? \Carbon\Carbon::parse($consignment['created_at'])->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        @if(isset($consignment['updated_at']))
                            <div class="flex justify-between text-sm text-gray-400 mt-2">
                                <span>Cập nhật:</span>
                                <span
                                    class="font-medium text-gray-300">{{ \Carbon\Carbon::parse($consignment['updated_at'])->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Properties -->
        @php
            $filteredRelated = [];
            if (isset($relatedConsignments) && is_array($relatedConsignments)) {
                $filteredRelated = array_filter($relatedConsignments, fn($item) => $item['id'] != $consignment['id']);
            }
        @endphp
        @if(count($filteredRelated) > 0)
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-100 mb-6 flex items-center">
                    <svg class="w-7 h-7 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Bất động sản tương tự
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($filteredRelated as $item)
                        @include('components.consignment-card', ['consignment' => $item, 'vertical' => true])
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