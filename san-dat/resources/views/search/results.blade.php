@extends('layouts.app')

@section('title', 'Tìm kiếm: ' . $searchQuery . ' - Sàn Đất')
@section('description', 'Kết quả tìm kiếm bất động sản: ' . $searchQuery)

@section('content')
<!-- Advanced Search Section (same as home) -->
<section class="bg-navy-800 py-6 border-b border-navy-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form action="{{ route('search.results') }}" method="GET" id="searchForm">
            <div class="flex gap-2 mb-0">
                <div class="flex-1">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nhập từ khóa tìm kiếm"
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
            <div id="advancedFilters"
                class="{{ request()->except(['q', 'page']) ? '' : 'hidden' }} border-t border-navy-600 pt-4 mt-4">
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
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Loại bất động sản</label>
                        <select name="property_type"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="dat_nen" {{ request('property_type')=='dat_nen' ? 'selected' : '' }}>Đất nền
                            </option>
                            <option value="dat_tai_dinh_cu" {{ request('property_type')=='dat_tai_dinh_cu' ? 'selected'
                                : '' }}>Đất tái định cư</option>
                            <option value="dat_sao" {{ request('property_type')=='dat_sao' ? 'selected' : '' }}>Đất sào
                            </option>
                            <option value="dat_ray" {{ request('property_type')=='dat_ray' ? 'selected' : '' }}>Đất rẫy
                            </option>
                            <option value="bat_dong_san_nghi_duong" {{
                                request('property_type')=='bat_dong_san_nghi_duong' ? 'selected' : '' }}>Bất động sản
                                nghỉ dưỡng</option>
                            <option value="dat_phan_lo_du_an" {{ request('property_type')=='dat_phan_lo_du_an'
                                ? 'selected' : '' }}>Đất phân lô dự án</option>
                            <option value="chung_cu" {{ request('property_type')=='chung_cu' ? 'selected' : '' }}>Chung
                                cư</option>
                            <option value="dang_su_dung_kinh_doanh" {{
                                request('property_type')=='dang_su_dung_kinh_doanh' ? 'selected' : '' }}>Đang sử dụng
                                kinh doanh</option>
                            <option value="khac" {{ request('property_type')=='khac' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nhà trên đất</label>
                        <select name="house_on_land"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="co" {{ request('house_on_land')=='co' ? 'selected' : '' }}>Có</option>
                            <option value="khong" {{ request('house_on_land')=='khong' ? 'selected' : '' }}>Không
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Diện tích sàn</label>
                        <select name="floor_area_range"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="0-50" {{ request('floor_area_range')=='0-50' ? 'selected' : '' }}>Dưới 50 m²
                            </option>
                            <option value="50-100" {{ request('floor_area_range')=='50-100' ? 'selected' : '' }}>50 -
                                100 m²</option>
                            <option value="100-200" {{ request('floor_area_range')=='100-200' ? 'selected' : '' }}>100 -
                                200 m²</option>
                            <option value="200-500" {{ request('floor_area_range')=='200-500' ? 'selected' : '' }}>200 -
                                500 m²</option>
                            <option value="500+" {{ request('floor_area_range')=='500+' ? 'selected' : '' }}>Trên 500 m²
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Tài chính</label>
                        <select name="price_range"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="0-500" {{ request('price_range')=='0-500' ? 'selected' : '' }}>Dưới 500 triệu
                            </option>
                            <option value="500-1000" {{ request('price_range')=='500-1000' ? 'selected' : '' }}>500
                                triệu - 1 tỷ</option>
                            <option value="1000-2000" {{ request('price_range')=='1000-2000' ? 'selected' : '' }}>1 - 2
                                tỷ</option>
                            <option value="2000-5000" {{ request('price_range')=='2000-5000' ? 'selected' : '' }}>2 - 5
                                tỷ</option>
                            <option value="5000+" {{ request('price_range')=='5000+' ? 'selected' : '' }}>Trên 5 tỷ
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Thổ cư</label>
                        <select name="tho_cu"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="full" {{ request('tho_cu')=='full' ? 'selected' : '' }}>100% thổ cư</option>
                            <option value="partial" {{ request('tho_cu')=='partial' ? 'selected' : '' }}>Một phần thổ cư
                            </option>
                            <option value="none" {{ request('tho_cu')=='none' ? 'selected' : '' }}>Chưa có thổ cư
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Đường thể hiện</label>
                        <select name="road_type"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="co" {{ request('road_type')=='co' ? 'selected' : '' }}>Có</option>
                            <option value="khong" {{ request('road_type')=='khong' ? 'selected' : '' }}>Không</option>
                        </select>
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Chiều dài mặt tiền</label>
                        <select name="frontage"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="duoi_5m" {{ request('frontage')=='duoi_5m' ? 'selected' : '' }}>Dưới 5 mét
                            </option>
                            <option value="5_10m" {{ request('frontage')=='5_10m' ? 'selected' : '' }}>Từ 5 - 10 mét
                            </option>
                            <option value="10_20m" {{ request('frontage')=='10_20m' ? 'selected' : '' }}>Từ 10 - 20 mét
                            </option>
                            <option value="tren_20m" {{ request('frontage')=='tren_20m' ? 'selected' : '' }}>Trên 20 mét
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Diện tích đất</label>
                        <select name="area_range"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="duoi_100" {{ request('area_range')=='duoi_100' ? 'selected' : '' }}>Dưới 100
                                m²</option>
                            <option value="100_200" {{ request('area_range')=='100_200' ? 'selected' : '' }}>Từ 100 -
                                200 m²</option>
                            <option value="200_500" {{ request('area_range')=='200_500' ? 'selected' : '' }}>Từ 200 -
                                500 m²</option>
                            <option value="500_1000" {{ request('area_range')=='500_1000' ? 'selected' : '' }}>Từ 500 -
                                1000 m²</option>
                            <option value="tren_1000" {{ request('area_range')=='tren_1000' ? 'selected' : '' }}>Trên
                                1000 m²</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Số tờ</label>
                        <input type="text" name="so_to" value="{{ request('so_to') }}" placeholder="Số tờ"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200 placeholder-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Số thửa</label>
                        <input type="text" name="so_thua" value="{{ request('so_thua') }}" placeholder="Số Thửa"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200 placeholder-gray-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Hướng đất</label>
                        <select name="direction"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Tất cả --</option>
                            <option value="dong" {{ request('direction')=='dong' ? 'selected' : '' }}>Đông</option>
                            <option value="tay" {{ request('direction')=='tay' ? 'selected' : '' }}>Tây</option>
                            <option value="nam" {{ request('direction')=='nam' ? 'selected' : '' }}>Nam</option>
                            <option value="bac" {{ request('direction')=='bac' ? 'selected' : '' }}>Bắc</option>
                            <option value="dong_nam" {{ request('direction')=='dong_nam' ? 'selected' : '' }}>Đông Nam
                            </option>
                            <option value="dong_bac" {{ request('direction')=='dong_bac' ? 'selected' : '' }}>Đông Bắc
                            </option>
                            <option value="tay_nam" {{ request('direction')=='tay_nam' ? 'selected' : '' }}>Tây Nam
                            </option>
                            <option value="tay_bac" {{ request('direction')=='tay_bac' ? 'selected' : '' }}>Tây Bắc
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Cách sắp xếp</label>
                        <select name="sort"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                            <option value="">-- Mặc định --</option>
                            <option value="newest" {{ request('sort')=='newest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }}>Cũ nhất</option>
                            <option value="price_asc" {{ request('sort')=='price_asc' ? 'selected' : '' }}>Giá thấp đến
                                cao</option>
                            <option value="price_desc" {{ request('sort')=='price_desc' ? 'selected' : '' }}>Giá cao đến
                                thấp</option>
                            <option value="area_asc" {{ request('sort')=='area_asc' ? 'selected' : '' }}>Diện tích nhỏ
                                đến lớn</option>
                            <option value="area_desc" {{ request('sort')=='area_desc' ? 'selected' : '' }}>Diện tích lớn
                                đến nhỏ</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Tìm nhanh</label>
                        <input type="text" name="phone" value="{{ request('phone') }}" placeholder="STT hoặc SĐT"
                            class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200 placeholder-gray-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <a href="{{ route('search.results') }}"
                            class="flex-1 px-4 py-2 border border-green-500 text-green-400 font-semibold rounded-lg hover:bg-green-500/10 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Làm mới
                        </a>
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

<!-- Province/Ward JS -->
<script>
    let provincesData = [];
    (async function loadProvinces() {
        try {
            const res = await fetch('/api/public/provinces');
            const json = await res.json();
            provincesData = json.data || [];
            const select = document.getElementById('provinceSelect');
            const currentProvince = '{{ request('province') }}';
            provincesData.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.name;
                opt.textContent = p.name;
                if (p.name === currentProvince) opt.selected = true;
                select.appendChild(opt);
            });
            if (currentProvince) updateWards();
        } catch (e) { console.error('Failed to load provinces:', e); }
    })();

    function updateWards() {
        const name = document.getElementById('provinceSelect').value;
        const wardSelect = document.getElementById('wardSelect');
        wardSelect.innerHTML = '<option value="">-- Tất cả --</option>';
        if (!name) { wardSelect.innerHTML = '<option value="">-- Chọn tỉnh trước --</option>'; return; }
        const province = provincesData.find(p => p.name === name);
        const currentWard = '{{ request('district') }}';
        if (province && province.wards) {
            province.wards.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.name;
                opt.textContent = w.name;
                if (w.name === currentWard) opt.selected = true;
                wardSelect.appendChild(opt);
            });
        }
    }
