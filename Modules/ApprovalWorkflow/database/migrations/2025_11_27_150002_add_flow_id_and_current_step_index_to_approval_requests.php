<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm flow_id và current_step_index vào approval_requests
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->foreignId('flow_id')->nullable()->after('module_type')->constrained('approval_flows')->onDelete('set null');
            $table->integer('current_step_index')->nullable()->after('current_step');
            $table->index('flow_id');
            $table->index('current_step_index');
        });
    }

    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropForeign(['flow_id']);
            $table->dropIndex(['flow_id']);
            $table->dropIndex(['current_step_index']);
            $table->dropColumn(['flow_id', 'current_step_index']);
        });
    }
};

