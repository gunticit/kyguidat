@php $isVertical = !empty($vertical); @endphp
<a href="{{ route('consignments.show', !empty(data_get($consignment, 'seo_url')) ? data_get($consignment, 'seo_url') : data_get($consignment, 'id')) }}"
    class="flex {{ $isVertical ? 'flex-col' : 'flex-col md:flex-row' }} bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition group border border-navy-600">
    <!-- Image -->
    <div
        class="w-full {{ $isVertical ? 'aspect-video' : 'md:w-48 flex-shrink-0 aspect-video md:aspect-auto' }} bg-navy-800 relative overflow-hidden">
        @php
            $firstImage = null;
            $fi = data_get($consignment, 'featured_image', '');
            if (!empty($fi)) {
                $firstImage = $fi;
            }
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
                onerror="this.onerror=null;this.style.display='none';this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-400 text-sm bg-navy-800\'>No Image</div>'">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">No Image</div>
        @endif

        @php
            // Calculate distance if user location is available
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
        @if($distance !== null)
            <span class="absolute top-2 left-2 px-2 py-0.5 bg-blue-600/90 text-white text-xs rounded-full font-medium">
                📍 {{ $distance < 1 ? number_format($distance * 1000, 0) . ' m' : number_format($distance, 1) . ' km' }}
            </span>
        @endif
    </div>

    <!-- Content (right side) -->
    <div class="flex-1 p-3 md:p-4 flex flex-col justify-between min-w-0">
        <div>
            @if(data_get($consignment, 'order_number'))
                <p class="text-xs text-gray-500 mb-1 font-medium">Mã Số: {{ data_get($consignment, 'order_number') }}</p>
            @endif

            <h3 class="font-bold uppercase text-sm md:text-base mb-2 line-clamp-2">
                {{ data_get($consignment, 'title', '') }}
            </h3>

            @php
                $price = data_get($consignment, 'price', 0);
                if ($price >= 1000000000) {
                    $billions = floor($price / 1000000000);
                    $millions = round(($price % 1000000000) / 1000000);
                    $formatted = $billions . ' tỷ' . ($millions > 0 ? ' ' . $millions . ' triệu' : '');
                } elseif ($price >= 1000000) {
                    $formatted = rtrim(rtrim(number_format($price / 1000000, 1), '0'), '.') . ' triệu';
                } else {
                    $formatted = number_format($price) . ' đ';
                }
            @endphp

            <div class="text-xs md:text-sm text-gray-600">
                @if(data_get($consignment, 'address'))
                    <p class="mb-1"><span class="text-gray-400">Địa chỉ:</span> {{ data_get($consignment, 'address') }}</p>
                @endif
                <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                    @if(data_get($consignment, 'area_dimensions'))
                        <p><span class="text-gray-400">Diện tích:</span> {{ data_get($consignment, 'area_dimensions') }}</p>
                    @endif
                    @if(data_get($consignment, 'residential_area'))
                        <p><span class="text-gray-400">Thổ cư:</span>
                            {{ rtrim(rtrim(number_format(data_get($consignment, 'residential_area'), 2), '0'), '.') }} m²
                        </p>
                    @endif
                    @php
                        $_ld = data_get($consignment, 'land_directions', null);
                        $directions = is_string($_ld) ? (json_decode($_ld, true) ?? []) : (is_array($_ld) ? $_ld : []);
                        $dirMap = ['dong' => 'Đông', 'tay' => 'Tây', 'nam' => 'Nam', 'bac' => 'Bắc', 'dong-nam' => 'Đông Nam', 'dong_nam' => 'Đông Nam', 'dong-bac' => 'Đông Bắc', 'dong_bac' => 'Đông Bắc', 'tay-nam' => 'Tây Nam', 'tay_nam' => 'Tây Nam', 'tay-bac' => 'Tây Bắc', 'tay_bac' => 'Tây Bắc'];
                        $directions = array_map(fn($d) => $dirMap[$d] ?? $d, $directions);
                    @endphp
                    @if(!empty($directions))
                        <p><span class="text-gray-400">Hướng:</span> {{ implode(', ', $directions) }}</p>
                    @endif
                    @if(data_get($consignment, 'road'))
                        <p><span class="text-gray-400">Loại đường:</span> {{ data_get($consignment, 'road') }}</p>
                    @endif
                    @if(data_get($consignment, 'frontage_actual') && data_get($consignment, 'frontage_actual') != '0' && data_get($consignment, 'frontage_actual') != '0.00')
                        <p>
                            <span class="text-gray-400">Mặt tiền:</span>
                            {{ floatval(data_get($consignment, 'frontage_actual')) }} m
                        </p>
                    @endif
                    @php
                        $createdAt = data_get($consignment, 'created_at');
                        $statusText = 'Chưa bán';
                        if ($createdAt) {
                            $createdDate = \Carbon\Carbon::parse($createdAt);
                            if ($createdDate->diffInDays(now('Asia/Ho_Chi_Minh')) < 5) {
                                $statusText = $createdDate->locale('vi')->diffForHumans();
                            }
                        }
                    @endphp
                    <p><span class="text-gray-400">Tình trạng:</span> {{ $statusText }}</p>
                    <p><span class="text-orange-500 font-bold">Giá: {{ $formatted }}</span></p>
                </div>
            </div>
        </div>
    </div>
</a>