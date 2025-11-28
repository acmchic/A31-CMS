<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tìm flow_id của vehicle workflow
        $vehicleFlow = DB::table('approval_flows')
            ->where('module_type', 'vehicle')
            ->first();
        
        if (!$vehicleFlow) {
            return;
        }

        // Cập nhật order của director_approval từ 2 thành 3
        DB::table('approval_steps')
            ->where('flow_id', $vehicleFlow->id)
            ->where('step', 'director_approval')
            ->update(['order' => 3]);

        // Thêm step review giữa department_head_approval và director_approval
        DB::table('approval_steps')->insert([
            'flow_id' => $vehicleFlow->id,
            'module_type' => 'vehicle',
            'step' => 'review',
            'step_type' => 'review',
            'order' => 2,
            'is_final' => false,
            'needs_modal' => true, // Cần mở modal chọn người phê duyệt BGD
            'metadata' => json_encode([
                'label' => 'Gửi lên BGD',
                'permission' => 'vehicle_registration.review',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Tìm flow_id của vehicle workflow
        $vehicleFlow = DB::table('approval_flows')
            ->where('module_type', 'vehicle')
            ->first();
        
        if (!$vehicleFlow) {
            return;
        }

        // Xóa step review
        DB::table('approval_steps')
            ->where('flow_id', $vehicleFlow->id)
            ->where('step', 'review')
            ->where('module_type', 'vehicle')
            ->delete();

        // Khôi phục order của director_approval về 2
        DB::table('approval_steps')
            ->where('flow_id', $vehicleFlow->id)
            ->where('step', 'director_approval')
            ->update(['order' => 2]);
    }
};
