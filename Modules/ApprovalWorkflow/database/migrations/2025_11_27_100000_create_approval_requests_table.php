<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng tập trung quản lý tất cả các yêu cầu phê duyệt từ các module
 * 
 * ⭐ Ý TƯỞNG: Status chung - Bước duyệt tùy module
 * 
 * Các trạng thái chung cho toàn hệ thống:
 * 1. draft - Nháp (chưa submit)
 * 2. submitted - Đã gửi (chờ xử lý)
 * 3. in_review - Đang xem xét/phê duyệt
 * 4. approved - Đã phê duyệt hoàn tất
 * 5. rejected - Đã từ chối
 * 6. returned - Trả lại (yêu cầu sửa)
 * 7. cancelled - Đã hủy
 * 
 * approval_steps: Các bước duyệt đặc thù của từng module (JSON array)
 * - Leave: ['department_head_approval', 'review', 'director_approval']
 * - Vehicle: ['vehicle_picked', 'director_approval']
 * - MaterialPlan: ['review', 'director_approval']
 * 
 * current_step: Bước hiện tại đang xử lý
 * 
 * Ví dụ:
 * - status = 'in_review', current_step = 'review' → Đang ở bước thẩm định
 * - status = 'in_review', current_step = 'director_approval' → Đang chờ BGD phê duyệt
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            
            // Thông tin module và record
            $table->string('module_type'); // 'leave', 'vehicle', 'material_plan', ...
            $table->string('model_type'); // Class name: EmployeeLeave, VehicleRegistration, MaterialPlan
            $table->unsignedBigInteger('model_id'); // ID của record trong bảng gốc
            
            // Thông tin người tạo
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title'); // Tiêu đề yêu cầu
            $table->text('description')->nullable(); // Mô tả chi tiết
            
            // Workflow - Status chung cho toàn hệ thống
            $table->enum('status', [
                'draft',        // Nháp
                'submitted',    // Đã gửi
                'in_review',    // Đang xem xét/phê duyệt
                'approved',     // Đã phê duyệt
                'rejected',     // Đã từ chối
                'returned',     // Trả lại (yêu cầu sửa)
                'cancelled'     // Đã hủy
            ])->default('draft');
            
            // Approval steps - Các bước duyệt đặc thù của từng module (JSON)
            // Ví dụ: ['vehicle_picked', 'reviewed', 'director_approved']
            $table->json('approval_steps')->nullable();
            
            // Current step - Bước hiện tại đang xử lý
            $table->string('current_step')->nullable();
            
            // Người phê duyệt được chọn cho từng step (JSON)
            // Format: {"step_name": [user_id1, user_id2], ...}
            $table->json('selected_approvers')->nullable();
            
            // Thông tin phê duyệt theo step (JSON)
            // Format: {"step_name": {"approved_by": user_id, "approved_at": timestamp, "comment": "...", "signature_path": "..."}, ...}
            $table->json('approval_history')->nullable();
            
            // Từ chối/Trả lại
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('rejection_step')->nullable(); // Step nào bị từ chối
            
            // PDF và chữ ký số
            $table->string('signed_pdf_path')->nullable();
            $table->string('template_pdf_path')->nullable();
            
            // Metadata (JSON) - lưu thông tin bổ sung từ module gốc
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['module_type', 'model_type', 'model_id']);
            $table->index('status');
            $table->index('current_step');
            $table->index('created_by');
            $table->index('rejected_by');
            $table->index('created_at');
            
            // Unique constraint: một model chỉ có 1 approval request active
            $table->unique(['model_type', 'model_id'], 'unique_model_approval');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};

