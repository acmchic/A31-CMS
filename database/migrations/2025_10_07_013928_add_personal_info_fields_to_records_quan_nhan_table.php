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
        Schema::table('records_quan_nhan', function (Blueprint $table) {
            // Thêm các trường thông tin cá nhân bổ sung
            $table->string('ho_ten_thuong_dung')->nullable()->comment('Họ tên thường dùng')->after('department_id');
            $table->string('so_hieu_quan_nhan')->nullable()->comment('Số hiệu quân nhân')->after('ho_ten_thuong_dung');
            $table->string('so_the_QN')->nullable()->comment('Số thẻ quân nhân')->after('so_hieu_quan_nhan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records_quan_nhan', function (Blueprint $table) {
            // Xóa các cột khi rollback
            $table->dropColumn(['ho_ten_thuong_dung', 'so_hieu_quan_nhan', 'so_the_QN']);
        });
    }
};
