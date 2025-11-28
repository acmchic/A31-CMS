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
            $table->string('code')->unique()->nullable();        // Mã vật tư
            $table->string('ten_vat_tu');                       // Tên vật tư
            $table->string('quy_cach')->nullable();             // Quy cách
            $table->string('ky_hieu')->nullable();              // Ký hiệu
            $table->string('don_vi_tinh');                      // Đơn vị tính (lít, kg, cái, bộ, tờ, đôi)
            $table->text('mo_ta')->nullable();                  // Mô tả chi tiết
            $table->decimal('min_stock_level', 10, 2)->default(0); // Mức tồn kho tối thiểu
            $table->decimal('max_stock_level', 10, 2)->nullable(); // Mức tồn kho tối đa
            // Trạng thái vật tư
            $table->enum('status', ['active', 'inactive'])->default('active'); // Active / Inactive
            $table->boolean('can_import')->default(true);        // Cho phép nhập (true) / Dừng nhập (false)
            $table->boolean('can_export')->default(true);        // Cho phép xuất (true) / Cấm xuất (false)
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('ten_vat_tu');
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



