@extends('layouts.app')

@section('title', 'Sàn Đất - Ký gửi Bất động sản')
@section('description', 'Tìm kiếm và ký gửi bất động sản uy tín')

@section('content')
    <!-- Advanced Search Section -->
    <section class="bg-navy-800 py-6 border-b border-navy-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Search Bar -->
            <form action="{{ route('search.results') }}" method="GET" id="searchForm">
                <div class="flex gap-2 mb-0">
                    <div class="flex-1">
                        <input type="text" name="q" placeholder="Nhập từ khóa tìm kiếm"
                            class="w-full px-4 py-3 bg-navy-700 border-2 border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-100 placeholder-gray-500">
                    </div>
                    <button type="button" id="toggleFilters"
                        class="hidden md:flex px-4 py-3 bg-navy-600 text-gray-200 font-semibold rounded-lg hover:bg-navy-500 border border-green-500/30 transition items-center gap-2 whitespace-nowrap">
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
                    <button type="submit"
                        class="px-6 py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition whitespace-nowrap shadow-lg shadow-green-500/25">
                        Tìm Kiếm
                    </button>
                </div>
                <!-- Mobile filter button -->
                <div class="md:hidden">
                    <button type="button" id="toggleFiltersMobile"
                        class="w-full px-6 mt-3 py-3 bg-navy-600 text-gray-200 font-semibold rounded-lg hover:bg-navy-500 border border-green-500/30 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Bộ lọc chi tiết
                        <svg class="w-4 h-4 transition-transform" id="filterArrowMobile" fill="none" stroke="currentColor"
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
                            <label class="block text-sm font-medium text-gray-300 mb-1">Diện tích đất</label>
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
                            <label class="block text-sm font-medium text-gray-300 mb-1">Diện tích sàn</label>
                            <select name="floor_area_range"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Tất cả --</option>
                                <option value="0-50">Dưới 50 m²</option>
                                <option value="50-100">50 - 100 m²</option>
                                <option value="100-200">100 - 200 m²</option>
                                <option value="200-500">200 - 500 m²</option>
                                <option value="500+">Trên 500 m²</option>
                            </select>
                        </div>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tìm nhanh</label>
                            <input type="text" name="phone" placeholder="STT hoặc SĐT"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200 placeholder-gray-500">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="button" id="resetFilters"
                                class="flex-1 px-4 py-2 border border-green-500 text-green-400 font-semibold rounded-lg hover:bg-green-500/10 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Làm mới
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition flex items-center justify-center gap-2 shadow-lg shadow-green-500/25 border border-green-500">
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
        function toggleFilterPanel() {
            const filters = document.getElementById('advancedFilters');
            const arrow = document.getElementById('filterArrow');
            const arrowMobile = document.getElementById('filterArrowMobile');
            filters.classList.toggle('hidden');
            if (arrow) arrow.classList.toggle('rotate-180');
            if (arrowMobile) arrowMobile.classList.toggle('rotate-180');
        }
        document.getElementById('toggleFilters').addEventListener('click', toggleFilterPanel);
        document.getElementById('toggleFiltersMobile')?.addEventListener('click', toggleFilterPanel);

        document.getElementById('resetFilters').addEventListener('click', function () {
            const form = document.getElementById('searchForm');
            form.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
            form.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
        });

        // If no search/filter values, go to homepage instead of search results
        // Otherwise, strip empty params from the URL
        document.getElementById('searchForm').addEventListener('submit', function (e) {
            const form = this;
            let hasValue = false;

            // Check all inputs and selects for values
            form.querySelectorAll('input[type="text"], select').forEach(el => {
                if (el.value.trim()) {
                    hasValue = true;
                } else {
                    el.disabled = true; // Disable empty fields so they don't appear in URL
                }
            });

            if (!hasValue) {
                e.preventDefault();
                window.location.href = '/';
                return;
            }

            // Re-enable after a short delay (for browser back button)
            setTimeout(() => {
                form.querySelectorAll('[disabled]').forEach(el => el.disabled = false);
            }, 100);
        });
    </script>

    <!-- Map Section -->
    <section class="sm:py-8 py-4 bg-navy-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div>
                <div id="locationStatus" class="flex items-center gap-2 text-sm">
                    <button onclick="requestLocation()"
                        class="inline-flex items-center text-gray-400 hover:text-green-400 transition cursor-pointer bg-transparent border-0 p-0 mb-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Hiển thị theo vị trí gần tôi
                    </button>
                </div>
            </div>
            <h2 class="sm:text-3xl text-xl font-bold text-gray-100 sm:mb-8 mb-4">Bất động sản cần bán</h2>

            <div id="property-map" class="w-full h-[500px] rounded-lg shadow-lg overflow-hidden border border-navy-600">
            </div>
        </div>
    </section>

    <!-- Bất động sản đang bán Section -->
    <section class="sm:py-8 py-4" id="all-properties-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <div class="flex flex-1 items-end gap-1">
                    <button onclick="setHomeView('grid')" id="home-btn-grid"
                        class="p-2 rounded-lg border border-navy-600 transition" title="Dạng lưới">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button onclick="setHomeView('list')" id="home-btn-list"
                        class="p-2 rounded-lg border border-navy-600 transition" title="Dạng danh sách">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Loading Skeleton -->
            <div id="allPropertiesSkeleton" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @for($i = 0; $i < 12; $i++)
                    @include('components.skeleton-card')
                @endfor
            </div>

            <!-- Properties Grid -->
            <div id="allPropertiesGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6 hidden"></div>

            <!-- Properties List -->
            <div id="allPropertiesList" class="flex flex-col gap-4 hidden"></div>

            <!-- Pagination -->
            <div id="allPropertiesPagination" class="flex justify-center items-center gap-2 mt-8 hidden"></div>
        </div>
    </section>

    <script>
        let currentPage = 1;
        const perPage = 30;
        let totalPages = 1;
        let userLat = 0;
        let userLng = 0;
        let locationReady = false;

        // Request user location (triggered by button click)
        function requestLocation() {
            if (!navigator.geolocation) {
                updateLocationStatus('unavailable');
                return;
            }
            updateLocationStatus('loading');
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    userLat = pos.coords.latitude;
                    userLng = pos.coords.longitude;
                    locationReady = true;
                    updateLocationStatus('active');
                    loadAllProperties(1);
                },
                function (err) {
                    updateLocationStatus('denied');
                },
                { enableHighAccuracy: false, timeout: 8000 }
            );
        }

        function resetLocation() {
            userLat = 0;
            userLng = 0;
            locationReady = false;
            updateLocationStatus('reset');
            loadAllProperties(1);
        }

        function updateLocationStatus(status) {
            const el = document.getElementById('locationStatus');
            if (!el) return;
            if (status === 'active') {
                el.innerHTML = `
                    <span class="inline-flex items-center text-green-400">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Đang hiển thị theo vị trí gần bạn
                    </span>
                    <button onclick="resetLocation()" class="ml-3 text-gray-400 hover:text-gray-200 underline text-xs bg-transparent border-0 cursor-pointer">Bỏ lọc vị trí</button>`;
            } else if (status === 'loading') {
                el.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span><span class="text-gray-400">Đang xác định vị trí...</span>';
            } else if (status === 'denied') {
                el.innerHTML = '<span class="text-red-400 text-xs">Không lấy được vị trí. Vui lòng cho phép truy cập vị trí trong trình duyệt.</span>';
            } else if (status === 'reset' || status === 'unavailable') {
                el.innerHTML = `<button onclick="requestLocation()" class="inline-flex items-center text-gray-400 hover:text-green-400 transition cursor-pointer bg-transparent border-0 p-0">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Hiển thị theo vị trí gần tôi
                </button>`;
            }
        }

        function formatDistance(km) {
            if (!km && km !== 0) return '';
            if (km < 1) return Math.round(km * 1000) + ' m';
            return km.toFixed(1) + ' km';
        }

        function cleanNumber(val) {
            if (val === null || val === undefined) return val;
            const s = String(val);
            return s.replace(/\.00$/, '').replace(/(\.[1-9])0$/, '$1');
        }

        function calcDistance(item) {
            if (item.distance !== undefined && item.distance !== null) return item.distance;
            if (!userLat || !userLng) return null;
            const lat2 = parseFloat(item.lat || item.latitude);
            const lng2 = parseFloat(item.lng || item.longitude);
            if (!lat2 || !lng2) return null;
            const R = 6371;
            const dLat = (lat2 - userLat) * Math.PI / 180;
            const dLng = (lng2 - userLng) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(userLat * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function loadAllProperties(page = 1) {
            currentPage = page;
            const skeleton = document.getElementById('allPropertiesSkeleton');
            const grid = document.getElementById('allPropertiesGrid');
            const pagination = document.getElementById('allPropertiesPagination');

            skeleton.classList.remove('hidden');
            grid.classList.add('hidden');
            pagination.classList.add('hidden');

            let apiUrl = `/api/consignments?page=${page}&limit=${perPage}&sort=latest`;
            if (userLat && userLng) {
                apiUrl += `&lat=${userLat}&lng=${userLng}&max_distance=15`;
            }
            fetch(apiUrl)
                .then(res => res.json())
                .then(data => {
                    let items = data.data || [];
                    const total = data.total || 0;

                    // Client-side filter: only show items within 15km when location is active
                    if (locationReady && userLat && userLng) {
                        items = items.filter(item => {
                            const d = calcDistance(item);
                            item._distance = d; // cache for badge
                            return d !== null && d <= 15;
                        });
                    }

                    totalPages = Math.ceil(total / perPage);

                    const listContainer = document.getElementById('allPropertiesList');

                    const emptyMsg = locationReady
                        ? '<p class="col-span-full text-center text-gray-400 py-8">Không có bất động sản nào trong bán kính 15km. <button onclick="resetLocation()" class="text-green-400 underline">Xem tất cả</button></p>'
                        : '<p class="col-span-full text-center text-gray-500 py-8">Chưa có bất động sản nào</p>';

                    grid.innerHTML = items.length > 0
                        ? items.map(item => renderPropertyCard(item)).join('')
                        : emptyMsg;

                    listContainer.innerHTML = items.length > 0
                        ? items.map(item => renderPropertyListCard(item)).join('')
                        : emptyMsg;

                    renderPagination(currentPage, totalPages, pagination);

                    // Update map to show only current results
                    updateMapMarkers(items);

                    skeleton.classList.add('hidden');
                    const savedMode = localStorage.getItem('viewMode') || 'grid';
                    if (savedMode === 'list') {
                        grid.classList.add('hidden');
                        listContainer.classList.remove('hidden');
                    } else {
                        grid.classList.remove('hidden');
                        listContainer.classList.add('hidden');
                    }
                    grid.style.opacity = '0';
                    listContainer.style.opacity = '0';
                    requestAnimationFrame(() => {
                        grid.style.transition = 'opacity 0.3s ease-in';
                        grid.style.opacity = '1';
                        listContainer.style.transition = 'opacity 0.3s ease-in';
                        listContainer.style.opacity = '1';
                    });
                    pagination.classList.remove('hidden');
                    setHomeView(savedMode);
                })
                .catch(err => console.error('Error:', err));
        }

        const directionMap = { 'dong': 'Đông', 'tay': 'Tây', 'nam': 'Nam', 'bac': 'Bắc', 'dong-nam': 'Đông Nam', 'dong_nam': 'Đông Nam', 'dong-bac': 'Đông Bắc', 'dong_bac': 'Đông Bắc', 'tay-nam': 'Tây Nam', 'tay_nam': 'Tây Nam', 'tay-bac': 'Tây Bắc', 'tay_bac': 'Tây Bắc' };
        function mapDirection(d) { return directionMap[d] || d; }

        const landTypeMap = {
            'dat-nen': 'Đất nền', 'dat_nen': 'Đất nền',
            'dat-nong-nghiep': 'Đất nông nghiệp', 'dat_nong_nghiep': 'Đất nông nghiệp',
            'dat-vuon': 'Đất vườn', 'dat_vuon': 'Đất vườn',
            'nha-pho': 'Nhà phố', 'nha_pho': 'Nhà phố',
            'biet-thu': 'Biệt thự', 'biet_thu': 'Biệt thự',
        };
        function mapLandType(t) { return landTypeMap[t] || t; }

        function formatPrice(p) {
            if (!p) return 'Liên hệ';
            if (p >= 1000000000) {
                const val = p / 1000000000;
                return (val % 1 === 0 ? val.toFixed(0) : parseFloat(val.toFixed(1))) + ' tỷ';
            } else if (p >= 1000000) {
                const val = p / 1000000;
                return (val % 1 === 0 ? val.toFixed(0) : parseFloat(val.toFixed(0))) + ' triệu';
            }
            return new Intl.NumberFormat('vi-VN').format(p) + ' đ';
        }

        function renderPropertyCard(item) {
            const price = formatPrice(item.price);
            const images = Array.isArray(item.images) ? item.images : (typeof item.images === 'string' ? (JSON.parse(item.images || '[]') || []) : []);
            const firstImage = item.featured_image || images.find(i => i) || '';
            const slug = item.seo_url || item.id;

            // Parse directions
            let dirs = item.land_directions;
            if (typeof dirs === 'string') { try { dirs = JSON.parse(dirs); } catch (e) { dirs = []; } }
            const dirText = Array.isArray(dirs) && dirs.length > 0 ? dirs.map(mapDirection).join(', ') : '';

            const imgHtml = firstImage
                ? `<img src="${firstImage}" alt="${item.title || 'BĐS'}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" onerror="this.onerror=null;this.style.display='none';this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center text-gray-400 text-sm bg-gray-100\\'>No Image</div>'">`
                : `<div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">No Image</div>`;

            const dist = locationReady ? calcDistance(item) : null;
            const distanceBadge = (dist !== null)
                ? `<span class="absolute top-2 left-2 px-2 py-0.5 bg-blue-600/90 text-white text-xs rounded-full font-medium">📍 ${formatDistance(dist)}</span>`
                : '';

            let gridItems = '';
            if (item.area_dimensions) gridItems += `<p><span class="text-gray-400">Diện tích:</span> ${item.area_dimensions}</p>`;
            if (item.residential_area) gridItems += `<p><span class="text-gray-400">Thổ cư:</span> ${cleanNumber(item.residential_area)} m²</p>`;
            if (dirText) gridItems += `<p><span class="text-gray-400">Hướng:</span> ${dirText}</p>`;
            if (item.road) gridItems += `<p><span class="text-gray-400">Loại đường:</span> ${item.road}</p>`;
            if (item.frontage_actual && item.frontage_actual !== '0' && item.frontage_actual !== '0.00') gridItems += `<p><span class="text-gray-400">Mặt tiền:</span> ${parseFloat(item.frontage_actual)} m</p>`;
            if (item.has_house) gridItems += `<p><span class="text-gray-400">Tình trạng:</span> ${item.has_house === 'co' || item.has_house === 'yes' ? 'Có nhà' : 'Chưa bán'}</p>`;
            gridItems += `<p><span class="text-orange-500 font-bold">Giá: ${price}</span></p>`;

            return `
                                                                                                                                    <a href="/bat-dong-san/${slug}" class="flex flex-col md:flex-row bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition group border border-navy-600">
                                                                                                                                        <div class="w-full md:w-48 flex-shrink-0 bg-navy-800 relative overflow-hidden aspect-video md:aspect-auto">
                                                                                                                                            ${imgHtml}
                                                                                                                                            ${distanceBadge}
                                                                                                                                        </div>
                                                                                                                                        <div class="flex-1 p-3 md:p-4 flex flex-col justify-between min-w-0">
                                                                                                                                            <div>
                                                                                                                                                ${item.order_number ? `<p class="text-xs text-gray-500 mb-1 font-medium">Mã Số: ${item.order_number}</p>` : ''}
                                                                                                                                                <h3 class="font-bold uppercase text-sm md:text-base mb-2 line-clamp-2">${item.title || 'Bất động sản'}</h3>
                                                                                                                                                <div class="text-xs md:text-sm text-gray-600">
                                                                                                                                                    ${item.address ? `<p class="mb-1"><span class="text-gray-400">Địa chỉ:</span> ${item.address}</p>` : ''}
                                                                                                                                                    <div class="grid grid-cols-2 gap-x-2 gap-y-0.5">
                                                                                                                                                        ${gridItems}
                                                                                                                                                    </div>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </a>
                                                                                                                                `;
        }

        function renderPropertyListCard(item) {
            const price = formatPrice(item.price);
            const images = Array.isArray(item.images) ? item.images : (typeof item.images === 'string' ? (JSON.parse(item.images || '[]') || []) : []);
            const firstImage = item.featured_image || images.find(i => i) || '';
            const slug = item.seo_url || item.id;

            let landTypes = item.land_types;
            if (typeof landTypes === 'string') { try { landTypes = JSON.parse(landTypes); } catch (e) { landTypes = []; } }
            const rawType = (Array.isArray(landTypes) && landTypes.length > 0) ? landTypes[0] : (item.type || '');
            const typeLabel = rawType ? mapLandType(rawType) : '';

            let dirs = item.land_directions;
            if (typeof dirs === 'string') { try { dirs = JSON.parse(dirs); } catch (e) { dirs = []; } }
            const dirText = Array.isArray(dirs) && dirs.length > 0 ? dirs.map(mapDirection).join(', ') : '';

            const imgHtml = firstImage
                ? `<img src="${firstImage}" alt="${item.title || 'BĐS'}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" onerror="this.onerror=null;this.style.display='none';this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center text-gray-500 text-sm\\' style=\\'background:#1e293b\\'>No Image</div>'">`
                : `<div class="w-full h-full flex items-center justify-center text-gray-500 text-sm">No Image</div>`;

            let details = '';
            if (item.address) details += `<p><span class="text-gray-500">Địa chỉ:</span> ${item.address}</p>`;
            if (item.area_dimensions) details += `<span><span class="text-gray-500">Diện tích:</span> ${item.area_dimensions}</span>`;
            if (item.residential_area) details += `<span><span class="text-gray-500">Thổ cư:</span> ${cleanNumber(item.residential_area)} m²</span>`;
            if (dirText) details += `<span><span class="text-gray-500">Hướng:</span> ${dirText}</span>`;
            if (item.road) details += `<span><span class="text-gray-500">Đường:</span> ${item.road}</span>`;

            const dist = locationReady ? calcDistance(item) : null;
            const distanceBadge = (dist !== null)
                ? `<span class="absolute bottom-2 left-2 px-2 py-1 bg-blue-600/90 text-white text-xs rounded-full font-medium">📍 ${formatDistance(dist)}</span>`
                : '';

            return `
                                                                                                                                    <a href="/bat-dong-san/${slug}" class="flex bg-navy-700 rounded-lg shadow-md overflow-hidden hover:shadow-xl hover:shadow-green-500/10 transition group border border-navy-600">
                                                                                                                                        <div class="w-48 md:w-64 h-40 flex-shrink-0 bg-navy-800 relative overflow-hidden">
                                                                                                                                            ${imgHtml}
                                                                                                                                            ${typeLabel ? `<span class="absolute top-2 left-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full font-medium">${typeLabel}</span>` : ''}
                                                                                                                                            ${distanceBadge}
                                                                                                                                        </div>
                                                                                                                                        <div class="flex-1 p-4 flex flex-col justify-between">
                                                                                                                                            <div>
                                                                                                                                                ${item.order_number ? `<p class="text-xs text-gray-400 mb-1">STT: ${item.order_number}</p>` : ''}
                                                                                                                                                <h3 class="font-semibold text-gray-100 text-lg mb-2">${item.title || 'Bất động sản'}</h3>
                                                                                                                                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-400 mb-2">
                                                                                                                                                    ${details}
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                            <p class="text-green-400 font-bold text-lg">${price}</p>
                                                                                                                                        </div>
                                                                                                                                    </a>
                                                                                                                                `;
        }

        function setHomeView(mode) {
            const grid = document.getElementById('allPropertiesGrid');
            const list = document.getElementById('allPropertiesList');
            const btnGrid = document.getElementById('home-btn-grid');
            const btnList = document.getElementById('home-btn-list');

            if (mode === 'list') {
                grid.classList.add('hidden');
                list.classList.remove('hidden');
                btnGrid.classList.remove('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnGrid.classList.add('text-gray-400');
                btnList.classList.add('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnList.classList.remove('text-gray-400');
            } else {
                list.classList.add('hidden');
                grid.classList.remove('hidden');
                btnList.classList.remove('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnList.classList.add('text-gray-400');
                btnGrid.classList.add('bg-green-500/20', 'text-green-400', 'border-green-500');
                btnGrid.classList.remove('text-gray-400');
            }
            localStorage.setItem('viewMode', mode);
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
                    return $imgs[0] ?? $item['featured_image'] ?? $item['image'] ?? '/images/placeholder.jpg';
                })($item),
                'lat' => !empty($item['lat'] ?? $item['latitude'] ?? null) ? (float) ($item['lat'] ?? $item['latitude']) : null,
                'lng' => !empty($item['lng'] ?? $item['longitude'] ?? null) ? (float) ($item['lng'] ?? $item['longitude']) : null,
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
                zoom: 5,
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

            // Parse directions for popup
            let popupDirs = property.land_directions;
            if (typeof popupDirs === 'string') { try { popupDirs = JSON.parse(popupDirs); } catch (e) { popupDirs = []; } }
            const popupDirText = Array.isArray(popupDirs) && popupDirs.length > 0 ? popupDirs.map(mapDirection).join(', ') : '';

            // Build detail rows (same fields as property card)
            let popupDetails = '';
            if (property.address) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Địa chỉ:</span> ${property.address}</p>`;
            if (property.area_dimensions) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Diện tích:</span> ${property.area_dimensions}</p>`;
            if (property.residential_area) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Thổ cư:</span> ${parseFloat(property.residential_area)} m²</p>`;
            if (popupDirText) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Hướng:</span> ${popupDirText}</p>`;
            if (property.road) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Loại đường:</span> ${property.road}</p>`;
            if (property.frontage_actual && property.frontage_actual !== '0' && property.frontage_actual !== '0.00') popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Mặt tiền:</span> ${parseFloat(property.frontage_actual)} m</p>`;
            if (property.has_house) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Tình trạng:</span> ${property.has_house === 'co' || property.has_house === 'yes' ? 'Có nhà' : 'Chưa bán'}</p>`;

            // Create info window content
            const infoContent = `
                                            <div style="width:350px;max-width:90vw;font-family:Arial,sans-serif;background:var(--navy-800);border-radius:12px;overflow:hidden;">
                                                <img src="${property.image}" alt="${property.title}"
                                                    style="width:100%;height:160px;object-fit:cover;"
                                                    onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22350%22 height=%22160%22%3E%3Crect fill=%22%23334155%22 width=%22350%22 height=%22160%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%2394a3b8%22 font-size=%2214%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                                                <div style="padding:12px;">
                                                    ${property.id ? `<p style="color:#6b7280;font-size:11px;margin:0 0 4px;font-weight:500;">Mã Số: ${property.id}</p>` : ''}
                                                    <p style="font-weight:bold;color:var(--gray-100);font-size:14px;margin:0 0 8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-transform:uppercase;">
                                                        ${property.title}
                                                    </p>
                                                    <p style="color:#f97316;font-weight:bold;font-size:16px;margin:0 0 8px;">Giá: ${property.priceFormatted}</p>
                                                    ${popupDetails}
                                                    <a href="/bat-dong-san/${property.seo_url || property.id}"
                                                        style="display:block;text-align:center;margin-top:10px;padding:8px;background:#22c55e;color:white;border-radius:6px;text-decoration:none;font-weight:600;">
                                                        Xem chi tiết
                                                    </a>
                                                </div>
                                            </div>`;

            const infoWindow = new google.maps.InfoWindow({
                content: infoContent,
                maxWidth: 380
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

        // Update map markers to match current filtered results
        function updateMapMarkers(items) {
            if (!map) return;
            // Clear existing markers
            markers.forEach(m => m.setMap(null));
            markers = [];
            if (activeInfoWindow) { activeInfoWindow.close(); activeInfoWindow = null; }

            // Add markers for items with valid coordinates
            items.forEach(item => {
                const lat = parseFloat(item.latitude || item.lat);
                const lng = parseFloat(item.longitude || item.lng);
                if (!lat || !lng) return;

                const images = Array.isArray(item.images) ? item.images : (typeof item.images === 'string' ? (JSON.parse(item.images || '[]') || []) : []);
                const img = item.featured_image || images[0] || '/images/placeholder.jpg';

                addMarker({
                    id: item.order_number || item.id,
                    title: item.title || 'Bất động sản',
                    price: item.price || 0,
                    priceFormatted: formatPrice(item.price),
                    address: item.address || '',
                    province: item.province || '',
                    district: item.ward || '',
                    area_dimensions: item.area_dimensions || '',
                    residential_area: item.residential_area || '',
                    land_directions: item.land_directions || [],
                    road: item.road || '',
                    frontage_actual: item.frontage_actual || '',
                    has_house: item.has_house || '',
                    seo_url: item.seo_url || '',
                    image: img,
                    lat: lat,
                    lng: lng
                });
            });

            // Fit map bounds to visible markers
            if (markers.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                markers.forEach(m => bounds.extend(m.getPosition()));
                map.fitBounds(bounds);
                if (markers.length === 1) map.setZoom(14);
            }
        }
    </script>


    <!-- Featured Provinces Section -->
    @if(!empty($featuredProvinces) && count($featuredProvinces) > 0)
        <section class="sm:py-8 py-4 bg-navy-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-100 mb-6">Bất động sản theo địa điểm</h2>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4" style="grid-auto-rows: 180px;">
                    @foreach($featuredProvinces as $index => $province)
                        <a href="{{ url('/tim-kiem?province=' . urlencode($province['name'])) }}"
                            class="relative rounded-xl overflow-hidden group cursor-pointer {{ $index === 0 ? 'col-span-2 row-span-2 md:col-span-1 md:row-span-2' : '' }}"
                            style="{{ $index === 0 ? 'grid-row: span 2;' : '' }}">
                            <!-- Image carousel -->
                            <div class="absolute inset-0 province-carousel" data-province-idx="{{ $index }}">
                                @if(!empty($province['images']))
                                    @foreach($province['images'] as $imgIdx => $img)
                                        <img src="{{ $img }}" alt="{{ $province['name'] }}"
                                            class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 {{ $imgIdx === 0 ? 'opacity-100' : 'opacity-0' }}"
                                            data-slide="{{ $imgIdx }}" />
                                    @endforeach
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-gray-600 to-gray-800"></div>
                                @endif
                            </div>
                            <!-- Overlay gradient -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent group-hover:from-black/80 transition-all">
                            </div>
                            <!-- Content -->
                            <div class="absolute bottom-0 left-0 right-0 p-3 md:p-4 z-10">
                                <h3 class="text-white font-bold text-sm md:text-lg leading-tight">{{ $province['name'] }}</h3>
                                <p class="text-gray-300 text-xs md:text-sm mt-0.5 text-white">
                                    {{ number_format($province['consignment_count']) }} tin đăng
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        <script>
            // Province image carousel — fade every 3 seconds
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.province-carousel').forEach(carousel => {
                    const slides = carousel.querySelectorAll('img[data-slide]');
                    if (slides.length <= 1) return;
                    let current = 0;
                    setInterval(() => {
                        slides[current].style.opacity = '0';
                        current = (current + 1) % slides.length;
                        slides[current].style.opacity = '1';
                    }, 3000);
                });
            });
        </script>
    @endif
    <!-- CTA Section -->
    <section class="sm:py-24 py-8 bg-gradient-to-r from-green-600 to-green-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="sm:text-3xl text-2xl font-bold text-white mb-4">
                Bạn có bất động sản cần ký gửi?
            </h2>
            <p class="text-green-100 mb-8">
                Đăng ký ngay để đăng tin và tiếp cận hàng nghìn khách hàng tiềm năng
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ env('APP_URL_SANDAT') }}"
                    class="inline-flex items-center px-8 py-4 bg-navy-700 text-gray-100 rounded-lg hover:bg-navy-600 transition font-semibold shadow-xl border border-navy-600">
                    Đăng tin ngay
                </a>

                <!-- Download App Button -->
                <button
                    class="pwa-install-btn inline-flex items-center gap-2 px-8 py-4 bg-white/10 backdrop-blur text-white rounded-lg font-semibold hover:bg-white/20 transition-all shadow-xl border border-white/20"
                    onclick="if(window.deferredPrompt){window.deferredPrompt.prompt();window.deferredPrompt.userChoice.then(function(){window.deferredPrompt=null})}">
                    <svg class="w-5 h-5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Tải ứng dụng
                </button>
            </div>
        </div>
    </section>

    <!-- Google Maps API -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key', '') }}&callback=initMap&libraries=marker">
        </script>

    <!-- Marker Clusterer -->
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
@endsection