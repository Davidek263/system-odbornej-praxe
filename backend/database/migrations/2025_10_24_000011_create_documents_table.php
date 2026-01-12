<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents Table Migration
 *
 * Creates the documents table for managing all files and documents associated with
 * internships. This includes agreements, contracts, timesheets, and other required
 * documentation throughout the internship lifecycle.
 *
 * Purpose:
 * - Store document metadata and file information (FR-08)
 * - Track document upload and verification workflow
 * - Link documents to specific internships
 * - Support multi-party document approval process
 * - Enable document completeness checking
 *
 * Key Features:
 * - Complete file metadata (name, path, type, size)
 * - Verification workflow with approver tracking
 * - Upload and verification timestamps
 * - Soft deletes for document retention
 * - Comprehensive indexing for queries
 *
 * Key Columns:
 * - document_name: User-friendly document name
 * - file_path: Storage path or URL to the actual file
 * - file_name: Original filename when uploaded
 * - file_mime_type: MIME type (e.g., "application/pdf")
 * - file_size: File size in bytes for storage management
 * - is_required: Whether this document is mandatory
 * - is_verified: Whether document has been approved
 * - verification_notes: Comments from verifier
 * - uploaded_at: When the file was uploaded
 * - verified_at: When the document was verified/approved
 *
 * Relationships:
 * - internship: Many-to-one (documents belong to one internship)
 * - document_type: Many-to-one (categorizes the document)
 * - uploaded_by_user: Many-to-one (who uploaded the file)
 * - verified_by_user: Many-to-one (who verified/approved it)
 * - Referenced by timesheet_status_history (for timesheet documents)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('documents', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();

            // Document metadata
            $table->string('document_name', 100); // User-friendly document name
            $table->text('description')->nullable(); // Additional details about the document

            // File storage information (FR-08 requirement)
            $table->string('file_path', 255)->nullable()->comment('Storage path or URL'); // Where file is stored
            $table->string('file_name', 255)->nullable()->comment('Original filename'); // Original name when uploaded
            $table->string('file_mime_type', 50)->nullable()->comment('e.g., application/pdf'); // File type
            $table->bigInteger('file_size')->nullable()->comment('File size in bytes'); // Size for storage management

            // Document verification status
            $table->boolean('is_required')->default(false); // Is this document mandatory?
            $table->boolean('is_verified')->default(false); // Has it been approved?
            $table->text('verification_notes')->nullable(); // Comments from verifier

            // Timestamps for tracking
            $table->timestamp('uploaded_at')->nullable(); // When file was uploaded
            $table->timestamp('verified_at')->nullable(); // When document was approved

            $table->timestamps();
            $table->softDeletes(); // Soft delete for document retention

            // Foreign key columns
            $table->unsignedBigInteger('internship_id'); // Which internship
            $table->unsignedBigInteger('document_type_id'); // Type of document
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable(); // Who uploaded it
            $table->unsignedBigInteger('verified_by_user_id')->nullable(); // Who verified it

            // ==================== FOREIGN KEYS ====================
            // Internship this document belongs to
            $table->foreign('internship_id')
                ->references('id')
                ->on('internship')
                ->onDelete('cascade') // Delete documents with internship
                ->onUpdate('cascade');

            // Document type/category
            $table->foreign('document_type_id')
                ->references('id')
                ->on('document_type')
                ->onDelete('cascade') // Delete if document type removed
                ->onUpdate('cascade');

            // User who uploaded the document
            $table->foreign('uploaded_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // Preserve document even if uploader deleted

            // User who verified/approved the document
            $table->foreign('verified_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // Preserve verification even if verifier deleted

            // ==================== INDEXES ====================
            $table->index(['internship_id', 'document_type_id']); // Find specific document type for internship
            $table->index('uploaded_by_user_id'); // Find all documents uploaded by user
            $table->index('verified_by_user_id'); // Find all documents verified by user
            $table->index(['is_required', 'is_verified']); // Find incomplete required documents
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('documents');
    }
};