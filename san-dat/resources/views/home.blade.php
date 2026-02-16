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
                                <option value="ho-chi-minh">TP Hồ Chí Minh</option>
                                <option value="dong-nai">Đồng Nai</option>
                                <option value="tay-ninh">Tây Ninh</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Xã / Phường / Đặc khu</label>
                            <select name="district" id="wardSelect"
                                class="w-full px-3 py-2 bg-navy-700 border border-navy-600 rounded-lg focus:border-green-500 focus:outline-none text-gray-200">
                                <option value="">-- Chọn tỉnh trước --</option>
                            </select>

                            <script>
                                // Dữ liệu sau sáp nhập hành chính 01/07/2025
                                // Bỏ cấp huyện — chính quyền 2 cấp: tỉnh → xã/phường/đặc khu
                                const wardsByProvince = {
                                    'ho-chi-minh': [
                                        // === Khu vực TP HCM cũ (102 đơn vị: 78 phường, 24 xã) ===
                                        // Quận 1 → 4 phường
                                        'P. Sài Gòn', 'P. Tân Định', 'P. Bến Thành', 'P. Cầu Ông Lãnh',
                                        // Quận 3 → 3 phường
                                        'P. Bàn Cờ', 'P. Xuân Hòa', 'P. Nhiêu Lộc',
                                        // Quận 4 → 3 phường
                                        'P. Vĩnh Hội', 'P. Khánh Hội', 'P. Xóm Chiếu',
                                        // Quận 5 → 3 phường
                                        'P. Chợ Quán', 'P. An Đông', 'P. Chợ Lớn',
                                        // Quận 6 → 4 phường
                                        'P. Bình Tiên', 'P. Bình Tây', 'P. Bình Phú', 'P. Phú Lâm',
                                        // Quận 7 → 4 phường
                                        'P. Tân Hưng', 'P. Tân Thuận', 'P. Tân Mỹ', 'P. Phú Thuận',
                                        // Quận 8 → 3 phường
                                        'P. Chánh Hưng', 'P. Bình Đông', 'P. Phú Định',
                                        // Quận 10 → 3 phường
                                        'P. Vườn Lài', 'P. Diên Hồng', 'P. Hòa Hưng',
                                        // Quận 11 → 4 phường
                                        'P. Bình Thới', 'P. Phú Thọ', 'P. Hòa Bình', 'P. Minh Phụng',
                                        // Quận 12 → 5 phường
                                        'P. Đông Hưng Thuận', 'P. Trung Mỹ Tây', 'P. Tân Thới Hiệp', 'P. Thới An', 'P. An Phú Đông',
                                        // Quận Bình Tân → 5 phường
                                        'P. Bình Trị Đông', 'P. Tân Tạo', 'P. An Lạc', 'P. Bình Hưng Hòa', 'P. Tên Lửa',
                                        // Quận Bình Thạnh → 4 phường
                                        'P. Bình Quới', 'P. Bạch Đằng', 'P. Hàng Xanh', 'P. Thanh Đa',
                                        // Quận Gò Vấp → 4 phường
                                        'P. Quang Trung', 'P. Hạnh Thông Tây', 'P. An Hội', 'P. Thống Nhất',
                                        // Quận Phú Nhuận → 3 phường
                                        'P. Phú Nhuận', 'P. Phan Xích Long', 'P. Tân Sơn Nhất',
                                        // Quận Tân Bình → 4 phường
                                        'P. Tân Bình', 'P. Gia Định', 'P. Bảy Hiền', 'P. Tân Sơn',
                                        // Quận Tân Phú → 4 phường
                                        'P. Tân Phú', 'P. Tây Thạnh', 'P. Hiệp Tân', 'P. Tân Quý',
                                        // TP Thủ Đức → 12 phường
                                        'P. Hiệp Bình', 'P. Thủ Đức', 'P. Tam Bình', 'P. Linh Trung',
                                        'P. Thủ Thiêm', 'P. Cát Lái', 'P. An Khánh', 'P. Thạnh Mỹ Lợi',
                                        'P. Trường Thạnh', 'P. Phước Long', 'P. Tăng Nhơn Phú', 'P. Long Trường',
                                        // Huyện Bình Chánh → 7 xã
                                        'X. Vĩnh Lộc', 'X. Tân Kiên', 'X. Bình Lợi', 'X. Lê Minh Xuân',
                                        'X. Phong Phú', 'X. Đa Phước', 'X. Quy Đức',
                                        // Huyện Cần Giờ → 5 xã + 1 đặc khu
                                        'X. Bình Khánh', 'X. An Thới Đông', 'X. Tam Thôn Hiệp', 'X. Lý Nhơn', 'X. Long Hòa',
                                        'ĐK. Thạnh An',
                                        // Huyện Củ Chi → 7 xã
                                        'X. Tân An Hội', 'X. Phước Vĩnh An', 'X. Trung An', 'X. Tân Thạnh Đông',
                                        'X. Phú Hòa Đông', 'X. Hòa Phú', 'X. Nhuận Đức',
                                        // Huyện Hóc Môn → 3 xã
                                        'X. Xuân Thới Thượng', 'X. Tân Hiệp', 'X. Đông Thạnh',
                                        // Huyện Nhà Bè → 2 xã
                                        'X. Phước Kiển', 'X. Hiệp Phước',
                                        // === Khu vực Bình Dương cũ (36 đơn vị) ===
                                        'P. Thủ Dầu Một', 'P. Phú Hòa', 'P. Chánh Nghĩa', 'P. Hiệp Thành (BD)',
                                        'P. Dĩ An', 'P. Đông Hòa', 'P. Tân Đông Hiệp',
                                        'P. Thuận An', 'P. Bình Hòa', 'P. An Phú (BD)',
                                        'P. Uyên Hưng', 'P. Tân Phước Khánh', 'P. Thái Hòa',
                                        'P. Mỹ Phước', 'P. Thới Hòa',
                                        'X. Lai Uyên', 'X. Tân Hưng (BD)', 'X. Long Nguyên',
                                        'X. Tân Định (BD)', 'X. Tân Mỹ', 'X. Đất Cuốc',
                                        'X. Minh Hòa', 'X. Định Thành', 'X. Long Hòa (BD)',
                                        'X. An Bình (BD)', 'X. Tân Hiệp (BD)', 'X. Phước Sang',
                                        'X. An Lập', 'X. Thanh Tuyền', 'X. Minh Tân',
                                        'X. Phú An', 'X. Vĩnh Hòa', 'X. An Long',
                                        'X. An Linh', 'X. Tân Lập (BD)', 'X. Phước Hòa (BD)',
                                        // === Khu vực Bà Rịa - Vũng Tàu cũ (30 đơn vị) ===
                                        'P. Vũng Tàu', 'P. Thắng Nhất', 'P. Rạch Dừa', 'P. Nguyễn An Ninh',
                                        'P. Long Toàn', 'P. Phước Hiệp', 'P. Kim Dinh',
                                        'P. Phú Mỹ', 'P. Mỹ Xuân', 'P. Tân Phước (BRVT)',
                                        'X. Long Sơn', 'X. Hòa Hiệp', 'X. Bình Châu',
                                        'X. Châu Pha', 'X. Sông Xoài', 'X. Hắc Dịch',
                                        'X. Ngãi Giao', 'X. Suối Nghệ', 'X. Bình Ba',
                                        'X. Láng Lớn', 'X. Quảng Thành', 'X. Kim Long',
                                        'X. Phước Tỉnh', 'X. Long Hải', 'X. Phước Hải',
                                        'X. Đất Đỏ', 'X. Lộc An (BRVT)', 'X. Phước Bửu',
                                        'X. Bông Trang', 'X. Bưng Riềng'
                                    ],
                                    'dong-nai': [
                                        // === Tỉnh Đồng Nai mới (Đồng Nai + Bình Phước) — 95 đơn vị ===
                                        // Khu vực Đồng Nai cũ
                                        // TP Biên Hòa → 9 phường
                                        'P. Trấn Biên', 'P. Tam Hiệp', 'P. Tân Phong (BH)', 'P. Bửu Hòa', 'P. Long Bình',
                                        'P. Tam Phước', 'P. Phước Tân', 'P. An Hòa (BH)', 'P. Hiệp Hòa',
                                        // TP Long Khánh → 4 phường
                                        'P. Xuân An', 'P. Xuân Lập', 'P. Xuân Bình', 'P. Xuân Trung',
                                        // Huyện Long Thành
                                        'X. Long Thành', 'X. An Phước', 'X. Phước Thái', 'X. Tam An',
                                        // Huyện Nhơn Trạch
                                        'X. Phú Hội', 'X. Phước Thiền', 'X. Long Tân', 'X. Hiệp Phước (ĐN)',
                                        // Huyện Trảng Bom
                                        'X. Trảng Bom', 'X. Bắc Sơn', 'X. Hố Nai 3', 'X. Sông Thao',
                                        // Huyện Thống Nhất
                                        'X. Dầu Giây', 'X. Xuân Thiện', 'X. Bàu Hàm 2',
                                        // Huyện Vĩnh Cửu
                                        'X. Vĩnh An', 'X. Thiện Tân', 'X. Phú Lý', 'X. Mã Đà',
                                        // Huyện Xuân Lộc
                                        'X. Xuân Hưng', 'X. Xuân Tâm', 'X. Suối Cát', 'X. Xuân Hòa (XL)',
                                        // Huyện Định Quán
                                        'X. Định Quán', 'X. Phú Ngọc', 'X. Thanh Sơn', 'X. La Ngà',
                                        // Huyện Tân Phú
                                        'X. Tân Phú (ĐN)', 'X. Phú Sơn', 'X. Đak Lua', 'X. Nam Cát Tiên',
                                        // Huyện Cẩm Mỹ
                                        'X. Long Giao', 'X. Xuân Quế', 'X. Sông Nhạn', 'X. Xuân Đông',
                                        // Khu vực Bình Phước cũ
                                        // TP Đồng Xoài → 5 phường
                                        'P. Tân Phú (BP)', 'P. Tân Đồng', 'P. Tân Xuân', 'P. Tân Thiện', 'P. Tiến Thành',
                                        // TX Phước Long
                                        'X. Phước Bình', 'X. Long Giang', 'X. Bình Tân (BP)',
                                        // TX Bình Long
                                        'X. An Khương', 'X. Thanh Phú (BP)', 'X. Thanh Lương',
                                        // TX Chơn Thành
                                        'X. Minh Lập', 'X. Nha Bích', 'X. Thành Tâm',
                                        // Huyện Bù Đăng
                                        'X. Đức Liễu', 'X. Nghĩa Trung', 'X. Đồng Nai (BĐ)', 'X. Thống Nhất (BĐ)',
                                        // Huyện Bù Gia Mập
                                        'X. Bù Gia Mập', 'X. Đăk Ơ', 'X. Phú Nghĩa', 'X. Đa Kia',
                                        // Huyện Bù Đốp
                                        'X. Thanh Hòa (BĐ)', 'X. Phước Thiện', 'X. Tân Tiến (BĐ)',
                                        // Huyện Đồng Phú
                                        'X. Tân Lập (ĐP)', 'X. Tân Hòa', 'X. Đồng Tiến', 'X. Tân Phước (ĐP)',
                                        // Huyện Hớn Quản
                                        'X. Tân Khai', 'X. Thanh An', 'X. An Phú (HQ)', 'X. Minh Đức',
                                        // Huyện Lộc Ninh
                                        'X. Lộc Ninh', 'X. Lộc Hòa', 'X. Lộc Thạnh', 'X. Lộc Thiện',
                                        // Huyện Phú Riềng
                                        'X. Phú Riềng', 'X. Long Tân (PR)', 'X. Bù Nho', 'X. Long Bình (PR)'
                                    ],
                                    'tay-ninh': [
                                        // === Tỉnh Tây Ninh mới (Tây Ninh + Long An) — 96 đơn vị ===
                                        // Khu vực Tây Ninh cũ (36 đơn vị: 10 phường, 26 xã)
                                        // TP Tây Ninh → 5 phường
                                        'P. Ninh Sơn', 'P. Ninh Thạnh', 'P. Hiệp Ninh', 'P. Long Hoa (TN)', 'P. Thạnh Tân',
                                        // TX Trảng Bàng → 3 phường
                                        'P. Trảng Bàng', 'P. An Tịnh', 'P. Gia Lộc',
                                        // TX Hòa Thành → 2 phường
                                        'P. Long Thành Bắc', 'P. Trường Hòa',
                                        // Huyện Gò Dầu
                                        'X. Thanh Phước (GD)', 'X. Hiệp Thạnh (GD)', 'X. Phước Đông', 'X. Bàu Đồn',
                                        // Huyện Bến Cầu
                                        'X. Long Chữ', 'X. Long Phước (BC)', 'X. Tiên Thuận',
                                        // Huyện Dương Minh Châu
                                        'X. Suối Đá', 'X. Phước Ninh', 'X. Chà Là', 'X. Bến Củi',
                                        // Huyện Châu Thành (TN)
                                        'X. Thành Long', 'X. Hảo Đước', 'X. Ninh Điền', 'X. Long Vĩnh',
                                        // Huyện Tân Biên
                                        'X. Tân Phong (TB)', 'X. Thạnh Bắc', 'X. Tân Lập (TB)', 'X. Hòa Hiệp (TB)',
                                        // Huyện Tân Châu
                                        'X. Tân Hà', 'X. Suối Ngô', 'X. Tân Đông (TC)', 'X. Tân Hòa (TC)',
                                        // Khu vực Long An cũ (60 đơn vị: 4 phường, 56 xã)
                                        // TP Tân An → 4 phường
                                        'P. Tân An', 'P. Tân Khánh', 'P. Khánh Hậu', 'P. Hướng Thọ Phú',
                                        // Huyện Đức Hòa
                                        'X. Đức Hòa', 'X. Hựu Thạnh', 'X. Hiệp Hòa (ĐH)', 'X. Đức Lập', 'X. Mỹ Hạnh',
                                        // Huyện Bến Lức
                                        'X. Bến Lức', 'X. Long Hiệp', 'X. Phước Lợi', 'X. Thạnh Đức', 'X. Nhựt Chánh',
                                        // Huyện Cần Giuộc
                                        'X. Trường Bình', 'X. Long Thượng', 'X. Phước Lý', 'X. Mỹ Lộc', 'X. Long An (CG)',
                                        // Huyện Cần Đước
                                        'X. Tân Trạch', 'X. Long Sơn (CĐ)', 'X. Long Hựu', 'X. Phước Tuy',
                                        // Huyện Châu Thành (LA)
                                        'X. Tầm Vu', 'X. Thanh Phú Long', 'X. Hòa Phú (LA)', 'X. Dương Xuân Hội',
                                        // Huyện Tân Trụ
                                        'X. Tân Phước Tây', 'X. Lạc Tấn', 'X. Nhựt Ninh',
                                        // Huyện Thủ Thừa
                                        'X. Bình An', 'X. Mỹ Phú', 'X. Long Thạnh',
                                        // Huyện Thạnh Hóa
                                        'X. Thạnh Hóa', 'X. Tân Hiệp (TH)', 'X. Thuận Bình',
                                        // TX Kiến Tường
                                        'X. Tuyên Thạnh', 'X. Bình Hiệp', 'X. Thạnh Trị',
                                        // Huyện Mộc Hóa
                                        'X. Bình Phong Thạnh', 'X. Tân Lập (MH)', 'X. Bình Hòa Tây',
                                        // Huyện Vĩnh Hưng
                                        'X. Vĩnh Bửu', 'X. Thái Bình Trung', 'X. Tuyên Bình',
                                        // Huyện Tân Hưng
                                        'X. Vĩnh Thạnh', 'X. Hưng Hà', 'X. Vĩnh Đại',
                                        // Huyện Tân Thạnh
                                        'X. Tân Thạnh', 'X. Nhơn Hòa Lập', 'X. Tân Ninh',
                                        // Huyện Đức Huệ
                                        'X. Đông Thành', 'X. Mỹ Quý Đông', 'X. Bình Hòa Hưng'
                                    ]
                                };

                                function updateWards() {
                                    const province = document.getElementById('provinceSelect').value;
                                    const wardSelect = document.getElementById('wardSelect');
                                    wardSelect.innerHTML = '<option value="">-- Tất cả --</option>';

                                    if (province && wardsByProvince[province]) {
                                        wardsByProvince[province].forEach(w => {
                                            const opt = document.createElement('option');
                                            opt.value = w;
                                            opt.textContent = w;
                                            wardSelect.appendChild(opt);
                                        });
                                    } else if (!province) {
                                        wardSelect.innerHTML = '<option value="">-- Chọn tỉnh trước --</option>';
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
            const rawImage = item.images?.[0] || item.image || '/images/placeholder.jpg';
            const image = rawImage.replace(/^https?:\/\/[^\/]+/, '');
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
                'image' => preg_replace('#^https?://[^/]+#', '', $item['image'] ?? '/images/placeholder.jpg'),
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