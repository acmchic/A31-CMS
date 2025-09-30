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
        Schema::table('users', function (Blueprint $table) {
            $table->string('certificate_pin')->nullable()->after('signature_path')
                  ->comment('PIN riêng cho chữ ký số của user');
            $table->string('certificate_path')->nullable()->after('certificate_pin')
                  ->comment('Đường dẫn đến file certificate .pfx riêng của user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['certificate_pin', 'certificate_path']);
        });
    }
};