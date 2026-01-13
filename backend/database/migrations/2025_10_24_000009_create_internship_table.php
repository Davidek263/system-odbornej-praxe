<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internship Table Migration
 *
 * Creates the core internship table that represents individual internship records.
 * Each internship links a student to a company for a specific time period and
 * tracks the current approval status through the workflow.
 *
 * Purpose:
 * - Store internship records for students (FR-05, FR-06, FR-07)
 * - Track internship dates and academic context
 * - Link students to companies for specific time periods
 * - Maintain current status for efficient filtering and reporting
 * - Support multi-semester and multi-year internship tracking
 *
 * Key Features:
 * - Academic year and semester tracking
 * - Date-based internship duration (date type, not string)
 * - Current status for optimized queries (denormalized for performance)
 * - Soft deletes for data archiving
 * - Comprehensive indexing for filtering (FR-07)
 *
 * Key Columns:
 * - academic_year: Format YYYY/YYYY (e.g., "2024/2025")
 * - semester: 1 = winter semester, 2 = summer semester
 * - date_start, date_end: Actual internship duration (proper date type)
 * - current_status_id: Cached current status for efficient queries
 * - users_id: Student performing the internship
 * - company_id: Host organization
 *
 * Relationships:
 * - users: Many-to-one with users (the student)
 * - company: Many-to-one with company (the host organization)
 * - current_status: Many-to-one with internship_status (current state)
 * - Referenced by documents (internship documents)
 * - Referenced by internship_status_change (status history)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('internship', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();

            // Academic context (FR-05)
            $table->string('academic_year', 9)->comment('Format: YYYY/YYYY, e.g., 2024/2025'); // Academic year
            $table->tinyInteger('semester')->comment('1 = winter, 2 = summer'); // Semester indicator

            // Internship duration (proper date type)
            $table->date('date_start'); // Internship start date
            $table->date('date_end'); // Internship end date

            $table->timestamps();
            $table->softDeletes(); // Soft delete for archiving historical data

            // ==================== FOREIGN KEYS ====================
            // Current status (denormalized for performance)
            $table->foreignId('current_status_id')
                ->nullable()
                ->constrained('internship_status')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Student performing the internship
            $table->foreignId('users_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Host company/organization
            $table->foreignId('company_id')
                ->constrained('company')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // ==================== INDEXES ====================
            // Performance indexes for filtering and reporting (FR-07)
            $table->index(['users_id', 'academic_year']); // Student + year filtering
            $table->index(['company_id', 'semester']); // Company + semester filtering
            $table->index('academic_year'); // Year-based filtering
            $table->index('current_status_id'); // Status-based filtering
            $table->index(['academic_year', 'semester']); // Combined year + semester filter
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('internship');
    }
};
