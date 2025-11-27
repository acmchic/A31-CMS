<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Xóa các trường workflow-related không cần thiết nữa
     * Vì đã chuyển sang dùng approval_requests table
     */
    public function up(): void
    {
        // Xóa foreign key constraints trước khi xóa cột
        // Sử dụng DB::statement để xóa foreign key trực tiếp
        $foreignKeyColumns = [
            'approved_by_department_head',
            'approved_by_reviewer',
            'approved_by_director',
            'approved_by_approver',
        ];
        
        foreach ($foreignKeyColumns as $column) {
            if (Schema::hasColumn('employee_leave', $column)) {
                // Lấy tên foreign key từ information_schema
                $fk = \DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'employee_leave' 
                    AND COLUMN_NAME = ? 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$column]);
                
                if ($fk && isset($fk->CONSTRAINT_NAME)) {
                    \DB::statement("ALTER TABLE `employee_leave` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }
            }
        }
        
        Schema::table('employee_leave', function (Blueprint $table) {
            
            // Xóa các trường workflow status và selected approvers
            if (Schema::hasColumn('employee_leave', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
            
            if (Schema::hasColumn('employee_leave', 'selected_approvers')) {
                $table->dropColumn('selected_approvers');
            }
            
            // Xóa các trường approval by department head
            if (Schema::hasColumn('employee_leave', 'approved_by_department_head')) {
                $table->dropColumn('approved_by_department_head');
            }
            
            if (Schema::hasColumn('employee_leave', 'approved_at_department_head')) {
                $table->dropColumn('approved_at_department_head');
            }
            
            // Xóa các trường approval by reviewer
            if (Schema::hasColumn('employee_leave', 'approved_by_reviewer')) {
                $table->dropColumn('approved_by_reviewer');
            }
            
            if (Schema::hasColumn('employee_leave', 'approved_at_reviewer')) {
                $table->dropColumn('approved_at_reviewer');
            }
            
            // Xóa các trường approval by director
            if (Schema::hasColumn('employee_leave', 'approved_by_director')) {
                $table->dropColumn('approved_by_director');
            }
            
            if (Schema::hasColumn('employee_leave', 'approved_at_director')) {
                $table->dropColumn('approved_at_director');
            }
            
            // Xóa các trường signature paths
            if (Schema::hasColumn('employee_leave', 'approver_signature_path')) {
                $table->dropColumn('approver_signature_path');
            }
            
            if (Schema::hasColumn('employee_leave', 'director_signature_path')) {
                $table->dropColumn('director_signature_path');
            }
            
            // Xóa rejection_reason (đã chuyển sang approval_requests)
            if (Schema::hasColumn('employee_leave', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            
            // Xóa các trường legacy (approved_by_approver, etc.)
            if (Schema::hasColumn('employee_leave', 'approved_by_approver')) {
                $table->dropColumn('approved_by_approver');
            }
            
            if (Schema::hasColumn('employee_leave', 'approved_at_approver')) {
                $table->dropColumn('approved_at_approver');
            }
            
            if (Schema::hasColumn('employee_leave', 'approver_comment')) {
                $table->dropColumn('approver_comment');
            }
            
            if (Schema::hasColumn('employee_leave', 'director_comment')) {
                $table->dropColumn('director_comment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            // Re-add columns if rolling back
            $table->string('workflow_status', 50)->default('pending')->nullable();
            $table->json('selected_approvers')->nullable();
            
            $table->foreignId('approved_by_department_head')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_department_head')->nullable();
            
            $table->foreignId('approved_by_reviewer')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_reviewer')->nullable();
            
            $table->foreignId('approved_by_director')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_director')->nullable();
            
            $table->string('approver_signature_path')->nullable();
            $table->string('director_signature_path')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->foreignId('approved_by_approver')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_approver')->nullable();
            $table->text('approver_comment')->nullable();
            $table->text('director_comment')->nullable();
        });
    }
};

