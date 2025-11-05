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
     * Đảm bảo cấu trúc DB để Employee là Single Source of Truth:
     * - employee_id có foreign key constraint
     * - name và department_id có thể nullable (sẽ được sync từ Employee)
     */
    public function up(): void
    {
        // Đảm bảo employee_id tồn tại
        if (!Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('id');
            });
        }
        
        // Đảm bảo name có thể nullable (sẽ được sync từ Employee)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name')->nullable()->change();
            });
        } catch (\Exception $e) {
            // Column có thể đã nullable, bỏ qua
        }
        
        // Đảm bảo department_id có thể nullable (sẽ được sync từ Employee)
        if (Schema::hasColumn('users', 'department_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedBigInteger('department_id')->nullable()->change();
                });
            } catch (\Exception $e) {
                // Column có thể đã nullable, bỏ qua
            }
        }

        // Thêm foreign key constraint cho employee_id nếu chưa có
        try {
            Schema::table('users', function (Blueprint $table) {
                // Thêm foreign key constraint (sẽ bỏ qua nếu đã tồn tại)
                $table->foreign('employee_id', 'users_employee_id_foreign')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key đã tồn tại, bỏ qua
        }

        // Thêm index cho employee_id để tối ưu performance
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('employee_id', 'users_employee_id_index');
            });
        } catch (\Exception $e) {
            // Index đã tồn tại, bỏ qua
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                // Xóa foreign key constraint nếu có
                $table->dropForeign('users_employee_id_foreign');
            } catch (\Exception $e) {
                // Foreign key không tồn tại, bỏ qua
            }
            
            try {
                // Xóa index
                $table->dropIndex('users_employee_id_index');
            } catch (\Exception $e) {
                // Index không tồn tại, bỏ qua
            }
            
            // Không revert nullable vì có thể có dữ liệu null
        });
    }
};
