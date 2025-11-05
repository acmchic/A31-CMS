<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the column to use new enum values (this will set invalid values to empty)
        DB::statement("ALTER TABLE employee_leave MODIFY COLUMN workflow_status ENUM('pending', 'approved_by_approver', 'approved_by_director', 'rejected') NOT NULL DEFAULT 'pending'");
        
        // Then update existing data to match new enum values
        DB::statement("UPDATE employee_leave SET workflow_status = 'pending' WHERE workflow_status = '' OR workflow_status IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum values
        DB::statement("UPDATE employee_leave SET workflow_status = 'draft' WHERE workflow_status = 'pending'");
        DB::statement("UPDATE employee_leave SET workflow_status = 'approved' WHERE workflow_status = 'approved_by_director'");
        DB::statement("UPDATE employee_leave SET workflow_status = 'approved' WHERE workflow_status = 'approved_by_approver'");

        DB::statement("ALTER TABLE employee_leave MODIFY COLUMN workflow_status ENUM('draft','submitted','under_review','approved','rejected') NOT NULL DEFAULT 'draft'");
    }
};