</script>

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
    document.getElementById('toggleFilters')?.addEventListener('click', toggleFilterPanel);
    document.getElementById('toggleFiltersMobile')?.addEventListener('click', toggleFilterPanel);

    // Strip empty params from URL
    document.getElementById('searchForm').addEventListener('submit', function (e) {
        const form = this;
        let hasValue = false;
        form.querySelectorAll('input[type="text"], select').forEach(el => {
            if (el.value.trim()) { hasValue = true; } else { el.disabled = true; }
        });
        if (!hasValue) { e.preventDefault(); window.location.href = '/'; return; }
        setTimeout(() => { form.querySelectorAll('[disabled]').forEach(el => el.disabled = false); }, 100);
    });
</script>

<!-- Map Section -->
<section class="sm:py-8 py-4 bg-navy-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div id="property-map" class="w-full h-[400px] rounded-lg shadow-lg overflow-hidden border border-navy-600">
        </div>
    </div>
</section>

<!-- Results Section -->
<section class="sm:py-8 py-4" id="results-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-100">Kết quả tìm kiếm</h1>
                @if($searchQuery)
                <p class="text-gray-400 text-sm mt-1">
                    Từ khóa: "{{ $searchQuery }}"
                    @if($meta) · {{ $meta['total'] ?? 0 }} kết quả @endif
                </p>
                @elseif($meta)
                <p class="text-gray-400 text-sm mt-1">{{ $meta['total'] ?? 0 }} kết quả</p>
                @endif
            </div>
            <div class="flex items-end gap-1">
                <button onclick="setView('grid')" id="btn-grid" class="p-2 rounded-lg border border-navy-600 transition"
                    title="Dạng lưới">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <button onclick="setView('list')" id="btn-list" class="p-2 rounded-lg border border-navy-600 transition"
                    title="Dạng danh sách">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Grid View -->
        <div id="resultsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($consignments as $item)
            @include('components.consignment-card', ['consignment' => $item])
            @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-gray-400 text-lg">Không tìm thấy kết quả nào</p>
                <a href="/" class="mt-4 inline-block text-green-400 hover:underline">
                    ← Về trang chủ
                </a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @php
        $lastPage = ($meta['last_page'] ?? $meta['total_pages'] ?? 1);
        $currentPage = ($meta['current_page'] ?? 1);
        @endphp
        @if($meta && $lastPage > 1)
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center space-x-2">
                @if($currentPage > 1)
                <a href="{{ route('search.results', array_merge(request()->query(), ['page' => $currentPage - 1])) }}"
                    class="px-3 py-2 rounded-lg bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                @endif
                @for($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++) <a
                    href="{{ route('search.results', array_merge(request()->query(), ['page' => $i])) }}"
                    class="px-4 py-2 rounded-lg {{ $currentPage == $i ? 'bg-green-500 text-white shadow-lg shadow-green-500/25' : 'bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600' }} transition">
                    {{ $i }}
                    </a>
                    @endfor
                    @if($currentPage < $lastPage) <a
                        href="{{ route('search.results', array_merge(request()->query(), ['page' => $currentPage + 1])) }}"
                        class="px-3 py-2 rounded-lg bg-navy-700 text-gray-300 hover:bg-navy-600 border border-navy-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        </a>
                        @endif
            </nav>
        </div>
        @endif
    </div>
