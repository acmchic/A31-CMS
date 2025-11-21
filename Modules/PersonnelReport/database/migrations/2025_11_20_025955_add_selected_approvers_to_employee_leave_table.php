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
            $table->json('selected_approvers')->nullable()->after('approved_by_reviewer')->comment('Danh sách ID người phê duyệt được chọn bởi reviewer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            $table->dropColumn('selected_approvers');
        });
    }
};
