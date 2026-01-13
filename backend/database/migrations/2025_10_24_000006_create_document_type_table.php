<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Document Type Table Migration
 *
 * Creates the document_type table for defining categories of documents in the system.
 * Document types represent the different kinds of files that must be uploaded and
 * managed throughout the internship lifecycle.
 *
 * Purpose:
 * - Define document categories for internships (FR-08)
 * - Specify which documents are mandatory
 * - Track when documents are required in the workflow
 * - Support document validation and completeness checking
 *
 * Expected Document Types:
 * - Dohoda (Agreement): School-generated internship agreement
 * - Zmluva (Contract): Employer contract for jobs/brigades
 * - Výkaz (Timesheet): Hours worked documentation
 *
 * Key Columns:
 * - document_type_name: Unique name for the document type
 * - description: Explanation of the document type's purpose
 * - is_required: Whether this document is mandatory for completion
 * - required_at_status: Which internship status requires this document
 *
 * Relationships:
 * - Referenced by documents.document_type_id
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('document_type', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Explicitly set InnoDB for foreign key support

            // ==================== COLUMNS ====================
            $table->id();
            $table->string('document_type_name', 100)->unique(); // Unique document type name
            $table->string('description', 255)->nullable(); // Purpose/explanation
            $table->boolean('is_required')->default(false)->comment('Is this document mandatory?'); // Mandatory flag
            $table->string('required_at_status', 100)->nullable()->comment('Required at which internship status?'); // Status requirement
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('document_type');
    }
};
