<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng approval_flows: Quản lý metadata workflow cho từng module
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->string('module_type'); // 'leave', 'vehicle', 'material', ...
            $table->string('name'); // Tên workflow
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('module_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_flows');
    }
};

