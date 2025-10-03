<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\OrganizationStructure\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateEnlistDateFromJson extends Command
{
    protected $signature = 'employees:update-enlist-date';
    protected $description = 'Update enlist_date từ quanso.json theo username (format mm/yyyy hoặc --)';

    public function handle()
    {
        $this->info('Bắt đầu update enlist_date từ quanso.json...');
        
        // Đọc file JSON
        $jsonPath = storage_path('app/private/quanso.json');
        
        if (!file_exists($jsonPath)) {
            $this->error('File quanso.json không tồn tại!');
            return 1;
        }
        
        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);
        
        if (!$data || !isset($data['departments'])) {
            $this->error('File JSON không hợp lệ!');
            return 1;
        }
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $noUser = 0;
        $noEmployeeId = 0;
        $total = 0;
        
        // Loop qua từng department
        foreach ($data['departments'] as $dept) {
            $departmentName = $dept['name'];
            $members = $dept['members'] ?? [];
            $teams = $dept['teams'] ?? [];
            
            $this->info("Đang xử lý phòng: {$departmentName}");
            
            // Xử lý members ở cấp department
            foreach ($members as $member) {
                $this->processAndUpdateMember($member, $updated, $skipped, $errors, $noUser, $noEmployeeId, $total);
            }
            
            // Xử lý members trong teams
            foreach ($teams as $team) {
                $teamName = $team['name'] ?? 'N/A';
                $this->line("  → Team: {$teamName}");
                
                $teamMembers = $team['members'] ?? [];
                foreach ($teamMembers as $member) {
                    $this->processAndUpdateMember($member, $updated, $skipped, $errors, $noUser, $noEmployeeId, $total);
                }
            }
        }
        
        // Summary
        $this->newLine();
        $this->info('=== KẾT QUẢ ===');
        $this->info("📊 Tổng số trong JSON: {$total} người");
        $this->info("✓ Đã update: {$updated} nhân viên");
        $this->warn("⚠ Không tìm thấy User: {$noUser} người");
        $this->warn("⚠ User chưa có employee_id: {$noEmployeeId} người");
        $this->warn("- Bỏ qua (khác): {$skipped} người");
        $this->error("✗ Lỗi: {$errors} người");
        
        $this->newLine();
        $this->info("💡 Cần tạo User và link employee_id cho " . ($noUser + $noEmployeeId) . " người còn lại");
        
        return 0;
    }
    
    /**
     * Xử lý và update thông tin từ member
     */
    private function processAndUpdateMember($member, &$updated, &$skipped, &$errors, &$noUser, &$noEmployeeId, &$total)
    {
        $username = $member['username'] ?? null;
        $enlistTd = $member['enlist_td'] ?? null;
        $fullName = $member['full_name'] ?? 'N/A';
        
        $total++;
        
        if (!$username) {
            $this->line("    - Skip {$fullName}: Không có username");
            $skipped++;
            return;
        }
        
        try {
            // Bước 1: Tìm User theo username
            $user = User::where('username', $username)->first();
            
            if (!$user) {
                $this->warn("    ⚠ Không tìm thấy user: {$username} ({$fullName})");
                $noUser++;
                return;
            }
            
            // Bước 2: Lấy employee_id từ User
            if (!$user->employee_id) {
                $this->warn("    ⚠ User {$username} chưa có employee_id ({$fullName})");
                $noEmployeeId++;
                return;
            }
            
            // Bước 3: Tìm Employee
            $employee = Employee::find($user->employee_id);
            
            if (!$employee) {
                $this->warn("    ⚠ Employee #{$user->employee_id} không tồn tại");
                $skipped++;
                return;
            }
            
            // Bước 4: Convert enlist_td sang format mm/yyyy hoặc --
            $enlistDate = $this->convertEnlistTdToString($enlistTd);
            
            // Bước 5: Update CHỈ trường enlist_date
            DB::table('employees')
                ->where('id', $employee->id)
                ->update(['enlist_date' => $enlistDate]);
            
            $displayValue = $enlistTd ? "{$enlistTd} → {$enlistDate}" : "null → --";
            $this->info("    ✓ {$username}: {$displayValue}");
            $updated++;
            
        } catch (\Exception $e) {
            $this->error("    ✗ Lỗi {$username}: {$e->getMessage()}");
            $errors++;
        }
    }
    
    /**
     * Convert "mm/yy" sang "mm/yyyy" hoặc null → "--"
     * Ví dụ: "9/91" → "09/1991"
     *         "3/05" → "03/2005"
     *         null → "--"
     */
    private function convertEnlistTdToString($enlistTd)
    {
        // Nếu null hoặc rỗng → trả về "--"
        if (!$enlistTd || trim($enlistTd) === '') {
            return '--';
        }
        
        // Trim và lấy dòng đầu tiên nếu có xuống dòng
        $enlistTd = trim(explode("\n", $enlistTd)[0]);
        
        // Parse "mm/yy"
        $parts = explode('/', $enlistTd);
        
        if (count($parts) !== 2) {
            throw new \Exception("Format không hợp lệ: {$enlistTd}");
        }
        
        $month = (int) $parts[0];
        $year = (int) $parts[1];
        
        // Validate month
        if ($month < 1 || $month > 12) {
            throw new \Exception("Tháng không hợp lệ: {$month}");
        }
        
        // Convert yy → yyyy
        // Quy tắc: 
        // - Nếu yy >= 50 → 19yy (1950-1999)
        // - Nếu yy < 50 → 20yy (2000-2049)
        if ($year >= 50) {
            $fullYear = 1900 + $year;
        } else {
            $fullYear = 2000 + $year;
        }
        
        // Format: mm/yyyy (string)
        return sprintf('%02d/%04d', $month, $fullYear);
    }
}
