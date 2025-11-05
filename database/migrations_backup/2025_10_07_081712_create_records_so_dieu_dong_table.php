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
        Schema::create('records_so_dieu_dong', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->comment('ID nhân viên');
            $table->unsignedBigInteger('department_id')->comment('ID phòng ban');
            
            // Thông tin từ employees (chỉ lấy Nhap ngu, Chuc vu)
            $table->string('nhap_ngu')->nullable()->comment('Nhập ngũ (mm/yyyy)');
            $table->string('chuc_vu_cnqc')->nullable()->comment('Chức vụ CNQC');
            
            // Các trường mới cho sổ điều động
            $table->string('so_quyet_dinh')->nullable()->comment('Số quyết định');
            $table->date('ngay_quyet_dinh')->nullable()->comment('Ngày quyết định');
            $table->string('nguoi_ky')->nullable()->comment('Người ký');
            $table->string('chuc_vu_nguoi_ky')->nullable()->comment('Chức vụ người ký');
            $table->string('ly_do_dieu_dong')->nullable()->comment('Lý do điều động');
            $table->string('tu_don_vi')->nullable()->comment('Từ đơn vị');
            $table->string('den_don_vi')->nullable()->comment('Đến đơn vị');
            $table->string('chuc_vu_cu')->nullable()->comment('Chức vụ cũ');
            $table->string('chuc_vu_moi')->nullable()->comment('Chức vụ mới');
            $table->date('ngay_hieu_luc')->nullable()->comment('Ngày hiệu lực');
            $table->text('ghi_chu')->nullable()->comment('Ghi chú');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            
            // Indexes
            $table->index(['employee_id', 'department_id']);
            $table->index('ngay_quyet_dinh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records_so_dieu_dong');
    }
};