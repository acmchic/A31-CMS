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
        Schema::create('an_duong_records', function (Blueprint $table) {
            $table->id();
            $table->integer('stt')->nullable()->comment('Số thứ tự');
            $table->string('ho_va_ten')->nullable()->comment('Họ và tên');
            $table->string('cap_bac')->nullable()->comment('Cấp bậc');
            $table->string('chuc_vu')->nullable()->comment('Chức vụ');
            $table->text('tieu_chuan_duoc_huong')->nullable()->comment('Tiêu chuẩn được hưởng');
            $table->text('ghi_chu')->nullable()->comment('Ghi chú');
            $table->integer('year')->default(date('Y'))->comment('Năm');
            $table->unsignedBigInteger('department_id')->nullable()->comment('ID phòng ban');
            $table->unsignedBigInteger('employee_id')->nullable()->comment('ID nhân viên');
            $table->timestamps();

            // Foreign keys
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');

            // Indexes
            $table->index(['department_id']);
            $table->index(['employee_id']);
            $table->index(['stt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('an_duong_records');
    }
};
