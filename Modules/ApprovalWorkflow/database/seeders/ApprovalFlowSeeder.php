<?php

namespace Modules\ApprovalWorkflow\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ApprovalWorkflow\Models\ApprovalFlow;
use Modules\ApprovalWorkflow\Models\ApprovalStep;

/**
 * Seeder cho approval_flows và approval_steps
 * 
 * Seed dữ liệu workflow metadata cho các module: leave, vehicle
 */
class ApprovalFlowSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // LEAVE MODULE WORKFLOW
        // ============================================
        $leaveFlow = ApprovalFlow::create([
            'module_type' => 'leave',
            'name' => 'Quy trình phê duyệt đơn nghỉ phép',
            'description' => 'Flow: TP duyệt → Review → BGĐ duyệt → Hoàn tất',
        ]);

        // Step 0: department_head_approval
        ApprovalStep::create([
            'flow_id' => $leaveFlow->id,
            'module_type' => 'leave',
            'step' => 'department_head_approval',
            'step_type' => 'approval',
            'order' => 0,
            'is_final' => false,
            'needs_modal' => false,
            'metadata' => [
                'label' => 'Trưởng phòng duyệt',
                'role' => 'department_head',
            ],
        ]);

        // Step 1: review
        ApprovalStep::create([
            'flow_id' => $leaveFlow->id,
            'module_type' => 'leave',
            'step' => 'review',
            'step_type' => 'review',
            'order' => 1,
            'is_final' => false,
            'needs_modal' => false,
            'metadata' => [
                'label' => 'Thẩm định',
                'permission' => 'leave.review',
            ],
        ]);

        // Step 2: director_approval (final)
        ApprovalStep::create([
            'flow_id' => $leaveFlow->id,
            'module_type' => 'leave',
            'step' => 'director_approval',
            'step_type' => 'approval',
            'order' => 2,
            'is_final' => true,
            'needs_modal' => false,
            'metadata' => [
                'label' => 'Ban giám đốc duyệt',
                'role' => 'director',
            ],
        ]);

        // ============================================
        // VEHICLE MODULE WORKFLOW
        // ============================================
        $vehicleFlow = ApprovalFlow::create([
            'module_type' => 'vehicle',
            'name' => 'Quy trình phê duyệt đăng ký xe',
            'description' => 'Flow: vehicle_picked → TP duyệt → [Modal chọn BGĐ] → BGĐ duyệt → Hoàn tất',
        ]);

        // Step 0: vehicle_picked
        ApprovalStep::create([
            'flow_id' => $vehicleFlow->id,
            'module_type' => 'vehicle',
            'step' => 'vehicle_picked',
            'step_type' => 'special',
            'order' => 0,
            'is_final' => false,
            'needs_modal' => false,
            'metadata' => [
                'label' => 'Đã chọn xe',
            ],
        ]);

        // Step 1: department_head_approval
        ApprovalStep::create([
            'flow_id' => $vehicleFlow->id,
            'module_type' => 'vehicle',
            'step' => 'department_head_approval',
            'step_type' => 'approval',
            'order' => 1,
            'is_final' => false,
            'needs_modal' => true, // ⚠️ Cần mở modal chọn BGĐ sau khi duyệt
            'metadata' => [
                'label' => 'Trưởng phòng KH duyệt',
                'role' => 'department_head',
                'next_step_requires_modal' => true,
            ],
        ]);

        // Step 2: director_approval (final)
        ApprovalStep::create([
            'flow_id' => $vehicleFlow->id,
            'module_type' => 'vehicle',
            'step' => 'director_approval',
            'step_type' => 'approval',
            'order' => 2,
            'is_final' => true,
            'needs_modal' => false,
            'metadata' => [
                'label' => 'Ban giám đốc duyệt',
                'role' => 'director',
            ],
        ]);

        $this->command->info('✅ Seeded approval_flows and approval_steps for leave and vehicle modules');
    }
}

