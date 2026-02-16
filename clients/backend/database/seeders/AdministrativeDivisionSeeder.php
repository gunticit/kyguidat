<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Ward;

class AdministrativeDivisionSeeder extends Seeder
{
    public function run(): void
    {
        // Dữ liệu sau sáp nhập hành chính 01/07/2025
        // Chính quyền 2 cấp: tỉnh → xã/phường/đặc khu

        $data = [
            [
                'name' => 'TP Hồ Chí Minh',
                'slug' => 'ho-chi-minh',
                'sort_order' => 1,
                'wards' => [
                    // Khu vực TP HCM cũ — Q1
                    ['name' => 'P. Sài Gòn', 'type' => 'phuong', 'sort_order' => 1],
                    ['name' => 'P. Tân Định', 'type' => 'phuong', 'sort_order' => 2],
                    ['name' => 'P. Bến Thành', 'type' => 'phuong', 'sort_order' => 3],
                    ['name' => 'P. Cầu Ông Lãnh', 'type' => 'phuong', 'sort_order' => 4],
                    // Q3
                    ['name' => 'P. Bàn Cờ', 'type' => 'phuong', 'sort_order' => 5],
                    ['name' => 'P. Xuân Hòa', 'type' => 'phuong', 'sort_order' => 6],
                    ['name' => 'P. Nhiêu Lộc', 'type' => 'phuong', 'sort_order' => 7],
                    // Q4
                    ['name' => 'P. Vĩnh Hội', 'type' => 'phuong', 'sort_order' => 8],
                    ['name' => 'P. Khánh Hội', 'type' => 'phuong', 'sort_order' => 9],
                    ['name' => 'P. Xóm Chiếu', 'type' => 'phuong', 'sort_order' => 10],
                    // Q5
                    ['name' => 'P. Chợ Quán', 'type' => 'phuong', 'sort_order' => 11],
                    ['name' => 'P. An Đông', 'type' => 'phuong', 'sort_order' => 12],
                    ['name' => 'P. Chợ Lớn', 'type' => 'phuong', 'sort_order' => 13],
                    // Q6
                    ['name' => 'P. Bình Tiên', 'type' => 'phuong', 'sort_order' => 14],
                    ['name' => 'P. Bình Tây', 'type' => 'phuong', 'sort_order' => 15],
                    ['name' => 'P. Bình Phú', 'type' => 'phuong', 'sort_order' => 16],
                    ['name' => 'P. Phú Lâm', 'type' => 'phuong', 'sort_order' => 17],
                    // Q7
                    ['name' => 'P. Tân Hưng', 'type' => 'phuong', 'sort_order' => 18],
                    ['name' => 'P. Tân Thuận', 'type' => 'phuong', 'sort_order' => 19],
                    ['name' => 'P. Tân Mỹ', 'type' => 'phuong', 'sort_order' => 20],
                    ['name' => 'P. Phú Thuận', 'type' => 'phuong', 'sort_order' => 21],
                    // Q8
                    ['name' => 'P. Chánh Hưng', 'type' => 'phuong', 'sort_order' => 22],
                    ['name' => 'P. Bình Đông', 'type' => 'phuong', 'sort_order' => 23],
                    ['name' => 'P. Phú Định', 'type' => 'phuong', 'sort_order' => 24],
                    // Q10
                    ['name' => 'P. Vườn Lài', 'type' => 'phuong', 'sort_order' => 25],
                    ['name' => 'P. Diên Hồng', 'type' => 'phuong', 'sort_order' => 26],
                    ['name' => 'P. Hòa Hưng', 'type' => 'phuong', 'sort_order' => 27],
                    // Q11
                    ['name' => 'P. Bình Thới', 'type' => 'phuong', 'sort_order' => 28],
                    ['name' => 'P. Phú Thọ', 'type' => 'phuong', 'sort_order' => 29],
                    ['name' => 'P. Hòa Bình', 'type' => 'phuong', 'sort_order' => 30],
                    ['name' => 'P. Minh Phụng', 'type' => 'phuong', 'sort_order' => 31],
                    // Q12
                    ['name' => 'P. Đông Hưng Thuận', 'type' => 'phuong', 'sort_order' => 32],
                    ['name' => 'P. Trung Mỹ Tây', 'type' => 'phuong', 'sort_order' => 33],
                    ['name' => 'P. Tân Thới Hiệp', 'type' => 'phuong', 'sort_order' => 34],
                    ['name' => 'P. Thới An', 'type' => 'phuong', 'sort_order' => 35],
                    ['name' => 'P. An Phú Đông', 'type' => 'phuong', 'sort_order' => 36],
                    // Q Bình Tân
                    ['name' => 'P. Bình Trị Đông', 'type' => 'phuong', 'sort_order' => 37],
                    ['name' => 'P. Tân Tạo', 'type' => 'phuong', 'sort_order' => 38],
                    ['name' => 'P. An Lạc', 'type' => 'phuong', 'sort_order' => 39],
                    ['name' => 'P. Bình Hưng Hòa', 'type' => 'phuong', 'sort_order' => 40],
                    ['name' => 'P. Tên Lửa', 'type' => 'phuong', 'sort_order' => 41],
                    // Q Bình Thạnh
                    ['name' => 'P. Bình Quới', 'type' => 'phuong', 'sort_order' => 42],
                    ['name' => 'P. Bạch Đằng', 'type' => 'phuong', 'sort_order' => 43],
                    ['name' => 'P. Hàng Xanh', 'type' => 'phuong', 'sort_order' => 44],
                    ['name' => 'P. Thanh Đa', 'type' => 'phuong', 'sort_order' => 45],
                    // Q Gò Vấp
                    ['name' => 'P. Quang Trung', 'type' => 'phuong', 'sort_order' => 46],
                    ['name' => 'P. Hạnh Thông Tây', 'type' => 'phuong', 'sort_order' => 47],
                    ['name' => 'P. An Hội', 'type' => 'phuong', 'sort_order' => 48],
                    ['name' => 'P. Thống Nhất', 'type' => 'phuong', 'sort_order' => 49],
                    // Q Phú Nhuận
                    ['name' => 'P. Phú Nhuận', 'type' => 'phuong', 'sort_order' => 50],
                    ['name' => 'P. Phan Xích Long', 'type' => 'phuong', 'sort_order' => 51],
                    ['name' => 'P. Tân Sơn Nhất', 'type' => 'phuong', 'sort_order' => 52],
                    // Q Tân Bình
                    ['name' => 'P. Tân Bình', 'type' => 'phuong', 'sort_order' => 53],
                    ['name' => 'P. Gia Định', 'type' => 'phuong', 'sort_order' => 54],
                    ['name' => 'P. Bảy Hiền', 'type' => 'phuong', 'sort_order' => 55],
                    ['name' => 'P. Tân Sơn', 'type' => 'phuong', 'sort_order' => 56],
                    // Q Tân Phú
                    ['name' => 'P. Tân Phú', 'type' => 'phuong', 'sort_order' => 57],
                    ['name' => 'P. Tây Thạnh', 'type' => 'phuong', 'sort_order' => 58],
                    ['name' => 'P. Hiệp Tân', 'type' => 'phuong', 'sort_order' => 59],
                    ['name' => 'P. Tân Quý', 'type' => 'phuong', 'sort_order' => 60],
                    // TP Thủ Đức
                    ['name' => 'P. Hiệp Bình', 'type' => 'phuong', 'sort_order' => 61],
                    ['name' => 'P. Thủ Đức', 'type' => 'phuong', 'sort_order' => 62],
                    ['name' => 'P. Tam Bình', 'type' => 'phuong', 'sort_order' => 63],
                    ['name' => 'P. Linh Trung', 'type' => 'phuong', 'sort_order' => 64],
                    ['name' => 'P. Thủ Thiêm', 'type' => 'phuong', 'sort_order' => 65],
                    ['name' => 'P. Cát Lái', 'type' => 'phuong', 'sort_order' => 66],
                    ['name' => 'P. An Khánh', 'type' => 'phuong', 'sort_order' => 67],
                    ['name' => 'P. Thạnh Mỹ Lợi', 'type' => 'phuong', 'sort_order' => 68],
                    ['name' => 'P. Trường Thạnh', 'type' => 'phuong', 'sort_order' => 69],
                    ['name' => 'P. Phước Long', 'type' => 'phuong', 'sort_order' => 70],
                    ['name' => 'P. Tăng Nhơn Phú', 'type' => 'phuong', 'sort_order' => 71],
                    ['name' => 'P. Long Trường', 'type' => 'phuong', 'sort_order' => 72],
                    // H Bình Chánh → xã
                    ['name' => 'X. Vĩnh Lộc', 'type' => 'xa', 'sort_order' => 73],
                    ['name' => 'X. Tân Kiên', 'type' => 'xa', 'sort_order' => 74],
                    ['name' => 'X. Bình Lợi', 'type' => 'xa', 'sort_order' => 75],
                    ['name' => 'X. Lê Minh Xuân', 'type' => 'xa', 'sort_order' => 76],
                    ['name' => 'X. Phong Phú', 'type' => 'xa', 'sort_order' => 77],
                    ['name' => 'X. Đa Phước', 'type' => 'xa', 'sort_order' => 78],
                    ['name' => 'X. Quy Đức', 'type' => 'xa', 'sort_order' => 79],
                    // H Cần Giờ
                    ['name' => 'X. Bình Khánh', 'type' => 'xa', 'sort_order' => 80],
                    ['name' => 'X. An Thới Đông', 'type' => 'xa', 'sort_order' => 81],
                    ['name' => 'X. Tam Thôn Hiệp', 'type' => 'xa', 'sort_order' => 82],
                    ['name' => 'X. Lý Nhơn', 'type' => 'xa', 'sort_order' => 83],
                    ['name' => 'X. Long Hòa', 'type' => 'xa', 'sort_order' => 84],
                    ['name' => 'ĐK. Thạnh An', 'type' => 'dac_khu', 'sort_order' => 85],
                    // H Củ Chi
                    ['name' => 'X. Tân An Hội', 'type' => 'xa', 'sort_order' => 86],
                    ['name' => 'X. Phước Vĩnh An', 'type' => 'xa', 'sort_order' => 87],
                    ['name' => 'X. Trung An', 'type' => 'xa', 'sort_order' => 88],
                    ['name' => 'X. Tân Thạnh Đông', 'type' => 'xa', 'sort_order' => 89],
                    ['name' => 'X. Phú Hòa Đông', 'type' => 'xa', 'sort_order' => 90],
                    ['name' => 'X. Hòa Phú', 'type' => 'xa', 'sort_order' => 91],
                    ['name' => 'X. Nhuận Đức', 'type' => 'xa', 'sort_order' => 92],
                    // H Hóc Môn
                    ['name' => 'X. Xuân Thới Thượng', 'type' => 'xa', 'sort_order' => 93],
                    ['name' => 'X. Tân Hiệp', 'type' => 'xa', 'sort_order' => 94],
                    ['name' => 'X. Đông Thạnh', 'type' => 'xa', 'sort_order' => 95],
                    // H Nhà Bè
                    ['name' => 'X. Phước Kiển', 'type' => 'xa', 'sort_order' => 96],
                    ['name' => 'X. Hiệp Phước', 'type' => 'xa', 'sort_order' => 97],
                    // KV Bình Dương cũ
                    ['name' => 'P. Thủ Dầu Một', 'type' => 'phuong', 'sort_order' => 100],
                    ['name' => 'P. Phú Hòa', 'type' => 'phuong', 'sort_order' => 101],
                    ['name' => 'P. Chánh Nghĩa', 'type' => 'phuong', 'sort_order' => 102],
                    ['name' => 'P. Hiệp Thành (BD)', 'type' => 'phuong', 'sort_order' => 103],
                    ['name' => 'P. Dĩ An', 'type' => 'phuong', 'sort_order' => 104],
                    ['name' => 'P. Đông Hòa', 'type' => 'phuong', 'sort_order' => 105],
                    ['name' => 'P. Tân Đông Hiệp', 'type' => 'phuong', 'sort_order' => 106],
                    ['name' => 'P. Thuận An', 'type' => 'phuong', 'sort_order' => 107],
                    ['name' => 'P. Bình Hòa', 'type' => 'phuong', 'sort_order' => 108],
                    ['name' => 'P. An Phú (BD)', 'type' => 'phuong', 'sort_order' => 109],
                    ['name' => 'P. Uyên Hưng', 'type' => 'phuong', 'sort_order' => 110],
                    ['name' => 'P. Tân Phước Khánh', 'type' => 'phuong', 'sort_order' => 111],
                    ['name' => 'P. Thái Hòa', 'type' => 'phuong', 'sort_order' => 112],
                    ['name' => 'P. Mỹ Phước', 'type' => 'phuong', 'sort_order' => 113],
                    ['name' => 'P. Thới Hòa', 'type' => 'phuong', 'sort_order' => 114],
                    ['name' => 'X. Lai Uyên', 'type' => 'xa', 'sort_order' => 115],
                    ['name' => 'X. Tân Hưng (BD)', 'type' => 'xa', 'sort_order' => 116],
                    ['name' => 'X. Long Nguyên', 'type' => 'xa', 'sort_order' => 117],
                    ['name' => 'X. Tân Định (BD)', 'type' => 'xa', 'sort_order' => 118],
                    ['name' => 'X. Tân Mỹ', 'type' => 'xa', 'sort_order' => 119],
                    ['name' => 'X. Đất Cuốc', 'type' => 'xa', 'sort_order' => 120],
                    ['name' => 'X. Minh Hòa', 'type' => 'xa', 'sort_order' => 121],
                    ['name' => 'X. Định Thành', 'type' => 'xa', 'sort_order' => 122],
                    ['name' => 'X. Long Hòa (BD)', 'type' => 'xa', 'sort_order' => 123],
                    ['name' => 'X. An Bình (BD)', 'type' => 'xa', 'sort_order' => 124],
                    ['name' => 'X. Tân Hiệp (BD)', 'type' => 'xa', 'sort_order' => 125],
                    ['name' => 'X. Phước Sang', 'type' => 'xa', 'sort_order' => 126],
                    ['name' => 'X. An Lập', 'type' => 'xa', 'sort_order' => 127],
                    ['name' => 'X. Thanh Tuyền', 'type' => 'xa', 'sort_order' => 128],
                    ['name' => 'X. Minh Tân', 'type' => 'xa', 'sort_order' => 129],
                    ['name' => 'X. Phú An', 'type' => 'xa', 'sort_order' => 130],
                    ['name' => 'X. Vĩnh Hòa', 'type' => 'xa', 'sort_order' => 131],
                    ['name' => 'X. An Long', 'type' => 'xa', 'sort_order' => 132],
                    ['name' => 'X. An Linh', 'type' => 'xa', 'sort_order' => 133],
                    ['name' => 'X. Tân Lập (BD)', 'type' => 'xa', 'sort_order' => 134],
                    ['name' => 'X. Phước Hòa (BD)', 'type' => 'xa', 'sort_order' => 135],
                    // KV Bà Rịa - Vũng Tàu cũ
                    ['name' => 'P. Vũng Tàu', 'type' => 'phuong', 'sort_order' => 140],
                    ['name' => 'P. Thắng Nhất', 'type' => 'phuong', 'sort_order' => 141],
                    ['name' => 'P. Rạch Dừa', 'type' => 'phuong', 'sort_order' => 142],
                    ['name' => 'P. Nguyễn An Ninh', 'type' => 'phuong', 'sort_order' => 143],
                    ['name' => 'P. Long Toàn', 'type' => 'phuong', 'sort_order' => 144],
                    ['name' => 'P. Phước Hiệp', 'type' => 'phuong', 'sort_order' => 145],
                    ['name' => 'P. Kim Dinh', 'type' => 'phuong', 'sort_order' => 146],
                    ['name' => 'P. Phú Mỹ', 'type' => 'phuong', 'sort_order' => 147],
                    ['name' => 'P. Mỹ Xuân', 'type' => 'phuong', 'sort_order' => 148],
                    ['name' => 'P. Tân Phước (BRVT)', 'type' => 'phuong', 'sort_order' => 149],
                    ['name' => 'X. Long Sơn', 'type' => 'xa', 'sort_order' => 150],
                    ['name' => 'X. Hòa Hiệp', 'type' => 'xa', 'sort_order' => 151],
                    ['name' => 'X. Bình Châu', 'type' => 'xa', 'sort_order' => 152],
                    ['name' => 'X. Châu Pha', 'type' => 'xa', 'sort_order' => 153],
                    ['name' => 'X. Sông Xoài', 'type' => 'xa', 'sort_order' => 154],
                    ['name' => 'X. Hắc Dịch', 'type' => 'xa', 'sort_order' => 155],
                    ['name' => 'X. Ngãi Giao', 'type' => 'xa', 'sort_order' => 156],
                    ['name' => 'X. Suối Nghệ', 'type' => 'xa', 'sort_order' => 157],
                    ['name' => 'X. Bình Ba', 'type' => 'xa', 'sort_order' => 158],
                    ['name' => 'X. Láng Lớn', 'type' => 'xa', 'sort_order' => 159],
                    ['name' => 'X. Quảng Thành', 'type' => 'xa', 'sort_order' => 160],
                    ['name' => 'X. Kim Long', 'type' => 'xa', 'sort_order' => 161],
                    ['name' => 'X. Phước Tỉnh', 'type' => 'xa', 'sort_order' => 162],
                    ['name' => 'X. Long Hải', 'type' => 'xa', 'sort_order' => 163],
                    ['name' => 'X. Phước Hải', 'type' => 'xa', 'sort_order' => 164],
                    ['name' => 'X. Đất Đỏ', 'type' => 'xa', 'sort_order' => 165],
                    ['name' => 'X. Lộc An (BRVT)', 'type' => 'xa', 'sort_order' => 166],
                    ['name' => 'X. Phước Bửu', 'type' => 'xa', 'sort_order' => 167],
                    ['name' => 'X. Bông Trang', 'type' => 'xa', 'sort_order' => 168],
                    ['name' => 'X. Bưng Riềng', 'type' => 'xa', 'sort_order' => 169],
                ],
            ],
            [
                'name' => 'Đồng Nai',
                'slug' => 'dong-nai',
                'sort_order' => 2,
                'wards' => [
                    // KV Đồng Nai cũ — TP Biên Hòa
                    ['name' => 'P. Trấn Biên', 'type' => 'phuong', 'sort_order' => 1],
                    ['name' => 'P. Tam Hiệp', 'type' => 'phuong', 'sort_order' => 2],
                    ['name' => 'P. Tân Phong (BH)', 'type' => 'phuong', 'sort_order' => 3],
                    ['name' => 'P. Bửu Hòa', 'type' => 'phuong', 'sort_order' => 4],
                    ['name' => 'P. Long Bình', 'type' => 'phuong', 'sort_order' => 5],
                    ['name' => 'P. Tam Phước', 'type' => 'phuong', 'sort_order' => 6],
                    ['name' => 'P. Phước Tân', 'type' => 'phuong', 'sort_order' => 7],
                    ['name' => 'P. An Hòa (BH)', 'type' => 'phuong', 'sort_order' => 8],
                    ['name' => 'P. Hiệp Hòa', 'type' => 'phuong', 'sort_order' => 9],
                    // TP Long Khánh
                    ['name' => 'P. Xuân An', 'type' => 'phuong', 'sort_order' => 10],
                    ['name' => 'P. Xuân Lập', 'type' => 'phuong', 'sort_order' => 11],
                    ['name' => 'P. Xuân Bình', 'type' => 'phuong', 'sort_order' => 12],
                    ['name' => 'P. Xuân Trung', 'type' => 'phuong', 'sort_order' => 13],
                    // H Long Thành
                    ['name' => 'X. Long Thành', 'type' => 'xa', 'sort_order' => 14],
                    ['name' => 'X. An Phước', 'type' => 'xa', 'sort_order' => 15],
                    ['name' => 'X. Phước Thái', 'type' => 'xa', 'sort_order' => 16],
                    ['name' => 'X. Tam An', 'type' => 'xa', 'sort_order' => 17],
                    // H Nhơn Trạch
                    ['name' => 'X. Phú Hội', 'type' => 'xa', 'sort_order' => 18],
                    ['name' => 'X. Phước Thiền', 'type' => 'xa', 'sort_order' => 19],
                    ['name' => 'X. Long Tân', 'type' => 'xa', 'sort_order' => 20],
                    ['name' => 'X. Hiệp Phước (ĐN)', 'type' => 'xa', 'sort_order' => 21],
                    // H Trảng Bom
                    ['name' => 'X. Trảng Bom', 'type' => 'xa', 'sort_order' => 22],
                    ['name' => 'X. Bắc Sơn', 'type' => 'xa', 'sort_order' => 23],
                    ['name' => 'X. Hố Nai 3', 'type' => 'xa', 'sort_order' => 24],
                    ['name' => 'X. Sông Thao', 'type' => 'xa', 'sort_order' => 25],
                    // H Thống Nhất
                    ['name' => 'X. Dầu Giây', 'type' => 'xa', 'sort_order' => 26],
                    ['name' => 'X. Xuân Thiện', 'type' => 'xa', 'sort_order' => 27],
                    ['name' => 'X. Bàu Hàm 2', 'type' => 'xa', 'sort_order' => 28],
                    // H Vĩnh Cửu
                    ['name' => 'X. Vĩnh An', 'type' => 'xa', 'sort_order' => 29],
                    ['name' => 'X. Thiện Tân', 'type' => 'xa', 'sort_order' => 30],
                    ['name' => 'X. Phú Lý', 'type' => 'xa', 'sort_order' => 31],
                    ['name' => 'X. Mã Đà', 'type' => 'xa', 'sort_order' => 32],
                    // H Xuân Lộc
                    ['name' => 'X. Xuân Hưng', 'type' => 'xa', 'sort_order' => 33],
                    ['name' => 'X. Xuân Tâm', 'type' => 'xa', 'sort_order' => 34],
                    ['name' => 'X. Suối Cát', 'type' => 'xa', 'sort_order' => 35],
                    ['name' => 'X. Xuân Hòa (XL)', 'type' => 'xa', 'sort_order' => 36],
                    // H Định Quán
                    ['name' => 'X. Định Quán', 'type' => 'xa', 'sort_order' => 37],
                    ['name' => 'X. Phú Ngọc', 'type' => 'xa', 'sort_order' => 38],
                    ['name' => 'X. Thanh Sơn', 'type' => 'xa', 'sort_order' => 39],
                    ['name' => 'X. La Ngà', 'type' => 'xa', 'sort_order' => 40],
                    // H Tân Phú
                    ['name' => 'X. Tân Phú (ĐN)', 'type' => 'xa', 'sort_order' => 41],
                    ['name' => 'X. Phú Sơn', 'type' => 'xa', 'sort_order' => 42],
                    ['name' => 'X. Đak Lua', 'type' => 'xa', 'sort_order' => 43],
                    ['name' => 'X. Nam Cát Tiên', 'type' => 'xa', 'sort_order' => 44],
                    // H Cẩm Mỹ
                    ['name' => 'X. Long Giao', 'type' => 'xa', 'sort_order' => 45],
                    ['name' => 'X. Xuân Quế', 'type' => 'xa', 'sort_order' => 46],
                    ['name' => 'X. Sông Nhạn', 'type' => 'xa', 'sort_order' => 47],
                    ['name' => 'X. Xuân Đông', 'type' => 'xa', 'sort_order' => 48],
                    // KV Bình Phước cũ — TP Đồng Xoài
                    ['name' => 'P. Tân Phú (BP)', 'type' => 'phuong', 'sort_order' => 50],
                    ['name' => 'P. Tân Đồng', 'type' => 'phuong', 'sort_order' => 51],
                    ['name' => 'P. Tân Xuân', 'type' => 'phuong', 'sort_order' => 52],
                    ['name' => 'P. Tân Thiện', 'type' => 'phuong', 'sort_order' => 53],
                    ['name' => 'P. Tiến Thành', 'type' => 'phuong', 'sort_order' => 54],
                    // TX Phước Long
                    ['name' => 'X. Phước Bình', 'type' => 'xa', 'sort_order' => 55],
                    ['name' => 'X. Long Giang', 'type' => 'xa', 'sort_order' => 56],
                    ['name' => 'X. Bình Tân (BP)', 'type' => 'xa', 'sort_order' => 57],
                    // TX Bình Long
                    ['name' => 'X. An Khương', 'type' => 'xa', 'sort_order' => 58],
                    ['name' => 'X. Thanh Phú (BP)', 'type' => 'xa', 'sort_order' => 59],
                    ['name' => 'X. Thanh Lương', 'type' => 'xa', 'sort_order' => 60],
                    // TX Chơn Thành
                    ['name' => 'X. Minh Lập', 'type' => 'xa', 'sort_order' => 61],
                    ['name' => 'X. Nha Bích', 'type' => 'xa', 'sort_order' => 62],
                    ['name' => 'X. Thành Tâm', 'type' => 'xa', 'sort_order' => 63],
                    // H Bù Đăng
                    ['name' => 'X. Đức Liễu', 'type' => 'xa', 'sort_order' => 64],
                    ['name' => 'X. Nghĩa Trung', 'type' => 'xa', 'sort_order' => 65],
                    ['name' => 'X. Đồng Nai (BĐ)', 'type' => 'xa', 'sort_order' => 66],
                    ['name' => 'X. Thống Nhất (BĐ)', 'type' => 'xa', 'sort_order' => 67],
                    // H Bù Gia Mập
                    ['name' => 'X. Bù Gia Mập', 'type' => 'xa', 'sort_order' => 68],
                    ['name' => 'X. Đăk Ơ', 'type' => 'xa', 'sort_order' => 69],
                    ['name' => 'X. Phú Nghĩa', 'type' => 'xa', 'sort_order' => 70],
                    ['name' => 'X. Đa Kia', 'type' => 'xa', 'sort_order' => 71],
                    // H Bù Đốp
                    ['name' => 'X. Thanh Hòa (BĐ)', 'type' => 'xa', 'sort_order' => 72],
                    ['name' => 'X. Phước Thiện', 'type' => 'xa', 'sort_order' => 73],
                    ['name' => 'X. Tân Tiến (BĐ)', 'type' => 'xa', 'sort_order' => 74],
                    // H Đồng Phú
                    ['name' => 'X. Tân Lập (ĐP)', 'type' => 'xa', 'sort_order' => 75],
                    ['name' => 'X. Tân Hòa', 'type' => 'xa', 'sort_order' => 76],
                    ['name' => 'X. Đồng Tiến', 'type' => 'xa', 'sort_order' => 77],
                    ['name' => 'X. Tân Phước (ĐP)', 'type' => 'xa', 'sort_order' => 78],
                    // H Hớn Quản
                    ['name' => 'X. Tân Khai', 'type' => 'xa', 'sort_order' => 79],
                    ['name' => 'X. Thanh An', 'type' => 'xa', 'sort_order' => 80],
                    ['name' => 'X. An Phú (HQ)', 'type' => 'xa', 'sort_order' => 81],
                    ['name' => 'X. Minh Đức', 'type' => 'xa', 'sort_order' => 82],
                    // H Lộc Ninh
                    ['name' => 'X. Lộc Ninh', 'type' => 'xa', 'sort_order' => 83],
                    ['name' => 'X. Lộc Hòa', 'type' => 'xa', 'sort_order' => 84],
                    ['name' => 'X. Lộc Thạnh', 'type' => 'xa', 'sort_order' => 85],
                    ['name' => 'X. Lộc Thiện', 'type' => 'xa', 'sort_order' => 86],
                    // H Phú Riềng
                    ['name' => 'X. Phú Riềng', 'type' => 'xa', 'sort_order' => 87],
                    ['name' => 'X. Long Tân (PR)', 'type' => 'xa', 'sort_order' => 88],
                    ['name' => 'X. Bù Nho', 'type' => 'xa', 'sort_order' => 89],
                    ['name' => 'X. Long Bình (PR)', 'type' => 'xa', 'sort_order' => 90],
                ],
            ],
            [
                'name' => 'Tây Ninh',
                'slug' => 'tay-ninh',
                'sort_order' => 3,
                'wards' => [
                    // KV Tây Ninh cũ — TP Tây Ninh
                    ['name' => 'P. Ninh Sơn', 'type' => 'phuong', 'sort_order' => 1],
                    ['name' => 'P. Ninh Thạnh', 'type' => 'phuong', 'sort_order' => 2],
                    ['name' => 'P. Hiệp Ninh', 'type' => 'phuong', 'sort_order' => 3],
                    ['name' => 'P. Long Hoa (TN)', 'type' => 'phuong', 'sort_order' => 4],
                    ['name' => 'P. Thạnh Tân', 'type' => 'phuong', 'sort_order' => 5],
                    // TX Trảng Bàng
                    ['name' => 'P. Trảng Bàng', 'type' => 'phuong', 'sort_order' => 6],
                    ['name' => 'P. An Tịnh', 'type' => 'phuong', 'sort_order' => 7],
                    ['name' => 'P. Gia Lộc', 'type' => 'phuong', 'sort_order' => 8],
                    // TX Hòa Thành
                    ['name' => 'P. Long Thành Bắc', 'type' => 'phuong', 'sort_order' => 9],
                    ['name' => 'P. Trường Hòa', 'type' => 'phuong', 'sort_order' => 10],
                    // H Gò Dầu
                    ['name' => 'X. Thanh Phước (GD)', 'type' => 'xa', 'sort_order' => 11],
                    ['name' => 'X. Hiệp Thạnh (GD)', 'type' => 'xa', 'sort_order' => 12],
                    ['name' => 'X. Phước Đông', 'type' => 'xa', 'sort_order' => 13],
                    ['name' => 'X. Bàu Đồn', 'type' => 'xa', 'sort_order' => 14],
                    // H Bến Cầu
                    ['name' => 'X. Long Chữ', 'type' => 'xa', 'sort_order' => 15],
                    ['name' => 'X. Long Phước (BC)', 'type' => 'xa', 'sort_order' => 16],
                    ['name' => 'X. Tiên Thuận', 'type' => 'xa', 'sort_order' => 17],
                    // H Dương Minh Châu
                    ['name' => 'X. Suối Đá', 'type' => 'xa', 'sort_order' => 18],
                    ['name' => 'X. Phước Ninh', 'type' => 'xa', 'sort_order' => 19],
                    ['name' => 'X. Chà Là', 'type' => 'xa', 'sort_order' => 20],
                    ['name' => 'X. Bến Củi', 'type' => 'xa', 'sort_order' => 21],
                    // H Châu Thành (TN)
                    ['name' => 'X. Thành Long', 'type' => 'xa', 'sort_order' => 22],
                    ['name' => 'X. Hảo Đước', 'type' => 'xa', 'sort_order' => 23],
                    ['name' => 'X. Ninh Điền', 'type' => 'xa', 'sort_order' => 24],
                    ['name' => 'X. Long Vĩnh', 'type' => 'xa', 'sort_order' => 25],
                    // H Tân Biên
                    ['name' => 'X. Tân Phong (TB)', 'type' => 'xa', 'sort_order' => 26],
                    ['name' => 'X. Thạnh Bắc', 'type' => 'xa', 'sort_order' => 27],
                    ['name' => 'X. Tân Lập (TB)', 'type' => 'xa', 'sort_order' => 28],
                    ['name' => 'X. Hòa Hiệp (TB)', 'type' => 'xa', 'sort_order' => 29],
                    // H Tân Châu
                    ['name' => 'X. Tân Hà', 'type' => 'xa', 'sort_order' => 30],
                    ['name' => 'X. Suối Ngô', 'type' => 'xa', 'sort_order' => 31],
                    ['name' => 'X. Tân Đông (TC)', 'type' => 'xa', 'sort_order' => 32],
                    ['name' => 'X. Tân Hòa (TC)', 'type' => 'xa', 'sort_order' => 33],
                    // KV Long An cũ — TP Tân An
                    ['name' => 'P. Tân An', 'type' => 'phuong', 'sort_order' => 40],
                    ['name' => 'P. Tân Khánh', 'type' => 'phuong', 'sort_order' => 41],
                    ['name' => 'P. Khánh Hậu', 'type' => 'phuong', 'sort_order' => 42],
                    ['name' => 'P. Hướng Thọ Phú', 'type' => 'phuong', 'sort_order' => 43],
                    // H Đức Hòa
                    ['name' => 'X. Đức Hòa', 'type' => 'xa', 'sort_order' => 44],
                    ['name' => 'X. Hựu Thạnh', 'type' => 'xa', 'sort_order' => 45],
                    ['name' => 'X. Hiệp Hòa (ĐH)', 'type' => 'xa', 'sort_order' => 46],
                    ['name' => 'X. Đức Lập', 'type' => 'xa', 'sort_order' => 47],
                    ['name' => 'X. Mỹ Hạnh', 'type' => 'xa', 'sort_order' => 48],
                    // H Bến Lức
                    ['name' => 'X. Bến Lức', 'type' => 'xa', 'sort_order' => 49],
                    ['name' => 'X. Long Hiệp', 'type' => 'xa', 'sort_order' => 50],
                    ['name' => 'X. Phước Lợi', 'type' => 'xa', 'sort_order' => 51],
                    ['name' => 'X. Thạnh Đức', 'type' => 'xa', 'sort_order' => 52],
                    ['name' => 'X. Nhựt Chánh', 'type' => 'xa', 'sort_order' => 53],
                    // H Cần Giuộc
                    ['name' => 'X. Trường Bình', 'type' => 'xa', 'sort_order' => 54],
                    ['name' => 'X. Long Thượng', 'type' => 'xa', 'sort_order' => 55],
                    ['name' => 'X. Phước Lý', 'type' => 'xa', 'sort_order' => 56],
                    ['name' => 'X. Mỹ Lộc', 'type' => 'xa', 'sort_order' => 57],
                    ['name' => 'X. Long An (CG)', 'type' => 'xa', 'sort_order' => 58],
                    // H Cần Đước
                    ['name' => 'X. Tân Trạch', 'type' => 'xa', 'sort_order' => 59],
                    ['name' => 'X. Long Sơn (CĐ)', 'type' => 'xa', 'sort_order' => 60],
                    ['name' => 'X. Long Hựu', 'type' => 'xa', 'sort_order' => 61],
                    ['name' => 'X. Phước Tuy', 'type' => 'xa', 'sort_order' => 62],
                    // H Châu Thành (LA)
                    ['name' => 'X. Tầm Vu', 'type' => 'xa', 'sort_order' => 63],
                    ['name' => 'X. Thanh Phú Long', 'type' => 'xa', 'sort_order' => 64],
                    ['name' => 'X. Hòa Phú (LA)', 'type' => 'xa', 'sort_order' => 65],
                    ['name' => 'X. Dương Xuân Hội', 'type' => 'xa', 'sort_order' => 66],
                    // H Tân Trụ
                    ['name' => 'X. Tân Phước Tây', 'type' => 'xa', 'sort_order' => 67],
                    ['name' => 'X. Lạc Tấn', 'type' => 'xa', 'sort_order' => 68],
                    ['name' => 'X. Nhựt Ninh', 'type' => 'xa', 'sort_order' => 69],
                    // H Thủ Thừa
                    ['name' => 'X. Bình An', 'type' => 'xa', 'sort_order' => 70],
                    ['name' => 'X. Mỹ Phú', 'type' => 'xa', 'sort_order' => 71],
                    ['name' => 'X. Long Thạnh', 'type' => 'xa', 'sort_order' => 72],
                    // H Thạnh Hóa
                    ['name' => 'X. Thạnh Hóa', 'type' => 'xa', 'sort_order' => 73],
                    ['name' => 'X. Tân Hiệp (TH)', 'type' => 'xa', 'sort_order' => 74],
                    ['name' => 'X. Thuận Bình', 'type' => 'xa', 'sort_order' => 75],
                    // TX Kiến Tường
                    ['name' => 'X. Tuyên Thạnh', 'type' => 'xa', 'sort_order' => 76],
                    ['name' => 'X. Bình Hiệp', 'type' => 'xa', 'sort_order' => 77],
                    ['name' => 'X. Thạnh Trị', 'type' => 'xa', 'sort_order' => 78],
                    // H Mộc Hóa
                    ['name' => 'X. Bình Phong Thạnh', 'type' => 'xa', 'sort_order' => 79],
                    ['name' => 'X. Tân Lập (MH)', 'type' => 'xa', 'sort_order' => 80],
                    ['name' => 'X. Bình Hòa Tây', 'type' => 'xa', 'sort_order' => 81],
                    // H Vĩnh Hưng
                    ['name' => 'X. Vĩnh Bửu', 'type' => 'xa', 'sort_order' => 82],
                    ['name' => 'X. Thái Bình Trung', 'type' => 'xa', 'sort_order' => 83],
                    ['name' => 'X. Tuyên Bình', 'type' => 'xa', 'sort_order' => 84],
                    // H Tân Hưng
                    ['name' => 'X. Vĩnh Thạnh', 'type' => 'xa', 'sort_order' => 85],
                    ['name' => 'X. Hưng Hà', 'type' => 'xa', 'sort_order' => 86],
                    ['name' => 'X. Vĩnh Đại', 'type' => 'xa', 'sort_order' => 87],
                    // H Tân Thạnh
                    ['name' => 'X. Tân Thạnh', 'type' => 'xa', 'sort_order' => 88],
                    ['name' => 'X. Nhơn Hòa Lập', 'type' => 'xa', 'sort_order' => 89],
                    ['name' => 'X. Tân Ninh', 'type' => 'xa', 'sort_order' => 90],
                    // H Đức Huệ
                    ['name' => 'X. Đông Thành', 'type' => 'xa', 'sort_order' => 91],
                    ['name' => 'X. Mỹ Quý Đông', 'type' => 'xa', 'sort_order' => 92],
                    ['name' => 'X. Bình Hòa Hưng', 'type' => 'xa', 'sort_order' => 93],
                ],
            ],
        ];

        foreach ($data as $provinceData) {
            $wards = $provinceData['wards'];
            unset($provinceData['wards']);

            $province = Province::create($provinceData);

            foreach ($wards as $ward) {
                $province->wards()->create($ward);
            }
        }
    }
}
