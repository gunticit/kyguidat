<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo Permissions
        $permissions = [
            // Users
            ['name' => 'users.view', 'display_name' => 'Xem người dùng', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Tạo người dùng', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Sửa người dùng', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Xóa người dùng', 'group' => 'users'],

            // Consignments (Bất động sản)
            ['name' => 'consignments.view', 'display_name' => 'Xem bất động sản', 'group' => 'consignments'],
            ['name' => 'consignments.create', 'display_name' => 'Tạo bất động sản', 'group' => 'consignments'],
            ['name' => 'consignments.edit', 'display_name' => 'Sửa bất động sản', 'group' => 'consignments'],
            ['name' => 'consignments.delete', 'display_name' => 'Xóa bất động sản', 'group' => 'consignments'],
            ['name' => 'consignments.approve', 'display_name' => 'Duyệt bất động sản', 'group' => 'consignments'],
            ['name' => 'consignments.reject', 'display_name' => 'Từ chối bất động sản', 'group' => 'consignments'],

            // Payments
            ['name' => 'payments.view', 'display_name' => 'Xem thanh toán', 'group' => 'payments'],
            ['name' => 'payments.manage', 'display_name' => 'Quản lý thanh toán', 'group' => 'payments'],

            // Support Tickets
            ['name' => 'tickets.view', 'display_name' => 'Xem hỗ trợ', 'group' => 'tickets'],
            ['name' => 'tickets.manage', 'display_name' => 'Quản lý hỗ trợ', 'group' => 'tickets'],

            // Settings
            ['name' => 'settings.view', 'display_name' => 'Xem cài đặt', 'group' => 'settings'],
            ['name' => 'settings.manage', 'display_name' => 'Quản lý cài đặt', 'group' => 'settings'],

            // Roles & Permissions
            ['name' => 'roles.view', 'display_name' => 'Xem phân quyền', 'group' => 'roles'],
            ['name' => 'roles.manage', 'display_name' => 'Quản lý phân quyền', 'group' => 'roles'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(['name' => $permData['name']], $permData);
        }

        // Tạo Roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Quản trị viên', 'description' => 'Toàn quyền hệ thống']
        );

        $moderatorRole = Role::firstOrCreate(
            ['name' => 'moderator'],
            ['display_name' => 'Kiểm duyệt', 'description' => 'Duyệt và quản lý nội dung']
        );

        $publisherRole = Role::firstOrCreate(
            ['name' => 'publisher'],
            ['display_name' => 'Đăng bài', 'description' => 'Tạo và quản lý bài đăng']
        );

        // Gán permissions cho Admin (tất cả)
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Gán permissions cho Kiểm duyệt
        $moderatorPermissions = Permission::whereIn('name', [
            'consignments.view',
            'consignments.approve',
            'consignments.reject',
            'tickets.view',
            'tickets.manage',
        ])->pluck('id');
        $moderatorRole->permissions()->sync($moderatorPermissions);

        // Gán permissions cho Đăng bài
        $publisherPermissions = Permission::whereIn('name', [
            'consignments.view',
            'consignments.create',
            'consignments.edit',
        ])->pluck('id');
        $publisherRole->permissions()->sync($publisherPermissions);

        // Tạo Admin user mặc định
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@khodat.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $adminUser->assignRole('admin');

        // Tạo Moderator user mẫu
        $moderatorUser = User::firstOrCreate(
            ['email' => 'moderator@khodat.com'],
            [
                'name' => 'Kiểm Duyệt Viên',
                'password' => Hash::make('mod123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $moderatorUser->assignRole('moderator');

        // Tạo Publisher user mẫu
        $publisherUser = User::firstOrCreate(
            ['email' => 'publisher@khodat.com'],
            [
                'name' => 'Người Đăng Bài',
                'password' => Hash::make('pub123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $publisherUser->assignRole('publisher');

        $this->command->info('Roles and Permissions seeded successfully!');
        $this->command->info('Admin: admin@khodat.com / admin123');
        $this->command->info('Moderator: moderator@khodat.com / mod123');
        $this->command->info('Publisher: publisher@khodat.com / pub123');
    }
}
