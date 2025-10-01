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
        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship to any approvable model
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            
            // Who performed the action
            $table->unsignedBigInteger('user_id');
            
            // Action details
            $table->string('action'); // approved, rejected, cancelled, returned
            $table->integer('level')->default(1); // 1, 2, 3 for multi-level approval
            
            // Workflow status tracking
            $table->string('workflow_status_before')->nullable();
            $table->string('workflow_status_after')->nullable();
            
            // Additional info
            $table->text('comment')->nullable();
            $table->text('reason')->nullable(); // For rejection
            $table->json('metadata')->nullable(); // For any additional data
            
            $table->timestamps();
            
            // Indexes
            $table->index(['approvable_type', 'approvable_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('level');
            $table->index('created_at');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
};


