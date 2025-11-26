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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                    // Mã vật tư
            $table->string('name');                              // Tên vật tư
            $table->string('unit');                              // Đơn vị tính (kg, m, cái...)
            $table->text('description')->nullable();
            $table->decimal('min_stock_level', 10, 2)->default(0); // Mức tồn kho tối thiểu
            $table->decimal('max_stock_level', 10, 2)->nullable(); // Mức tồn kho tối đa
            // Trạng thái vật tư
            $table->enum('status', ['active', 'inactive'])->default('active'); // Active / Inactive
            $table->boolean('can_import')->default(true);        // Cho phép nhập (true) / Dừng nhập (false)
            $table->boolean('can_export')->default(true);        // Cho phép xuất (true) / Cấm xuất (false)
            $table->timestamps();
            
            // Indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
