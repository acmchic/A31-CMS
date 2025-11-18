<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_leave', 'approved_by_department_head')) {
                $table->unsignedBigInteger('approved_by_department_head')->nullable()->after('approved_by_approver');
                $table->timestamp('approved_at_department_head')->nullable()->after('approved_at_approver');
            }
            
            if (!Schema::hasColumn('employee_leave', 'approved_by_reviewer')) {
                $table->unsignedBigInteger('approved_by_reviewer')->nullable()->after('approved_by_department_head');
                $table->timestamp('approved_at_reviewer')->nullable()->after('approved_at_department_head');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            if (Schema::hasColumn('employee_leave', 'approved_by_department_head')) {
                $table->dropColumn(['approved_by_department_head', 'approved_at_department_head']);
            }
            
            if (Schema::hasColumn('employee_leave', 'approved_by_reviewer')) {
                $table->dropColumn(['approved_by_reviewer', 'approved_at_reviewer']);
            }
        });
    }
};
