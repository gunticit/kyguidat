@extends('layouts.app')

@section('title', 'Sàn Đất - Ký gửi Bất động sản')
@section('description', 'Tìm kiếm và ký gửi bất động sản uy tín')

@section('content')
    <!-- Advanced Search Section -->
    <section class="bg-navy-800 py-6 border-b border-navy-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Search Bar -->
            <form action="{{ route('search.results') }}" method="GET" id="searchForm">
                <div class="flex flex-col md:flex-row gap-3 mb-4">
                    <div class="flex-1">
                        <input type="text" name="q" placeholder="Tìm theo mã số hoặc số điện thoại"
                            class="w-full px-4 py-3 bg-navy-700 border-2 border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-100 placeholder-gray-500">
                    </div>
                    <button type="submit"
                        class="px-8 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition flex items-center justify-center gap-2 shadow-lg shadow-green-500/25">
                        Tìm Kiếm
                    </button>
                    <button type="button" id="toggleFilters"
                        class="px-8 py-3 bg-navy-600 text-gray-200 font-semibold rounded-lg hover:bg-navy-500 border border-green-500/30 transition flex items-center justify-center gap-2">
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
                <div id="advancedFilters" class="hidden border-t border-navy-600 pt-4">
                    <!-- Row 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tỉnh - Thành phố</label>
                            <select name="province" id="provinceSelect"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200"
                                onchange="updateWards()">
                                <option value="">-- Tất cả --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Xã / Phường / Đặc khu</label>
                            <select name="district" id="wardSelect"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Chọn tỉnh trước --</option>
                            </select>

                            <script>
                                let provincesData = [];

                                // Load provinces from API on page load
                                (async function loadProvinces() {
                                    try {
                                        const res = await fetch('/api/public/provinces');
                                        const json = await res.json();
                                        provincesData = json.data || [];

                                        const select = document.getElementById('provinceSelect');
                                        provincesData.forEach(p => {
                                            const opt = document.createElement('option');
                                            opt.value = p.slug;
                                            opt.textContent = p.name;
                                            select.appendChild(opt);
                                        });
                                    } catch (e) {
                                        console.error('Failed to load provinces:', e);
                                    }
                                })();

                                function updateWards() {
                                    const slug = document.getElementById('provinceSelect').value;
                                    const wardSelect = document.getElementById('wardSelect');
                                    wardSelect.innerHTML = '<option value="">-- Tất cả --</option>';

                                    if (!slug) {
                                        wardSelect.innerHTML = '<option value="">-- Chọn tỉnh trước --</option>';
                                        return;
                                    }

                                    const province = provincesData.find(p => p.slug === slug);
                                    if (province && province.wards) {
                                        province.wards.forEach(w => {
                                            const opt = document.createElement('option');
                                            opt.value = w.name;
                                            opt.textContent = w.name;
                                            wardSelect.appendChild(opt);
                                        });
                                    }
                                }
                            </script>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Loại bất động sản</label>
                            <select name="property_type"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Tất cả --</option>
                                <option value="dat-nen">Đất nền</option>
                                <option value="dat-nong-nghiep">Đất nông nghiệp</option>
                                <option value="dat-vuon">Đất vườn</option>
                                <option value="nha-pho">Nhà phố</option>
                                <option value="biet-thu">Biệt thự</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Nhà trên đất</label>
                            <select name="house_on_land"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Tất cả --</option>
                                <option value="co">Có</option>
                                <option value="khong">Không</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tài chính</label>
                            <select name="price_range"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Tất cả --</option>
                                <option value="0-500">Dưới 500 triệu</option>
                                <option value="500-1000">500 triệu - 1 tỷ</option>
                                <option value="1000-2000">1 - 2 tỷ</option>
                                <option value="2000-5000">2 - 5 tỷ</option>
                                <option value="5000+">Trên 5 tỷ</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Thổ cư</label>
                            <select name="tho_cu"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Tất cả --</option>
                                <option value="full">100% thổ cư</option>
                                <option value="partial">Một phần thổ cư</option>
                                <option value="none">Chưa có thổ cư</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Đường thể hiện (trên sổ)</label>
                            <select name="road_type"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Tất cả --</option>
                                <option value="mat-tien">Mặt tiền đường</option>
                                <option value="hem">Hẻm</option>
                                <option value="ngo">Ngõ</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Chiều dài mặt tiền</label>
                            <select name="frontage"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
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
                            <label class="block text-sm font-medium text-gray-300 mb-1">Diện tích</label>
                            <select name="area_range"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Tất cả --</option>
                                <option value="0-100">Dưới 100 m²</option>
                                <option value="100-200">100 - 200 m²</option>
                                <option value="200-500">200 - 500 m²</option>
                                <option value="500-1000">500 - 1000 m²</option>
                                <option value="1000+">Trên 1000 m²</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Hướng đất</label>
                            <select name="direction"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
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
                            <label class="block text-sm font-medium text-gray-300 mb-1">Số tờ</label>
                            <input type="text" name="so_to" placeholder="Số tờ"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200 placeholder-gray-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Số thửa</label>
                            <input type="text" name="so_thua" placeholder="Số Thửa"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200 placeholder-gray-500">
                        </div>
                    </div>

                    <!-- Row 4 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Cách sắp xếp</label>
                            <select name="sort"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
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
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tìm nhanh</label>
                            <input type="text" name="phone" placeholder="Nhập mã số hoặc số điện thoại (nếu có)"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200 placeholder-gray-500">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="button" id="resetFilters"
                                class="flex-1 px-4 py-2 border-2 border-green-500 text-green-400 font-semibold rounded-lg hover:bg-green-500/10 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Làm mới
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition flex items-center justify-center gap-2 shadow-lg shadow-green-500/25">
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
            <h2 class="text-3xl font-bold text-gray-100 mb-8">Bất động sản nổi bật</h2>

            <!-- Skeleton Loading Grid -->
            <div id="skeletonGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @for($i = 0; $i < 8; $i++)
                    @include('components.skeleton-card')
                @endfor
            </div>

            <!-- Actual Products Grid (hidden initially) -->
            <div id="productsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 hidden">
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
                    class="inline-flex items-center px-6 py-3 border border-green-500 text-green-400 rounded-lg hover:bg-green-500 hover:text-white transition font-semibold">
                    Xem tất cả
                </a>
            </div>
        </div>
    </section>

    <!-- Skeleton to Products Transition Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const skeletonGrid = document.getElementById('skeletonGrid');
            const productsGrid = document.getElementById('productsGrid');

            // Simulate loading time (or use when data actually loads)
            setTimeout(function () {
                // Fade out skeleton
                skeletonGrid.style.opacity = '0';
                skeletonGrid.style.transition = 'opacity 0.3s ease-out';

                setTimeout(function () {
                    skeletonGrid.classList.add('hidden');
                    productsGrid.classList.remove('hidden');
                    productsGrid.style.opacity = '0';

                    // Fade in products
                    requestAnimationFrame(function () {
                        productsGrid.style.transition = 'opacity 0.3s ease-in';
                        productsGrid.style.opacity = '1';
                    });
                }, 300);
            }, 800); // Show skeleton for 800ms
        });
    </script>

    <!-- Locations -->
    @if(count($locations) > 0)
        <section class="py-16 bg-navy-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-100 mb-8">Khu vực nổi bật</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($locations as $location)
                        <a href="{{ route('consignments.index', ['province' => $location['province']]) }}"
                            class="p-4 bg-navy-700 rounded-lg hover:bg-navy-600 hover:border-green-500/50 border border-navy-600 transition text-center">
                            <p class="font-medium text-gray-100">{{ $location['province'] }}</p>
                            <p class="text-sm text-gray-400">{{ $location['count'] }} tin</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-green-600 to-green-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                Bạn có bất động sản cần ký gửi?
            </h2>
            <p class="text-green-100 mb-8">
                Đăng ký ngay để đăng tin và tiếp cận hàng nghìn khách hàng tiềm năng
            </p>
            <a href="{{ env('APP_URL_SANDAT') }}"
                class="inline-flex items-center px-8 py-4 bg-white text-green-700 rounded-lg hover:bg-gray-100 transition font-semibold shadow-xl">
                Đăng tin ngay
            </a>
        </div>
    </section>
    <!-- Map Section -->
    <section class="py-16 bg-navy-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-100 mb-8">Bản đồ Bất động sản</h2>

            <div id="property-map" class="w-full h-[500px] rounded-lg shadow-lg overflow-hidden border border-navy-600">
            </div>
        </div>
    </section>

    <!-- Bất động sản đang bán Section -->
    <section class="py-16" id="all-properties-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-100 mb-8">Bất động sản đang bán</h2>

            <!-- Loading Skeleton -->
            <div id="allPropertiesSkeleton" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @for($i = 0; $i < 12; $i++)
                    @include('components.skeleton-card')
                @endfor
            </div>

            <!-- Properties Grid -->
            <div id="allPropertiesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 hidden"></div>

            <!-- Pagination -->
            <div id="allPropertiesPagination" class="flex justify-center items-center gap-2 mt-8 hidden"></div>
        </div>
    </section>

    <script>
        let currentPage = 1;
        const perPage = 12;
        let totalPages = 1;

        function loadAllProperties(page = 1) {
            currentPage = page;
            const skeleton = document.getElementById('allPropertiesSkeleton');
            const grid = document.getElementById('allPropertiesGrid');
            const pagination = document.getElementById('allPropertiesPagination');

            skeleton.classList.remove('hidden');
            grid.classList.add('hidden');
            pagination.classList.add('hidden');

            fetch(`/api/consignments?page=${page}&limit=${perPage}`)
                .then(res => res.json())
                .then(data => {
                    const items = data.data || [];
                    const total = data.total || 0;
                    totalPages = Math.ceil(total / perPage);

                    grid.innerHTML = items.length > 0
                        ? items.map(item => renderPropertyCard(item)).join('')
                        : '<p class="col-span-full text-center text-gray-500 py-8">Chưa có bất động sản nào</p>';

                    renderPagination(currentPage, totalPages, pagination);

                    skeleton.classList.add('hidden');
                    grid.classList.remove('hidden');
                    grid.style.opacity = '0';
                    requestAnimationFrame(() => {
                        grid.style.transition = 'opacity 0.3s ease-in';
                        grid.style.opacity = '1';
                    });
                    pagination.classList.remove('hidden');
                })
                .catch(err => console.error('Error:', err));
        }

        function renderPropertyCard(item) {
            let price = 'Liên hệ';
            if (item.price) {
                if (item.price >= 1000000000) {
                    const val = item.price / 1000000000;
                    price = (val % 1 === 0 ? val.toFixed(0) : parseFloat(val.toFixed(1))) + ' tỷ';
                } else if (item.price >= 1000000) {
                    const val = item.price / 1000000;
                    price = (val % 1 === 0 ? val.toFixed(0) : parseFloat(val.toFixed(0))) + ' triệu';
                } else {
                    price = new Intl.NumberFormat('vi-VN').format(item.price) + ' đ';
                }
            }
            const rawImage = item.images?.[0] || item.image || item.featured_image || '/images/placeholder.jpg';
            const image = rawImage.startsWith('data:') ? rawImage : rawImage.replace(/^https?:\/\/[^\/]+/, '');
            const location = [item.district, item.province].filter(Boolean).join(', ');

            const slug = item.seo_url || item.id;

            return `
                                <a href="/bat-dong-san/${slug}" class="bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl hover:shadow-green-500/10 transition group border border-navy-600">
                                    <div class="relative h-48 overflow-hidden">
                                        <img src="${image}" alt="${item.title || 'BĐS'}" 
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                            onerror="this.src='/images/placeholder.jpg'">
                                        <span class="absolute top-2 right-2 px-2 py-1 bg-green-500 text-white text-xs rounded font-medium">
                                            ${item.property_type || 'Đất nền'}
                                        </span>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-100 line-clamp-2 mb-2">${item.title || 'Bất động sản'}</h3>
                                        <p class="text-green-400 font-bold mb-2">${price}</p>
                                        <div class="flex items-center text-sm text-gray-400 gap-3">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                                </svg>
                                                ${item.area || 0}m²
                                            </span>
                                            <span class="flex items-center gap-1 truncate">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                </svg>
                                                ${location}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            `;
        }

        function renderPagination(current, total, container) {
            if (total <= 1) { container.innerHTML = ''; return; }

            let html = `<button onclick="loadAllProperties(${current - 1})" ${current === 1 ? 'disabled' : ''} 
                                                                                                            class="px-3 py-2 rounded-lg ${current === 1 ? 'bg-navy-700 text-gray-600 cursor-not-allowed' : 'bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600'}">
                                                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                                                                                        </button>`;

            const pages = [];
            if (total <= 7) { for (let i = 1; i <= total; i++) pages.push(i); }
            else {
                pages.push(1);
                if (current > 3) pages.push('...');
                for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
                if (current < total - 2) pages.push('...');
                pages.push(total);
            }

            pages.forEach(p => {
                if (p === '...') html += '<span class="px-3 py-2 text-gray-500">...</span>';
                else html += `<button onclick="loadAllProperties(${p})" class="px-4 py-2 rounded-lg ${p === current ? 'bg-green-500 text-white shadow-lg shadow-green-500/25' : 'bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600'}">${p}</button>`;
            });

            html += `<button onclick="loadAllProperties(${current + 1})" ${current === total ? 'disabled' : ''} 
                                                                                                            class="px-3 py-2 rounded-lg ${current === total ? 'bg-navy-700 text-gray-600 cursor-not-allowed' : 'bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600'}">
                                                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                                                                        </button>`;

            container.innerHTML = html;
            if (current !== 1) document.getElementById('all-properties-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.addEventListener('DOMContentLoaded', () => setTimeout(() => loadAllProperties(1), 500));
    </script>

    <!-- Map Scripts -->

    @php
        $propertiesData = collect($consignments)->map(function ($item) {
            return [
                'id' => $item['id'] ?? rand(1000, 9999),
                'title' => $item['title'] ?? 'Bất động sản',
                'price' => $item['price'] ?? 0,
                'priceFormatted' => isset($item['price']) ? (function ($p) {
                    if ($p >= 1000000000)
                        return rtrim(rtrim(number_format($p / 1000000000, 1), '0'), '.') . ' tỷ';
                    if ($p >= 1000000)
                        return rtrim(rtrim(number_format($p / 1000000, 0), '0'), '.') . ' triệu';
                    return number_format($p) . ' đ';
                })($item['price']) : 'Liên hệ',
                'address' => $item['address'] ?? '',
                'province' => $item['province'] ?? '',
                'district' => $item['district'] ?? '',
                'area' => $item['area'] ?? 0,
                'seo_url' => $item['seo_url'] ?? '',
                'direction' => $item['direction'] ?? '',
                'image' => (function ($item) {
                    $imgs = is_string($item['images'] ?? '') ? json_decode($item['images'], true) : ($item['images'] ?? []);
                    $img = $imgs[0] ?? $item['featured_image'] ?? $item['image'] ?? '/images/placeholder.jpg';
                    return str_starts_with($img, 'data:') ? $img : preg_replace('#^https?://[^/]+#', '', $img);
                })($item),
                'lat' => $item['lat'] ?? $item['latitude'] ?? null,
                'lng' => $item['lng'] ?? $item['longitude'] ?? null,
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

            // Add markers for each property with valid coordinates
            properties.filter(p => p.lat && p.lng).forEach(property => {
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
                                                                                                                                <div style="width: 280px; font-family: Arial, sans-serif; background: var(--navy-800); border-radius: 12px; overflow:hidden;">
                                                                                                                                    <img src="${property.image}" alt="${property.title}" 
                                                                                                                                         style="width: 100%; height: 140px; object-fit: cover;"
                                                                                                                                         onerror="this.src='https://via.placeholder.com/280x140?text=No+Image'">
                                                                                                                                    <div style="padding: 12px;">
                                                                                                                                        <p style="color: var(--gray-400); font-size: 12px; margin: 0 0 5px 0;">Mã số: ${property.id}</p>
                                                                                                                                        <p style="font-weight: bold; color: var(--gray-100); font-size: 13px; margin: 0 0 8px 0; 
                                                                                                                                           display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                                                                                                            ${property.title}
                                                                                                                                        </p>
                                                                                                                                        <p style="color: #22c55e; font-weight: bold; font-size: 16px; margin: 0 0 8px 0;">
                                                                                                                                            ${property.priceFormatted}
                                                                                                                                        </p>
                                                                                                                                        <p style="color: var(--gray-400); font-size: 12px; margin: 0 0 5px 0;">
                                                                                                                                            Địa chỉ: ${property.address || property.district + ', ' + property.province}
                                                                                                                                        </p>
                                                                                                                                        <div style="display: flex; gap: 15px; font-size: 12px; color: var(--gray-400); margin-top: 8px;">
                                                                                                                                            <span>Hướng: ${property.direction || 'N/A'}</span>
                                                                                                                                            <span>Diện tích: ${property.area} m²</span>
                                                                                                                                        </div>
                                                                                                                                        <a href="/bat-dong-san/${property.seo_url || property.id}" 
                                                                                                                                           style="display: block; text-align: center; margin-top: 10px; padding: 8px; 
                                                                                                                                                  background: #22c55e; color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">
                                                                                                                                            Xem chi tiết
                                                                                                                                        </a>
                                                                                                                                    </div>
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