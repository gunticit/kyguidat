@extends('layouts.app')

@section('title', 'Sàn Đất - Ký gửi Bất động sản')
@section('description', 'Tìm kiếm và ký gửi bất động sản uy tín')

@section('content')
    <!-- Advanced Search Section -->
    <section class="bg-white py-6 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Search Bar -->
            <form action="{{ route('search.results') }}" method="GET" id="searchForm">
                <div class="flex flex-col md:flex-row gap-3 mb-4">
                    <div class="flex-1">
                        <input type="text" name="q" placeholder="Tìm theo mã số hoặc số điện thoại"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-green-500 focus:outline-none text-gray-700">
                    </div>
                    <button type="submit"
                        class="px-8 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition flex items-center justify-center gap-2">
                        Tìm Kiếm
                    </button>
                    <button type="button" id="toggleFilters"
                        class="px-8 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Bộ lọc chi tiết
                        <svg class="w-4 h-4 transition-transform" id="filterArrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div id="advancedFilters" class="hidden border-t border-gray-200 pt-4">
                    <!-- Row 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh - Thành phố</label>
                            <select name="province"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="ho-chi-minh">Hồ Chí Minh</option>
                                <option value="ba-ria-vung-tau">Bà Rịa - Vũng Tàu</option>
                                <option value="binh-duong">Bình Dương</option>
                                <option value="dong-nai">Đồng Nai</option>
                                <option value="long-an">Long An</option>
                                <option value="binh-phuoc">Bình Phước</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Xã / Phường / Đặc khu</label>
                            <select name="district"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loại bất động sản</label>
                            <select name="property_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="dat-nen">Đất nền</option>
                                <option value="dat-nong-nghiep">Đất nông nghiệp</option>
                                <option value="dat-vuon">Đất vườn</option>
                                <option value="nha-pho">Nhà phố</option>
                                <option value="biet-thu">Biệt thự</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nhà trên đất</label>
                            <select name="house_on_land"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="co">Có</option>
                                <option value="khong">Không</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tài chính</label>
                            <select name="price_range"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="0-500">Dưới 500 triệu</option>
                                <option value="500-1000">500 triệu - 1 tỷ</option>
                                <option value="1000-2000">1 - 2 tỷ</option>
                                <option value="2000-5000">2 - 5 tỷ</option>
                                <option value="5000+">Trên 5 tỷ</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Thổ cư</label>
                            <select name="tho_cu"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="full">100% thổ cư</option>
                                <option value="partial">Một phần thổ cư</option>
                                <option value="none">Chưa có thổ cư</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Đường thể hiện (trên sổ)</label>
                            <select name="road_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="mat-tien">Mặt tiền đường</option>
                                <option value="hem">Hẻm</option>
                                <option value="ngo">Ngõ</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chiều dài mặt tiền</label>
                            <select name="frontage"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="0-5">Dưới 5m</option>
                                <option value="5-10">5 - 10m</option>
                                <option value="10-20">10 - 20m</option>
                                <option value="20+">Trên 20m</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Diện tích</label>
                            <select name="area_range"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="0-100">Dưới 100 m²</option>
                                <option value="100-200">100 - 200 m²</option>
                                <option value="200-500">200 - 500 m²</option>
                                <option value="500-1000">500 - 1000 m²</option>
                                <option value="1000+">Trên 1000 m²</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hướng đất</label>
                            <select name="direction"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="dong">Đông</option>
                                <option value="tay">Tây</option>
                                <option value="nam">Nam</option>
                                <option value="bac">Bắc</option>
                                <option value="dong-nam">Đông Nam</option>
                                <option value="dong-bac">Đông Bắc</option>
                                <option value="tay-nam">Tây Nam</option>
                                <option value="tay-bac">Tây Bắc</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số tờ</label>
                            <input type="text" name="so_to" placeholder="Số tờ"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số thửa</label>
                            <input type="text" name="so_thua" placeholder="Số Thửa"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Row 4 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cách sắp xếp</label>
                            <select name="sort"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                                <option value="">-- Tất cả --</option>
                                <option value="newest">Mới nhất</option>
                                <option value="oldest">Cũ nhất</option>
                                <option value="price_asc">Giá thấp đến cao</option>
                                <option value="price_desc">Giá cao đến thấp</option>
                                <option value="area_asc">Diện tích nhỏ đến lớn</option>
                                <option value="area_desc">Diện tích lớn đến nhỏ</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tìm nhanh</label>
                            <input type="text" name="phone" placeholder="Nhập mã số hoặc số điện thoại (nếu có)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:outline-none">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="button" id="resetFilters"
                                class="flex-1 px-4 py-2 border-2 border-green-500 text-green-500 font-semibold rounded-lg hover:bg-green-50 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Làm mới bộ lọc
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Tìm kiếm
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Toggle Filter Script -->
    <script>
        document.getElementById('toggleFilters').addEventListener('click', function () {
            const filters = document.getElementById('advancedFilters');
            const arrow = document.getElementById('filterArrow');
            filters.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });

        document.getElementById('resetFilters').addEventListener('click', function () {
            const form = document.getElementById('searchForm');
            form.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            form.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
        });
    </script>


    <!-- Featured Listings -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Bất động sản nổi bật</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($consignments as $item)
                    @include('components.consignment-card', ['consignment' => $item])
                @empty
                    <p class="col-span-full text-center text-gray-500 py-8">
                        Chưa có bất động sản nào
                    </p>
                @endforelse
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('consignments.index') }}"
                    class="inline-flex items-center px-6 py-3 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition">
                    Xem tất cả
                </a>
            </div>
        </div>
    </section>

    <!-- Locations -->
    @if(count($locations) > 0)
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Khu vực nổi bật</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($locations as $location)
                        <a href="{{ route('consignments.index', ['province' => $location['province']]) }}"
                            class="p-4 bg-gray-50 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 border border-transparent transition text-center">
                            <p class="font-medium text-gray-900">{{ $location['province'] }}</p>
                            <p class="text-sm text-gray-500">{{ $location['count'] }} tin</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA Section -->
    <section class="py-16 bg-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                Bạn có bất động sản cần ký gửi?
            </h2>
            <p class="text-indigo-100 mb-8">
                Đăng ký ngay để đăng tin và tiếp cận hàng nghìn khách hàng tiềm năng
            </p>
            <a href="http://localhost:3015"
                class="inline-flex items-center px-8 py-4 bg-white text-indigo-600 rounded-lg hover:bg-gray-100 transition font-semibold">
                Đăng tin ngay
            </a>
        </div>
    </section>
    <!-- Map Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Bản đồ Bất động sản</h2>

            <div id="property-map" class="w-full h-[500px] rounded-lg shadow-lg overflow-hidden"></div>
        </div>
    </section>

    <!-- Map Scripts -->
    @php
        $propertiesData = collect($consignments)->map(function ($item) {
            return [
                'id' => $item['id'] ?? rand(1000, 9999),
                'title' => $item['title'] ?? 'Bất động sản',
                'price' => $item['price'] ?? 0,
                'priceFormatted' => isset($item['price']) ? number_format($item['price'] / 1000000000, 2) . ' tỷ' : 'Liên hệ',
                'address' => $item['address'] ?? '',
                'province' => $item['province'] ?? '',
                'district' => $item['district'] ?? '',
                'area' => $item['area'] ?? 0,
                'direction' => $item['direction'] ?? '',
                'image' => $item['image'] ?? '/images/placeholder.jpg',
                'lat' => $item['lat'] ?? (10.8 + (rand(-100, 100) / 1000)),
                'lng' => $item['lng'] ?? (106.6 + (rand(-100, 100) / 1000)),
            ];
        });
    @endphp
    <script>
        // Property data from server
        const properties = @json($propertiesData);

        let map;
        let markers = [];
        let activeInfoWindow = null;

        function initMap() {
            // Center on Vietnam (Ho Chi Minh City area)
            const center = { lat: 12.5, lng: 108.5 };

            map = new google.maps.Map(document.getElementById('property-map'), {
                zoom: 6,
                center: center,
                mapTypeControl: true,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DEFAULT,
                    position: google.maps.ControlPosition.TOP_LEFT
                },
                streetViewControl: true,
                fullscreenControl: true
            });

            // Add markers for each property
            properties.forEach(property => {
                addMarker(property);
            });

            // Cluster markers if library loaded
            if (typeof markerClusterer !== 'undefined') {
                new markerClusterer.MarkerClusterer({
                    map,
                    markers,
                    renderer: {
                        render: ({ count, position }) => {
                            return new google.maps.Marker({
                                position,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    fillColor: '#22c55e',
                                    fillOpacity: 1,
                                    strokeColor: '#16a34a',
                                    strokeWeight: 2,
                                    scale: 20,
                                },
                                label: {
                                    text: String(count),
                                    color: 'white',
                                    fontWeight: 'bold'
                                },
                                zIndex: Number(google.maps.Marker.MAX_ZINDEX) + count
                            });
                        }
                    }
                });
            }
        }

        function addMarker(property) {
            const marker = new google.maps.Marker({
                position: { lat: property.lat, lng: property.lng },
                map: map,
                title: property.title,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#22c55e',
                    fillOpacity: 1,
                    strokeColor: '#16a34a',
                    strokeWeight: 2,
                    scale: 10,
                },
                animation: google.maps.Animation.DROP
            });

            // Create info window content
            const infoContent = `
                            <div style="width: 280px; font-family: Arial, sans-serif;">
                                <img src="${property.image}" alt="${property.title}" 
                                     style="width: 100%; height: 140px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;"
                                     onerror="this.src='https://via.placeholder.com/280x140?text=No+Image'">
                                <p style="color: #666; font-size: 12px; margin: 0 0 5px 0;">Mã số: ${property.id}</p>
                                <p style="font-weight: bold; color: #333; font-size: 13px; margin: 0 0 8px 0; 
                                   display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    ${property.title}
                                </p>
                                <p style="color: #dc2626; font-weight: bold; font-size: 16px; margin: 0 0 8px 0;">
                                    ${property.priceFormatted}
                                </p>
                                <p style="color: #666; font-size: 12px; margin: 0 0 5px 0;">
                                    Địa chỉ: ${property.address || property.district + ', ' + property.province}
                                </p>
                                <div style="display: flex; gap: 15px; font-size: 12px; color: #666; margin-top: 8px;">
                                    <span>Hướng: ${property.direction || 'N/A'}</span>
                                    <span>Diện tích: ${property.area} m²</span>
                                </div>
                                <a href="/consignments/${property.id}" 
                                   style="display: block; text-align: center; margin-top: 10px; padding: 8px; 
                                          background: #4f46e5; color: white; border-radius: 6px; text-decoration: none;">
                                    Xem chi tiết
                                </a>
                            </div>
                        `;

            const infoWindow = new google.maps.InfoWindow({
                content: infoContent,
                maxWidth: 300
            });

            marker.addListener('click', () => {
                if (activeInfoWindow) {
                    activeInfoWindow.close();
                }
                infoWindow.open(map, marker);
                activeInfoWindow = infoWindow;
            });

            markers.push(marker);
        }
    </script>

    <!-- Google Maps API -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&callback=initMap&libraries=marker">
        </script>

    <!-- Marker Clusterer -->
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
@endsection