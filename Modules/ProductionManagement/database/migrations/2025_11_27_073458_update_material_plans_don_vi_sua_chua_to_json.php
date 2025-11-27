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
        // Xóa dữ liệu cũ không hợp lệ trước khi đổi kiểu
        \DB::table('material_plans')->whereNotNull('don_vi_sua_chua')->update(['don_vi_sua_chua' => null]);
        
        Schema::table('material_plans', function (Blueprint $table) {
            // Đổi từ string sang json để lưu multiple values
            $table->json('don_vi_sua_chua')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_plans', function (Blueprint $table) {
            // Đổi lại từ json sang string
            $table->string('don_vi_sua_chua')->nullable()->change();
        });
    }
};
