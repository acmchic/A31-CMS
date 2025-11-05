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
        // Change workflow_status from ENUM to VARCHAR to support flexible workflow values
        DB::statement("ALTER TABLE `vehicle_registrations` MODIFY `workflow_status` VARCHAR(50) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original ENUM values if needed
        DB::statement("ALTER TABLE `vehicle_registrations` MODIFY `workflow_status` ENUM('submitted', 'dept_review', 'director_review', 'approved', 'rejected') DEFAULT 'submitted'");
    }
};
