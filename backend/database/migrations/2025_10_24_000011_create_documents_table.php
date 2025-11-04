<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Documents table with file storage and verification tracking (FR-08)
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            
            // Document metadata
            $table->string('document_name', 100);
            $table->text('description')->nullable();
            
            // File storage (NEW - FR-08 requirement)
            $table->string('file_path', 255)->nullable()->comment('Storage path or URL');
            $table->string('file_name', 255)->nullable()->comment('Original filename');
            $table->string('file_mime_type', 50)->nullable()->comment('e.g., application/pdf');
            $table->bigInteger('file_size')->nullable()->comment('File size in bytes');
            
            // Document status (NEW)
            $table->boolean('is_required')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->text('verification_notes')->nullable();
            
            // Foreign keys
            $table->unsignedBigInteger('internship_id');
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            
            // Upload tracking (NEW)
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Define foreign key constraints
            $table->foreign('internship_id')
                ->references('id')
                ->on('internship')
                ->onDelete('cascade')
                ->onUpdate('cascade');
                
            $table->foreign('document_type_id')
                ->references('id')
                ->on('document_type')
                ->onDelete('cascade')
                ->onUpdate('cascade');
                
            $table->foreign('uploaded_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
                
            $table->foreign('verified_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Indexes for quick document lookup
            $table->index(['internship_id', 'document_type_id']);
            $table->index('uploaded_by_user_id');
            $table->index('verified_by_user_id');
            $table->index(['is_required', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};