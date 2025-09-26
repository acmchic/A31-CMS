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
            // Thêm cột workflow_status nếu chưa có
            if (!Schema::hasColumn('employee_leave', 'workflow_status')) {
                $table->enum('workflow_status', ['pending', 'approved_by_approver', 'approved_by_director', 'rejected'])
                      ->default('pending')
                      ->after('status');
            }
            
            // Thông tin phê duyệt cấp 1 (Phê duyệt) - chỉ thêm nếu chưa có
            if (!Schema::hasColumn('employee_leave', 'approved_by_approver')) {
                $table->unsignedBigInteger('approved_by_approver')->nullable()->after('workflow_status');
            }
            if (!Schema::hasColumn('employee_leave', 'approved_at_approver')) {
                $table->timestamp('approved_at_approver')->nullable()->after('approved_by_approver');
            }
            if (!Schema::hasColumn('employee_leave', 'approver_comment')) {
                $table->text('approver_comment')->nullable()->after('approved_at_approver');
            }
            if (!Schema::hasColumn('employee_leave', 'approver_signature_path')) {
                $table->string('approver_signature_path')->nullable()->after('approver_comment');
            }
            
            // Thông tin phê duyệt cấp 2 (Ban Giám Đốc) - chỉ thêm nếu chưa có
            if (!Schema::hasColumn('employee_leave', 'approved_by_director')) {
                $table->unsignedBigInteger('approved_by_director')->nullable()->after('approver_signature_path');
            }
            if (!Schema::hasColumn('employee_leave', 'approved_at_director')) {
                $table->timestamp('approved_at_director')->nullable()->after('approved_by_director');
            }
            if (!Schema::hasColumn('employee_leave', 'director_comment')) {
                $table->text('director_comment')->nullable()->after('approved_at_director');
            }
            if (!Schema::hasColumn('employee_leave', 'director_signature_path')) {
                $table->string('director_signature_path')->nullable()->after('director_comment');
            }
        });
        
        // Add foreign keys if columns exist
        if (Schema::hasColumn('employee_leave', 'approved_by_approver')) {
            Schema::table('employee_leave', function (Blueprint $table) {
                $table->foreign('approved_by_approver')->references('id')->on('users')->onDelete('set null');
            });
        }
        if (Schema::hasColumn('employee_leave', 'approved_by_director')) {
            Schema::table('employee_leave', function (Blueprint $table) {
                $table->foreign('approved_by_director')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            $table->dropForeign(['approved_by_approver']);
            $table->dropForeign(['approved_by_director']);
            
            $table->dropColumn([
                'workflow_status',
                'approved_by_approver',
                'approved_at_approver',
                'approver_comment',
                'approver_signature_path',
                'approved_by_director',
                'approved_at_director',
                'director_comment',
                'director_signature_path'
            ]);
        });
    }
};