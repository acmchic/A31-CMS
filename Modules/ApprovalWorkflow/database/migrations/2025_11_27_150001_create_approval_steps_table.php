<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng approval_steps: Quản lý các bước duyệt cho từng workflow
 * 
 * TẤT CẢ step của mọi module đều nằm trong bảng này,
 * phân biệt bằng module_type + order
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('approval_flows')->onDelete('cascade');
            $table->string('module_type'); // 'leave', 'vehicle', 'material', ...
            $table->string('step'); // 'department_head_approval', 'review', 'director_approval', ...
            $table->string('step_type'); // 'approval', 'review', 'selection', 'modal', 'special'
            $table->integer('order'); // Thứ tự bước (0, 1, 2, ...)
            $table->boolean('is_final')->default(false); // Bước cuối cùng
            $table->boolean('needs_modal')->default(false); // Cần mở modal chọn người duyệt
            $table->json('metadata')->nullable(); // Thông tin bổ sung
            $table->timestamps();
            
            // Indexes
            $table->index(['module_type', 'order']);
            $table->index('flow_id');
            $table->index('step');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};

