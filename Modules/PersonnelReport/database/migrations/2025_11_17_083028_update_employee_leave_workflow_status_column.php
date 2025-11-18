<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change workflow_status from ENUM to VARCHAR to support new workflow values
        DB::statement("ALTER TABLE `employee_leave` MODIFY COLUMN `workflow_status` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert back to ENUM (with old values)
        DB::statement("ALTER TABLE `employee_leave` MODIFY COLUMN `workflow_status` ENUM('pending','approved_by_approver','approved_by_director','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'");
    }
};
