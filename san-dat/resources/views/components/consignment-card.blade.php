<a href="{{ route('consignments.show', $consignment['seo_url'] ?? $consignment['id']) }}"
    class="block bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl hover:shadow-green-500/10 transition group border border-navy-600">
    <!-- Image -->
    <div class="aspect-video bg-navy-800 relative overflow-hidden">
        @php
            $images = is_string($consignment['images'] ?? '') ? json_decode($consignment['images'], true) : ($consignment['images'] ?? []);
            // Strip any hardcoded domain from http image URLs (but not data: URIs)
            $images = array_map(fn($img) => str_starts_with($img, 'data:') ? $img : preg_replace('#^https?://[^/]+#', '', $img), $images ?: []);
            $firstImage = $images[0] ?? null;

            // Fallback to featured_image if no images array
            if (!$firstImage && !empty($consignment['featured_image'])) {
                $fi = $consignment['featured_image'];
                $firstImage = str_starts_with($fi, 'data:') ? $fi : preg_replace('#^https?://[^/]+#', '', $fi);
            }
        @endphp

        @if($firstImage)
            <img src="{{ $firstImage }}" alt="{{ $consignment['title'] }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif

        <!-- Status Badge -->
        <span class="absolute top-2 right-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full font-medium">
            Đã duyệt
        </span>
    </div>

    <!-- Content -->
    <div class="p-4">
        <h3 class="font-semibold text-gray-100 line-clamp-2 mb-2">
            {{ $consignment['title'] }}
        </h3>

        <p class="text-green-400 font-bold text-lg mb-2">
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
            {{ $formatted }}
        </p>

        <div class="flex items-center text-sm text-gray-400 space-x-4">
            @if(isset($consignment['area']) && $consignment['area'])
                <span>{{ $consignment['area'] }} m²</span>
            @endif
            <span>{{ $consignment['province'] ?? '' }}</span>
            @if(isset($consignment['distance']) && $consignment['distance'] > 0)
                <span class="inline-flex items-center text-blue-400">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    {{ number_format($consignment['distance'], 1) }} km
                </span>
            @endif
        </div>
    </div>
</a>