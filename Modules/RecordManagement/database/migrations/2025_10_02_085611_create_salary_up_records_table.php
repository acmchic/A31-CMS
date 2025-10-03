<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_up_records', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys - BẮT BUỘC
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->integer('year')->default(date('Y'));
            
            // Thông tin cá nhân
            $table->string('ho_ten');
            $table->string('nhap_ngu', 20)->nullable()->comment('Nhập ngũ (TĐ) - Format: mm/yyyy');
            $table->string('chuc_vu')->nullable()->comment('Chức vụ (CNQS)');
            
            // Lương hiện hưởng
            $table->string('luong_hien_loai_nhom')->nullable()->comment('Loại nhóm (MS)');
            $table->integer('luong_hien_bac')->nullable()->comment('Bậc L');
            $table->decimal('luong_hien_he_so', 5, 2)->nullable()->comment('Hệ số');
            $table->decimal('luong_hien_phan_tram_tn_vk', 5, 2)->nullable()->comment('% TN VK');
            $table->decimal('luong_hien_he_so_bl', 5, 2)->nullable()->comment('Hệ số BL');
            $table->string('luong_hien_quan_ham')->nullable()->comment('Quân hàm QN');
            $table->string('luong_hien_thang_nhan')->nullable()->comment('Tháng nhận bổ nhiệm');
            
            // Xếp lương mới
            $table->string('luong_moi_loai_nhom')->nullable()->comment('Loại nhóm (MS)');
            $table->integer('luong_moi_bac')->nullable()->comment('Bậc L');
            $table->decimal('luong_moi_he_so', 5, 2)->nullable()->comment('Hệ số');
            $table->decimal('luong_moi_phan_tram_tn_vk', 5, 2)->nullable()->comment('% TN VK');
            $table->decimal('luong_moi_he_so_bl', 5, 2)->nullable()->comment('Hệ số BL');
            $table->string('luong_moi_thang_qd_huong')->nullable()->comment('Tháng quân đội hưởng');
            $table->string('luong_moi_thang_nam_nhan')->nullable()->comment('Tháng năm nhận QNCN');
            
            // Thông tin khác
            $table->string('don_vi')->nullable()->comment('Đơn vị (Phòng, Ban, PX)');
            $table->text('ghi_chu')->nullable();
            
            // Metadata
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('year');
            $table->index('employee_id');
            $table->index('department_id');
            $table->index(['year', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_up_records');
    }
};
