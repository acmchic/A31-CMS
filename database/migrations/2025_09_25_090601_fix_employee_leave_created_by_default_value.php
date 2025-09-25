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
            // Make created_by and updated_by nullable with default value
            $table->string('created_by')->nullable()->default('system')->change();
            $table->string('updated_by')->nullable()->default('system')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            // Revert back to not nullable
            $table->string('created_by')->nullable(false)->change();
            $table->string('updated_by')->nullable(false)->change();
        });
    }
};
