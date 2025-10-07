<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records_quan_nhan', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();

            // Thông tin cá nhân bổ sung
            $table->string('ho_ten_thuong_dung')->nullable()->comment('Họ tên thường dùng');
            $table->string('so_hieu_quan_nhan')->nullable()->comment('Số hiệu quân nhân');
            $table->string('so_the_QN')->nullable()->comment('Số thẻ quân nhân');

            // Thông tin quân đội (các field mới, không có trong employees)
            $table->string('cap_bac')->nullable();
            $table->date('ngay_nhan_cap')->nullable();
            $table->date('ngay_cap_cc')->nullable()->comment('Ngày cấp Chứng minh, thẻ, CC');
            $table->string('cnqs')->nullable();
            $table->string('bac_ky_thuat')->nullable();
            $table->string('tai_ngu')->nullable();
            $table->date('ngay_chuyen_qncn')->nullable();
            $table->date('ngay_chuyen_cnv')->nullable();
            $table->string('luong_nhom_ngach_bac')->nullable()->comment('Lương: nhóm ngạch bậc');

            // Thông tin chính trị
            $table->date('ngay_vao_doan')->nullable();
            $table->date('ngay_vao_dang')->nullable();
            $table->date('ngay_chinh_thuc')->nullable()->comment('Ngày chính thức Đảng');

            // Thành phần
            $table->string('tp_gia_dinh')->nullable()->comment('Thành phần gia đình');
            $table->string('tp_ban_than')->nullable()->comment('Thành phần bản thân');
            $table->string('dan_toc')->nullable();
            $table->string('ton_giao')->nullable();

            // Trình độ
            $table->string('van_hoa')->nullable()->comment('Trình độ văn hóa');
            $table->string('ngoai_ngu')->nullable();
            $table->string('suc_khoe')->nullable();
            $table->string('hang_thuong_tru')->nullable();
            $table->string('khu_vuc')->nullable();

            // Khen thưởng - Kỷ luật
            $table->text('khen_thuong')->nullable();
            $table->text('ky_luat')->nullable();

            // Đào tạo
            $table->string('ten_truong')->nullable()->comment('Tên trường');
            $table->string('cap_hoc')->nullable()->comment('Cấp học: ĐH, CĐ, TC...');
            $table->string('nganh_hoc')->nullable();
            $table->string('thoi_gian_hoc')->nullable()->comment('VD: 2018-2022');

            // Nguồn
            $table->string('nguon_quan')->nullable()->comment('Sinh quân/Trở quân');

            // Liên hệ khẩn cấp
            $table->text('bao_tin')->nullable()->comment('Khi cần báo tin cho ai? ở đâu?');

            // Thông tin gia đình
            $table->string('ho_ten_cha')->nullable();
            $table->string('ho_ten_me')->nullable();
            $table->string('ho_ten_vo_chong')->nullable();
            $table->string('may_con')->nullable();

            // Ghi chú
            $table->text('ghi_chu')->nullable();

            // Metadata
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('employee_id');
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records_quan_nhan');
    }
};
