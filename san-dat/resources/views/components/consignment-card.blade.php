<a href="{{ route('consignments.show', $consignment['seo_url'] ?? $consignment['id']) }}"
    class="block bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl hover:shadow-green-500/10 transition group border border-navy-600">
    <!-- Image -->
    <div class="aspect-video bg-navy-800 relative overflow-hidden">
        @php
            $images = is_string($consignment['images'] ?? '') ? json_decode($consignment['images'], true) : ($consignment['images'] ?? []);
            $firstImage = null;
            if (!empty($images)) {
                foreach ($images as $img) {
                    if (!empty($img) && !str_starts_with($img, 'data:')) {
                        $firstImage = $img;
                        break;
                    }
                }
            }
            if (!$firstImage && !empty($consignment['featured_image'])) {
                $fi = $consignment['featured_image'];
                $firstImage = str_starts_with($fi, 'data:') ? null : $fi;
            }
        @endphp

        @if($firstImage)
            <img src="{{ $firstImage }}" alt="{{ $consignment['title'] }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-500 text-sm">No Image</div>
        @endif

        @php
            $landTypes = is_string($consignment['land_types'] ?? '') ? json_decode($consignment['land_types'], true) : ($consignment['land_types'] ?? []);
            $typeLabel = !empty($landTypes) ? $landTypes[0] : ($consignment['type'] ?? '');
        @endphp
        @if($typeLabel)
            <span class="absolute top-2 right-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full font-medium">
                {{ $typeLabel }}
            </span>
        @endif
    </div>

    <!-- Content -->
    <div class="p-4">
        @if(!empty($consignment['code']))
            <p class="text-xs text-gray-400 mb-1">Mã: {{ $consignment['code'] }}</p>
        @endif

        <h3 class="font-semibold text-gray-100 line-clamp-2 mb-2">
            {{ $consignment['title'] }}
        </h3>

        @php
            $price = $consignment['price'] ?? 0;
            if ($price >= 1000000000) {
                $formatted = rtrim(rtrim(number_format($price / 1000000000, 2), '0'), '.') . ' tỷ';
            } elseif ($price >= 1000000) {
                $formatted = rtrim(rtrim(number_format($price / 1000000, 1), '0'), '.') . ' triệu';
            } else {
                $formatted = number_format($price) . ' đ';
            }
        @endphp

        <div class="space-y-1 text-sm text-gray-400">
            @if(!empty($consignment['address']))
                <p><span class="text-gray-500">Địa chỉ:</span> {{ $consignment['address'] }}</p>
            @endif
            @if(!empty($consignment['area_dimensions']))
                <p><span class="text-gray-500">Diện tích:</span> {{ $consignment['area_dimensions'] }}</p>
            @endif
            @if(!empty($consignment['residential_area']))
                <p><span class="text-gray-500">Thổ cư:</span> {{ $consignment['residential_area'] }} m²</p>
            @endif
            @php
                $directions = is_string($consignment['land_directions'] ?? '') ? json_decode($consignment['land_directions'], true) : ($consignment['land_directions'] ?? []);
            @endphp
            @if(!empty($directions))
                <p><span class="text-gray-500">Hướng:</span> {{ implode(', ', $directions) }}</p>
            @endif
            @if(!empty($consignment['road']))
                <p><span class="text-gray-500">Loại đường:</span> {{ $consignment['road'] }}</p>
            @endif
            @if(!empty($consignment['has_house']))
                <p><span class="text-gray-500">Tình trạng:</span>
                    {{ $consignment['has_house'] === 'yes' ? 'Có nhà' : 'Đất trống' }}</p>
            @endif
        </div>

        <p class="text-green-400 font-bold text-lg mt-2">{{ $formatted }}</p>
    </div>
</a>