</section>

<!-- View Toggle Script -->
<script>
    function setView(mode) {
        const grid = document.getElementById('resultsGrid');
        const btnGrid = document.getElementById('btn-grid');
        const btnList = document.getElementById('btn-list');
        if (mode === 'list') {
            grid.classList.remove('grid-cols-1', 'md:grid-cols-2');
            grid.classList.add('grid-cols-1');
            btnGrid.classList.remove('bg-green-500/20', 'text-green-400', 'border-green-500');
            btnGrid.classList.add('text-gray-400');
            btnList.classList.add('bg-green-500/20', 'text-green-400', 'border-green-500');
            btnList.classList.remove('text-gray-400');
        } else {
            grid.classList.add('grid-cols-1', 'md:grid-cols-2');
            btnList.classList.remove('bg-green-500/20', 'text-green-400', 'border-green-500');
            btnList.classList.add('text-gray-400');
            btnGrid.classList.add('bg-green-500/20', 'text-green-400', 'border-green-500');
            btnGrid.classList.remove('text-gray-400');
        }
        localStorage.setItem('viewMode', mode);
    }
    document.addEventListener('DOMContentLoaded', () => setView(localStorage.getItem('viewMode') || 'grid'));
</script>

<!-- Map Scripts -->
@php
$mapData = collect($consignments)->map(function ($item) {
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
'area_dimensions' => $item['area_dimensions'] ?? '',
'residential_area' => $item['residential_area'] ?? '',
'land_directions' => $item['land_directions'] ?? [],
'road' => $item['road'] ?? '',
'frontage_actual' => $item['frontage_actual'] ?? '',
'statusText' => (function ($c) {
$createdAt = $c['created_at'] ?? null;
$status = 'Chưa bán';
if ($createdAt) {
try {
$createdDate = \Carbon\Carbon::parse($createdAt)->shiftTimezone('Asia/Ho_Chi_Minh');
if ($createdDate->diffInDays(now('Asia/Ho_Chi_Minh')) < 5) { $status=$createdDate->locale('vi')->diffForHumans();
    }
    } catch (\Exception $e) { }
    }
    return $status;
    })($item),
    'seo_url' => $item['seo_url'] ?? '',
    'order_number' => $item['order_number'] ?? '',
    'image' => (function ($item) {
    $imgs = is_string($item['images'] ?? '') ? json_decode($item['images'], true) : ($item['images'] ?? []);
    return $imgs[0] ?? $item['featured_image'] ?? '/images/placeholder.jpg';
    })($item),
    'lat' => !empty($item['latitude'] ?? null) ? (float) $item['latitude'] : null,
    'lng' => !empty($item['longitude'] ?? null) ? (float) $item['longitude'] : null,
    ];
    });
    @endphp
    <!-- Leaflet CSS + JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        /* Leaflet popup overrides */
        .leaflet-popup-content-wrapper {
            padding: 0 !important;
            border-radius: 12px !important;
            overflow: hidden;
        }

        .leaflet-popup-content {
            margin: 0 !important;
            width: auto !important;
        }

        .leaflet-popup-tip {
            border-top-color: white !important;
        }

        /* Cluster overrides */
        .marker-cluster-small,
        .marker-cluster-medium,
        .marker-cluster-large {
            background: rgba(34, 197, 94, 0.3) !important;
        }

        .marker-cluster-small div,
        .marker-cluster-medium div,
        .marker-cluster-large div {
            background: #22c55e !important;
            color: white !important;
            font-weight: bold;
        }

        #property-map {
            z-index: 1;
        }

        .map-gesture-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .map-gesture-overlay.visible {
            opacity: 1;
        }

        .map-gesture-overlay span {
            color: white;
            font-size: 15px;
            font-weight: 600;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 8px;
        }
    </style>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        const properties = @json($mapData);
        let map;
        let markers = [];
        let markerClusterGroup;

        // Custom green circle icon
        const greenIcon = L.divIcon({
            className: '',
            html: '<div style="width:20px;height:20px;background:#22c55e;border:2px solid #16a34a;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10],
            popupAnchor: [0, -12]
        });

        function formatPrice(p) {
            if (!p) return 'Liên hệ';
            if (p >= 1000000000) { const v = p / 1000000000; return (v % 1 === 0 ? v.toFixed(0) : parseFloat(v.toFixed(1))) + ' tỷ'; }
            if (p >= 1000000) { const v = p / 1000000; return (v % 1 === 0 ? v.toFixed(0) : parseFloat(v.toFixed(0))) + ' triệu'; }
            return new Intl.NumberFormat('vi-VN').format(p) + ' đ';
        }

        function initMap() {
            map = L.map('property-map', {
                center: [12.5, 108.5],
                zoom: 5,
                scrollWheelZoom: false,
                dragging: !L.Browser.mobile,
                tap: false
            });

            // OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(map);

            // Google Maps-style gesture handling
            setupGestureHandling();

            // Marker cluster group
            markerClusterGroup = L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false
            });
            map.addLayer(markerClusterGroup);

            properties.filter(p => p.lat && p.lng).forEach(p => addMarker(p));

            // Fit bounds
            if (markers.length > 0) {
                const group = L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
                if (markers.length === 1) map.setZoom(14);
            }
        }

        function mapDirection(d) {
            const m = { 'dong': 'Đông', 'tay': 'Tây', 'nam': 'Nam', 'bac': 'Bắc', 'dong-nam': 'Đông Nam', 'dong-bac': 'Đông Bắc', 'tay-nam': 'Tây Nam', 'tay-bac': 'Tây Bắc' };
            return m[d] || d;
        }

        function addMarker(property) {
            const marker = L.marker([property.lat, property.lng], { icon: greenIcon, title: property.title });

            // Parse directions
            let popupDirs = property.land_directions;
            if (typeof popupDirs === 'string') { try { popupDirs = JSON.parse(popupDirs); } catch (e) { popupDirs = []; } }
            const popupDirText = Array.isArray(popupDirs) && popupDirs.length > 0 ? popupDirs.map(mapDirection).join(', ') : '';

            // Build detail rows
            let popupDetails = '';
            if (property.address) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Địa chỉ:</span> ${property.address}</p>`;
            if (property.area_dimensions) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Diện tích:</span> ${property.area_dimensions}</p>`;
            if (property.residential_area) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Thổ cư:</span> ${parseFloat(property.residential_area)} m²</p>`;
            if (popupDirText) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Hướng:</span> ${popupDirText}</p>`;
            if (property.road) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Loại đường:</span> ${property.road}</p>`;
            if (property.frontage_actual && property.frontage_actual !== '0' && property.frontage_actual !== '0.00') popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Mặt tiền:</span> ${parseFloat(property.frontage_actual)} m</p>`;
            if (property.statusText) popupDetails += `<p style="color:#94a3b8;font-size:12px;margin:0 0 4px;"><span style="color:#6b7280;">Tình trạng:</span> ${property.statusText}</p>`;

            const popupContent = `
                <div style="width:350px;max-width:90vw;font-family:Arial,sans-serif;border-radius:12px;overflow:hidden;">
                    <img src="${property.image}" alt="${property.title}"
                        style="width:100%;height:160px;object-fit:cover;"
                        onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22350%22 height=%22160%22%3E%3Crect fill=%22%23334155%22 width=%22350%22 height=%22160%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%2394a3b8%22 font-size=%2214%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                    <div style="padding:12px;">
                        ${property.order_number ? `<p style="color:#6b7280;font-size:11px;margin:0 0 4px;font-weight:500;">Mã Số: ${property.order_number}</p>` : ''}
                        <p style="font-weight:bold;font-size:14px;margin:0 0 8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-transform:uppercase;">
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

            marker.bindPopup(popupContent, { maxWidth: 380, closeButton: true });
            markerClusterGroup.addLayer(marker);
            markers.push(marker);
        }

        // Google Maps-style gesture handling
        function setupGestureHandling() {
            const container = document.getElementById('property-map');
            const overlay = document.createElement('div');
            overlay.className = 'map-gesture-overlay';
            const isMobile = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
            overlay.innerHTML = isMobile
                ? '<span>Sử dụng hai ngón tay để di chuyển bản đồ</span>'
                : '<span>Sử dụng Ctrl + cuộn chuột để phóng to</span>';
            container.style.position = 'relative';
            container.appendChild(overlay);
            let overlayTimeout;
            function showOverlay() {
                overlay.classList.add('visible');
                clearTimeout(overlayTimeout);
                overlayTimeout = setTimeout(() => overlay.classList.remove('visible'), 1500);
            }
            container.addEventListener('wheel', function (e) {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    map.scrollWheelZoom.enable();
                    clearTimeout(overlayTimeout);
                    overlay.classList.remove('visible');
                    clearTimeout(container._wheelTimer);
                    container._wheelTimer = setTimeout(() => map.scrollWheelZoom.disable(), 400);
                } else {
                    showOverlay();
                }
            }, { passive: false });
            if (isMobile) {
                let touchCount = 0;
                container.addEventListener('touchstart', function (e) {
                    touchCount = e.touches.length;
                    if (touchCount >= 2) { map.dragging.enable(); overlay.classList.remove('visible'); }
                    else { map.dragging.disable(); }
                }, { passive: true });
                container.addEventListener('touchmove', function (e) {
                    if (e.touches.length < 2 && touchCount < 2) showOverlay();
                }, { passive: true });
                container.addEventListener('touchend', function (e) {
                    if (e.touches.length === 0) map.dragging.disable();
                }, { passive: true });
            }
        }

        // Initialize map when DOM is ready
        document.addEventListener('DOMContentLoaded', initMap);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-scroll to results grid when there are search parameters
            if (window.location.search) {
                setTimeout(() => {
                    const resultsGrid = document.getElementById('resultsGrid');
                    if (resultsGrid) {
                        // Offset for sticky header
                        const y = resultsGrid.getBoundingClientRect().top + window.scrollY - 168;
                        window.scrollTo({ top: y, behavior: 'smooth' });
                    }
                }, 100);
            }
        });
    </script>
    @endsection