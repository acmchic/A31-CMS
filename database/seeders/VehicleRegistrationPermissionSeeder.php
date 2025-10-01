<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class VehicleRegistrationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permissions cho Vehicle Registration
        $permissions = [
            // CRUD permissions
            'vehicle_registration.view' => 'Xem danh sách đăng ký xe',
            'vehicle_registration.create' => 'Tạo đăng ký xe mới',
            'vehicle_registration.edit' => 'Sửa đăng ký xe',
            'vehicle_registration.delete' => 'Xóa đăng ký xe',
            
            // Workflow permissions
            'vehicle_registration.assign' => 'Phân công xe và lái xe (Đội trưởng xe)',
            'vehicle_registration.approve' => 'Phê duyệt đăng ký xe (Ban Giám Đốc)',
            'vehicle_registration.reject' => 'Từ chối đăng ký xe',
            
            // Additional permissions
            'vehicle_registration.download_pdf' => 'Tải PDF đã ký',
            'vehicle_registration.check_signature' => 'Kiểm tra chữ ký số',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Lấy hoặc tạo roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $banGiamDoc = Role::firstOrCreate(['name' => 'ban_giam_doc']);
        $doiTruongXe = Role::firstOrCreate(['name' => 'doi_truong_xe']);
        $nhanVien = Role::firstOrCreate(['name' => 'nhan_vien']);

        // Gán quyền cho Admin (tất cả quyền)
        $admin->givePermissionTo(Permission::where('name', 'like', 'vehicle_registration.%')->get());

        // Gán quyền cho Ban Giám Đốc
        $banGiamDoc->givePermissionTo([
            'vehicle_registration.view',
            'vehicle_registration.approve',
            'vehicle_registration.reject',
            'vehicle_registration.download_pdf',
            'vehicle_registration.check_signature',
        ]);

        // Gán quyền cho Đội trưởng xe
        $doiTruongXe->givePermissionTo([
            'vehicle_registration.view',
            'vehicle_registration.assign',
            'vehicle_registration.edit',
        ]);

        // Gán quyền cho Nhân viên (tạo và xem đăng ký của mình)
        $nhanVien->givePermissionTo([
            'vehicle_registration.view',
            'vehicle_registration.create',
            'vehicle_registration.edit',
        ]);

        $this->command->info('✅ Vehicle Registration permissions và roles đã được tạo thành công!');
        $this->command->info('');
        $this->command->info('📋 Danh sách permissions:');
        foreach ($permissions as $name => $description) {
            $this->command->info("  - {$name}: {$description}");
        }
        $this->command->info('');
        $this->command->info('👥 Roles và quyền:');
        $this->command->info('  - Admin: Tất cả quyền');
        $this->command->info('  - Ban Giám Đốc: View, Approve, Reject, Download PDF, Check Signature');
        $this->command->info('  - Đội trưởng xe: View, Assign, Edit');
        $this->command->info('  - Nhân viên: View, Create, Edit (own records)');
    }
}
