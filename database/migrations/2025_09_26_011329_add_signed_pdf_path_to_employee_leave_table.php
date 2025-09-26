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
        Schema::table('employee_leave', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_leave', 'signed_pdf_path')) {
                $table->string('signed_pdf_path')->nullable()->after('director_signature_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave', function (Blueprint $table) {
            if (Schema::hasColumn('employee_leave', 'signed_pdf_path')) {
                $table->dropColumn('signed_pdf_path');
            }
        });
    }
};
