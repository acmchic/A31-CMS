<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove workflow-related fields from vehicle_registrations table
     * These fields are now managed in approval_requests table
     */
    public function up(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            // Remove workflow status fields (now in approval_requests.status)
            if (Schema::hasColumn('vehicle_registrations', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
            if (Schema::hasColumn('vehicle_registrations', 'status')) {
                $table->dropColumn('status');
            }

            // Remove approval fields (now in approval_requests.approval_history)
            if (Schema::hasColumn('vehicle_registrations', 'department_approved_by')) {
                $table->dropColumn('department_approved_by');
            }
            if (Schema::hasColumn('vehicle_registrations', 'department_approved_at')) {
                $table->dropColumn('department_approved_at');
            }
            if (Schema::hasColumn('vehicle_registrations', 'director_approved_by')) {
                $table->dropColumn('director_approved_by');
            }
            if (Schema::hasColumn('vehicle_registrations', 'director_approved_at')) {
                $table->dropColumn('director_approved_at');
            }

            // Remove digital signature fields (now in approval_requests.approval_history)
            if (Schema::hasColumn('vehicle_registrations', 'digital_signature_dept')) {
                $table->dropColumn('digital_signature_dept');
            }
            if (Schema::hasColumn('vehicle_registrations', 'digital_signature_director')) {
                $table->dropColumn('digital_signature_director');
            }

            // Remove rejection fields (now in approval_requests)
            if (Schema::hasColumn('vehicle_registrations', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('vehicle_registrations', 'rejection_level')) {
                $table->dropColumn('rejection_level');
            }

            // Remove selected_approvers (now in approval_requests.selected_approvers)
            if (Schema::hasColumn('vehicle_registrations', 'selected_approvers')) {
                $table->dropColumn('selected_approvers');
            }

            // Remove signed_pdf_path (now in approval_requests.signed_pdf_path)
            if (Schema::hasColumn('vehicle_registrations', 'signed_pdf_path')) {
                $table->dropColumn('signed_pdf_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            // Restore workflow status fields
            $table->enum('workflow_status', ['submitted', 'dept_review', 'director_review', 'approved', 'rejected'])->default('submitted')->after('vehicle_id');
            $table->enum('status', ['pending', 'dept_approved', 'approved', 'rejected'])->default('pending')->after('workflow_status');

            // Restore approval fields
            $table->foreignId('department_approved_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->timestamp('department_approved_at')->nullable()->after('department_approved_by');
            $table->foreignId('director_approved_by')->nullable()->constrained('users')->onDelete('set null')->after('department_approved_at');
            $table->timestamp('director_approved_at')->nullable()->after('director_approved_by');

            // Restore digital signature fields
            $table->text('digital_signature_dept')->nullable()->after('director_approved_at');
            $table->text('digital_signature_director')->nullable()->after('digital_signature_dept');

            // Restore rejection fields
            $table->text('rejection_reason')->nullable()->after('digital_signature_director');
            $table->enum('rejection_level', ['department', 'director'])->nullable()->after('rejection_reason');

            // Restore selected_approvers
            $table->json('selected_approvers')->nullable()->after('rejection_level');

            // Restore signed_pdf_path
            $table->string('signed_pdf_path')->nullable()->after('selected_approvers');
        });
    }
};



