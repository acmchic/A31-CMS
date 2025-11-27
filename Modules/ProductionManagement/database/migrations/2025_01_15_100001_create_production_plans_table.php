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
        Schema::create('material_plans', function (Blueprint $table) {
            $table->id();

            // Thông tin khí tài
            $table->string('ten_khi_tai'); // Tên khí tài
            $table->string('ky_hieu_khi_tai')->nullable(); // Ký hiệu (5P73-VT)
            $table->string('don_vi_co_khi_tai')->nullable(); // Đơn vị
            $table->string('so_hieu')->nullable(); // Số hiệu (№ 9111)
            $table->text('muc_sua_chua')->nullable(); // Mức sửa chữa
            $table->string('don_vi_sua_chua')->nullable(); // Đơn vị sửa chữa (Phân xưởng 8)
            $table->string('ngay_vao_sua_chua')->nullable(); // Ngày vào sửa chữa (3/2025)
            $table->string('du_kien_thoi_gian_sua_chua')->nullable(); // Dự kiến thời gian sửa chữa (03 tháng)

            // Trạng thái
            $table->enum('trang_thai', ['nhap', 'cho_phe_duyet', 'dang_phe_duyet', 'da_phe_duyet', 'tu_choi'])->default('nhap');

            // Người tạo
            $table->foreignId('nguoi_lap_id')->nullable()->constrained('users')->nullOnDelete();

            // Workflow
            $table->json('selected_approvers')->nullable(); // Danh sách người phê duyệt được chọn
            $table->string('workflow_status')->default('pending');
            $table->text('workflow_notes')->nullable();

            // Thông tin phê duyệt
            $table->foreignId('approved_by_director')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_plans');
    }
};

