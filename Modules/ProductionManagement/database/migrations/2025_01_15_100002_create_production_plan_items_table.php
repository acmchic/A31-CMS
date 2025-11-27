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
        Schema::create('material_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_plan_id')->constrained('material_plans')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            
            $table->integer('so_thu_tu')->default(0); // Số thứ tự trong bảng
            $table->decimal('so_luong', 10, 2)->default(0); // Số lượng
            $table->decimal('doi_cu', 10, 2)->default(0); // Đổi cũ
            $table->decimal('cap_moi', 10, 2)->default(0); // Cấp mới
            $table->text('ghi_chu')->nullable(); // Ghi chú
            
            $table->timestamps();
            
            $table->index(['material_plan_id', 'so_thu_tu']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_plan_items');
    }
};

