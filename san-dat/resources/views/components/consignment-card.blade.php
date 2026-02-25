<a href="{{ route('consignments.show', data_get($consignment, 'seo_url', data_get($consignment, 'id'))) }}"
    class="block bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl hover:shadow-green-500/10 transition group border border-navy-600">
    <!-- Image -->
    <div class="aspect-video bg-navy-800 relative overflow-hidden">
        @php
            $_imgs = data_get($consignment, 'images', []);
            $images = is_string($_imgs) ? (json_decode($_imgs, true) ?? []) : (is_array($_imgs) ? $_imgs : []);
            $firstImage = null;
            if (!empty($images)) {
                foreach ($images as $img) {
                    if (!empty($img)) {
                        $firstImage = $img;
                        break;
                    }
                }
            }
            if (!$firstImage) {
                $fi = data_get($consignment, 'featured_image', '');
                if (!empty($fi)) {
                    $firstImage = $fi;
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
            <span class="absolute top-2 right-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full font-medium">
                {{ $typeLabel }}
            </span>
        @endif
    </div>

    <!-- Content -->
    <div class="p-4">
        @if(data_get($consignment, 'code'))
            <p class="text-xs text-gray-400 mb-1">Mã: {{ data_get($consignment, 'code') }}</p>
        @endif

        <h3 class="font-semibold text-gray-100 line-clamp-2 mb-2">
            {{ data_get($consignment, 'title', '') }}
        </h3>

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

        <div class="space-y-1 text-sm text-gray-400">
            @if(data_get($consignment, 'address'))
                <p><span class="text-gray-500">Địa chỉ:</span> {{ data_get($consignment, 'address') }}</p>
            @endif
            @if(data_get($consignment, 'area_dimensions'))
                <p><span class="text-gray-500">Diện tích:</span> {{ data_get($consignment, 'area_dimensions') }}</p>
            @endif
            @if(data_get($consignment, 'residential_area'))
                <p><span class="text-gray-500">Thổ cư:</span> {{ data_get($consignment, 'residential_area') }} m²</p>
            @endif
            @php
                $_ld = data_get($consignment, 'land_directions', null);
                $directions = is_string($_ld) ? (json_decode($_ld, true) ?? []) : (is_array($_ld) ? $_ld : []);
            @endphp
            @if(!empty($directions))
                <p><span class="text-gray-500">Hướng:</span> {{ implode(', ', $directions) }}</p>
            @endif
            @if(data_get($consignment, 'road'))
                <p><span class="text-gray-500">Loại đường:</span> {{ data_get($consignment, 'road') }}</p>
            @endif
            @if(data_get($consignment, 'has_house'))
                <p><span class="text-gray-500">Tình trạng:</span>
                    {{ data_get($consignment, 'has_house') === 'yes' ? 'Có nhà' : 'Đất trống' }}</p>
            @endif
        </div>

        <p class="text-green-400 font-bold text-lg mt-2">{{ $formatted }}</p>
    </div>
</a>