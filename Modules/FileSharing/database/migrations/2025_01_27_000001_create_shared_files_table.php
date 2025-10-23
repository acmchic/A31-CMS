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
        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name'); // Tên file gốc
            $table->string('file_name'); // Tên file đã lưu trên server
            $table->string('file_path'); // Đường dẫn file
            $table->string('file_extension'); // Phần mở rộng file
            $table->bigInteger('file_size'); // Kích thước file (bytes)
            $table->string('mime_type'); // Loại MIME
            $table->text('description')->nullable(); // Mô tả file
            $table->string('category')->nullable(); // Danh mục file
            $table->json('tags')->nullable(); // Tags cho file
            $table->boolean('is_public')->default(false); // File công khai hay không
            $table->json('allowed_roles')->nullable(); // Các role được phép truy cập
            $table->json('allowed_users')->nullable(); // Các user được phép truy cập
            $table->integer('download_count')->default(0); // Số lần download
            $table->timestamp('expires_at')->nullable(); // Thời gian hết hạn
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // Người upload
            $table->timestamps();
            
            // Indexes
            $table->index(['uploaded_by']);
            $table->index(['category']);
            $table->index(['is_public']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_files');
    }
};
