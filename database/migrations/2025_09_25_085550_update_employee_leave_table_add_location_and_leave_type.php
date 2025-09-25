<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            // Add location field
            $table->string('location')->nullable()->after('note');
            
            // Add leave_type field
            $table->string('leave_type')->nullable()->after('location');
            
            // Drop foreign key constraint for leave_id if exists
            $table->dropForeign(['leave_id']);
            $table->dropColumn('leave_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            // Remove added fields
            $table->dropColumn(['location', 'leave_type']);
            
            // Add back leave_id
            $table->unsignedBigInteger('leave_id')->nullable()->after('employee_id');
            $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('set null');
        });
    }
};
