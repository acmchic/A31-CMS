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
        // Chỉ alter nếu bảng users đã tồn tại (từ migration Laravel core)
        if (!Schema::hasTable('users')) {
            return;
        }
        
        // Thêm các cột còn thiếu với điều kiện kiểm tra
        if (!Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('employee_id')->nullable()->after('id');
            });
        }
        
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('name');
            });
        }
        
        if (!Schema::hasColumn('users', 'profile_photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_photo_path', 2048)->nullable()->after('remember_token');
            });
        }
        
        if (!Schema::hasColumn('users', 'signature_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('signature_path')->nullable()->after('profile_photo_path');
            });
        }
        
        if (!Schema::hasColumn('users', 'certificate_pin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('certificate_pin')->nullable()->comment('PIN riêng cho chữ ký số của user')->after('signature_path');
            });
        }
        
        if (!Schema::hasColumn('users', 'certificate_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('certificate_path')->nullable()->comment('Đường dẫn đến file certificate .pfx riêng của user')->after('certificate_pin');
            });
        }
        
        if (!Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('certificate_path');
            });
        }
        
        if (!Schema::hasColumn('users', 'position')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('position')->nullable()->after('department_id');
            });
        }
        
        if (!Schema::hasColumn('users', 'is_department_head')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_department_head')->default(false)->after('position');
            });
        }
        
        if (!Schema::hasColumn('users', 'department_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('department_permissions')->nullable()->after('is_department_head');
            });
        }
        
        if (!Schema::hasColumn('users', 'created_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('created_by')->nullable()->after('department_permissions');
            });
        }
        
        if (!Schema::hasColumn('users', 'updated_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('updated_by')->nullable()->after('created_by');
            });
        }
        
        if (!Schema::hasColumn('users', 'deleted_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('deleted_by')->nullable()->after('updated_by');
            });
        }
        
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            });
        }
        
        // Thay đổi cột name từ NOT NULL sang nullable (theo SQL dump)
        if (Schema::hasColumn('users', 'name')) {
            \DB::statement('ALTER TABLE `users` MODIFY `name` VARCHAR(255) NULL');
        }
        
        // Thêm các index nếu chưa có
        if (Schema::hasColumn('users', 'username')) {
            $indexes = \DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'users_username_index'");
            if (empty($indexes)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('username', 'users_username_index');
                });
            }
        }
        
        if (Schema::hasColumn('users', 'department_id') && Schema::hasColumn('users', 'is_department_head')) {
            $indexes = \DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'users_department_id_is_department_head_index'");
            if (empty($indexes)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index(['department_id', 'is_department_head'], 'users_department_id_is_department_head_index');
                });
            }
        }
        
        if (Schema::hasColumn('users', 'employee_id')) {
            $indexes = \DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'users_employee_id_index'");
            if (empty($indexes)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('employee_id', 'users_employee_id_index');
                });
            }
        }
        
        // Thêm foreign key cho department_id nếu chưa có
        if (Schema::hasColumn('users', 'department_id') && Schema::hasTable('departments')) {
            $foreignKeys = \DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'users' 
                AND CONSTRAINT_NAME = 'users_department_id_foreign'
            ");
            if (empty($foreignKeys)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('department_id', 'users_department_id_foreign')
                        ->references('id')
                        ->on('departments')
                        ->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }
        
        // Xóa foreign key nếu tồn tại
        $foreignKeys = \DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'users' 
            AND CONSTRAINT_NAME = 'users_department_id_foreign'
        ");
        if (!empty($foreignKeys)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign('users_department_id_foreign');
            });
        }
        
        // Xóa các index nếu tồn tại
        $indexes = \DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'users_username_index'");
        if (!empty($indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_username_index');
            });
        }
        
        $indexes = \DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'users_department_id_is_department_head_index'");
        if (!empty($indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_department_id_is_department_head_index');
            });
        }
        
        $indexes = \DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'users_employee_id_index'");
        if (!empty($indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_employee_id_index');
            });
        }
        
        // Xóa các cột đã thêm (theo thứ tự ngược lại)
        $columnsToDrop = [
            'deleted_at',
            'deleted_by',
            'updated_by',
            'created_by',
            'department_permissions',
            'is_department_head',
            'position',
            'department_id',
            'certificate_path',
            'certificate_pin',
            'signature_path',
            'profile_photo_path',
            'username',
            'employee_id'
        ];
        
        foreach ($columnsToDrop as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
        
        // Khôi phục cột name về NOT NULL (nếu cần)
        if (Schema::hasColumn('users', 'name')) {
            \DB::statement('ALTER TABLE `users` MODIFY `name` VARCHAR(255) NOT NULL');
        }
    }
};
