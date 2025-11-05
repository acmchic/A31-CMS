<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Sync dữ liệu từ Employee sang User để đảm bảo đồng bộ:
     * - Sync name từ Employee sang User
     * - Sync department_id từ Employee sang User
     */
    public function up(): void
    {
        // Sync dữ liệu từ Employee sang User cho các user đã có employee_id
        DB::statement("
            UPDATE users u
            INNER JOIN employees e ON u.employee_id = e.id
            SET 
                u.name = e.name,
                u.department_id = e.department_id
            WHERE u.employee_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần revert vì đây chỉ là sync dữ liệu
    }
};
