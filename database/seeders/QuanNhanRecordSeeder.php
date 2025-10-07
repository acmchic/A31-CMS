<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\OrganizationStructure\Models\Employee;
use Modules\RecordManagement\Models\QuanNhanRecord;

class QuanNhanRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Bắt đầu tạo Sổ Danh sách Quân nhân từ dữ liệu nhân sự...');

        // Lấy tất cả employees
        $employees = Employee::with('department')
            ->orderBy('id', 'asc')
            ->get();

        $this->command->info("Tìm thấy {$employees->count()} nhân viên.");

        $created = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            // Kiểm tra xem đã tồn tại chưa
            $exists = QuanNhanRecord::where('employee_id', $employee->id)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Tạo record mới
            QuanNhanRecord::create([
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                // Các field khác để null, sẽ được điền sau
            ]);

            $created++;
        }

        $this->command->info("✅ Hoàn thành!");
        $this->command->info("   - Đã tạo: {$created} records");
        $this->command->info("   - Đã bỏ qua (đã tồn tại): {$skipped} records");
    }
}
