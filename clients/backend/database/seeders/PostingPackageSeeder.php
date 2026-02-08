<?php

namespace Database\Seeders;

use App\Models\PostingPackage;
use Illuminate\Database\Seeder;

class PostingPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Gói 1 tháng',
                'slug' => '1-month',
                'description' => 'Gói đăng bài cơ bản trong 1 tháng, phù hợp cho người mới bắt đầu',
                'duration_months' => 1,
                'price' => 99000,
                'original_price' => null,
                'post_limit' => 10,
                'featured_posts' => 1,
                'priority_support' => false,
                'features' => [
                    'Đăng tối đa 10 bài/tháng',
                    '1 bài nổi bật',
                    'Hỗ trợ qua email',
                    'Thống kê cơ bản',
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Gói 2 tháng',
                'slug' => '2-months',
                'description' => 'Gói đăng bài 2 tháng với nhiều ưu đãi hơn',
                'duration_months' => 2,
                'price' => 179000,
                'original_price' => 198000,
                'post_limit' => 25,
                'featured_posts' => 3,
                'priority_support' => false,
                'features' => [
                    'Đăng tối đa 25 bài',
                    '3 bài nổi bật',
                    'Hỗ trợ qua email & chat',
                    'Thống kê chi tiết',
                    'Tiết kiệm 10%',
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gói 3 tháng',
                'slug' => '3-months',
                'description' => 'Gói phổ biến nhất với giá trị tốt nhất, được nhiều người lựa chọn',
                'duration_months' => 3,
                'price' => 249000,
                'original_price' => 297000,
                'post_limit' => 50,
                'featured_posts' => 5,
                'priority_support' => true,
                'features' => [
                    'Đăng tối đa 50 bài',
                    '5 bài nổi bật',
                    'Hỗ trợ ưu tiên 24/7',
                    'Thống kê nâng cao',
                    'Đẩy bài lên top',
                    'Tiết kiệm 16%',
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Gói 6 tháng',
                'slug' => '6-months',
                'description' => 'Gói Premium với đầy đủ tính năng và ưu đãi tốt nhất',
                'duration_months' => 6,
                'price' => 449000,
                'original_price' => 594000,
                'post_limit' => -1, // Không giới hạn
                'featured_posts' => 15,
                'priority_support' => true,
                'features' => [
                    'Đăng bài không giới hạn',
                    '15 bài nổi bật',
                    'Hỗ trợ ưu tiên VIP 24/7',
                    'Thống kê nâng cao + Báo cáo',
                    'Đẩy bài lên top mỗi ngày',
                    'Huy hiệu Premium',
                    'Tiết kiệm 24%',
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $package) {
            PostingPackage::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }
    }
}
