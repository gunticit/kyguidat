<a href="{{ route('consignments.show', data_get($consignment, 'seo_url', data_get($consignment, 'id'))) }}"
    class="flex bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl hover:shadow-green-500/10 transition group border border-navy-600">
    <!-- Image -->
    <div class="w-48 md:w-64 h-40 flex-shrink-0 bg-navy-800 relative overflow-hidden">
        @php
            $firstImage = null;
            // Priority 1: featured_image (hình đại diện)
            $fi = data_get($consignment, 'featured_image', '');
            if (!empty($fi)) {
                $firstImage = $fi;
            }
            // Priority 2: first image from images array
            if (!$firstImage) {
                $_imgs = data_get($consignment, 'images', []);
                $images = is_string($_imgs) ? (json_decode($_imgs, true) ?? []) : (is_array($_imgs) ? $_imgs : []);
                foreach ($images as $img) {
                    if (!empty($img)) {
                        $firstImage = $img;
                        break;
                    }
                }
            }
        @endphp

        @if($firstImage)
            <img src="{{ $firstImage }}" alt="{{ data_get($consignment, 'title', '') }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy"
                onerror="this.onerror=null;this.style.display='none';this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-500 text-sm\' style=\'background:#1e293b\'>No Image</div>'">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-500 text-sm">No Image</div>
        @endif

        @php
            $_lt = data_get($consignment, 'land_types', null);
            $landTypes = is_string($_lt) ? (json_decode($_lt, true) ?? []) : (is_array($_lt) ? $_lt : []);
            $typeLabel = !empty($landTypes) ? $landTypes[0] : data_get($consignment, 'type', '');
        @endphp
        @if($typeLabel)
            <span class="absolute top-2 left-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full font-medium">
                {{ $typeLabel }}
            </span>
        @endif
    </div>

    <!-- Content -->
    <div class="flex-1 p-4 flex flex-col justify-between">
        <div>
            @php
                $distance = null;
                $cLat = data_get($consignment, 'lat') ?: data_get($consignment, 'latitude');
                $cLng = data_get($consignment, 'lng') ?: data_get($consignment, 'longitude');
                if (!empty($userLat) && !empty($userLng) && !empty($cLat) && !empty($cLng)) {
                    $R = 6371;
                    $dLat = deg2rad((float) $cLat - (float) $userLat);
                    $dLng = deg2rad((float) $cLng - (float) $userLng);
                    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad((float) $userLat)) * cos(deg2rad((float) $cLat)) * sin($dLng / 2) * sin($dLng / 2);
                    $distance = $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
                }
            @endphp

            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    @if(data_get($consignment, 'order_number'))
                        <p class="text-xs text-gray-400 mb-1">STT: {{ data_get($consignment, 'order_number') }}</p>
                    @endif
                </div>
                @if($distance !== null)
                    <span
                        class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs rounded-full font-medium"
                        title="Khoảng cách từ vị trí của bạn">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        {{ $distance < 1 ? number_format($distance * 1000, 0) . ' m' : number_format($distance, 1) . ' km' }}
                    </span>
                @endif
            </div>

            <h3 class="font-semibold text-gray-100 text-lg mb-2">
                {{ data_get($consignment, 'title', '') }}
            </h3>

            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-400 mb-2">
                @if(data_get($consignment, 'address'))
                    <p><span class="text-gray-500">Địa chỉ:</span> {{ data_get($consignment, 'address') }}</p>
                @endif
                @if(data_get($consignment, 'area_dimensions'))
                    <span><span class="text-gray-500">Diện tích:</span>
                        {{ data_get($consignment, 'area_dimensions') }}</span>
                @endif
                @if(data_get($consignment, 'residential_area'))
                    <span><span class="text-gray-500">Thổ cư:</span> {{ data_get($consignment, 'residential_area') }}
                        m²</span>
                @endif
                @php
                    $_ld = data_get($consignment, 'land_directions', null);
                    $directions = is_string($_ld) ? (json_decode($_ld, true) ?? []) : (is_array($_ld) ? $_ld : []);
                @endphp
                @if(!empty($directions))
                    <span><span class="text-gray-500">Hướng:</span> {{ implode(', ', $directions) }}</span>
                @endif
                @if(data_get($consignment, 'road'))
                    <span><span class="text-gray-500">Đường:</span> {{ data_get($consignment, 'road') }}</span>
                @endif
            </div>
        </div>

        @php
            $price = data_get($consignment, 'price', 0);
            if ($price >= 1000000000) {
                $formatted = rtrim(rtrim(number_format($price / 1000000000, 2), '0'), '.') . ' tỷ';
            } elseif ($price >= 1000000) {
                $formatted = rtrim(rtrim(number_format($price / 1000000, 1), '0'), '.') . ' triệu';
            } else {
                $formatted = number_format($price) . ' đ';
            }
        @endphp
        <p class="text-green-400 font-bold text-lg">{{ $formatted }}</p>
    </div>
</a>