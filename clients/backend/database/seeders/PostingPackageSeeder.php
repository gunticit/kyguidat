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
                'description' => 'Gói đăng bài 1 tháng, đăng tối đa 99 sản phẩm',
                'duration_months' => 1,
                'price' => 1500000,
                'original_price' => null,
                'post_limit' => 99,
                'featured_posts' => 5,
                'priority_support' => false,
                'features' => [
                    'Đăng tối đa 99 sản phẩm',
                    'Thời hạn 1 tháng',
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
                'description' => 'Gói đăng bài 2 tháng, đăng tối đa 99 sản phẩm',
                'duration_months' => 2,
                'price' => 3000000,
                'original_price' => null,
                'post_limit' => 99,
                'featured_posts' => 10,
                'priority_support' => false,
                'features' => [
                    'Đăng tối đa 99 sản phẩm',
                    'Thời hạn 2 tháng',
                    'Hỗ trợ qua email & chat',
                    'Thống kê chi tiết',
                ],
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gói 3 tháng',
                'slug' => '3-months',
                'description' => 'Gói đăng bài 3 tháng phổ biến nhất, đăng tối đa 99 sản phẩm',
                'duration_months' => 3,
                'price' => 4500000,
                'original_price' => null,
                'post_limit' => 99,
                'featured_posts' => 15,
                'priority_support' => true,
                'features' => [
                    'Đăng tối đa 99 sản phẩm',
                    'Thời hạn 3 tháng',
                    'Hỗ trợ ưu tiên 24/7',
                    'Thống kê nâng cao',
                    'Đẩy bài lên top',
                ],
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 3,
            ]
        ];

        foreach ($packages as $package) {
            PostingPackage::updateOrCreate(
                ['slug' => $package['slug']],
                $package
            );
        }

        // Remove old packages that no longer exist
        PostingPackage::whereNotIn('slug', ['1-month', '2-months', '3-months', '6-months', '12-months'])->delete();
    }
}
