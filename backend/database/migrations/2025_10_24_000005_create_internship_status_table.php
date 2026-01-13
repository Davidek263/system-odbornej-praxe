<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internship Status Table Migration
 *
 * Creates the internship_status table for tracking the lifecycle of internships.
 * Internships progress through multiple states from creation to completion,
 * with each status representing a significant milestone in the process.
 *
 * Purpose:
 * - Define possible states for internship lifecycle (FR-05, FR-06, FR-07)
 * - Enable status-based filtering and reporting
 * - Track internship progression through approval workflow
 * - Support business logic for status transitions
 *
 * Expected Statuses:
 * - Vytvorená (Created): Initial state when internship is registered
 * - Potvrdená (Confirmed): Company has confirmed the internship
 * - Schválená (Approved): Guarantor has approved the internship
 * - Obhájená (Defended): Student successfully defended/completed internship
 * - Neobhájená (Not Defended): Student failed to defend internship
 *
 * Key Columns:
 * - internship_status_name: Unique status identifier (e.g., "Vytvorená", "Schválená")
 * - description: Explanation of what the status represents
 * - order: Numeric order for displaying status progression (0-100)
 *
 * Relationships:
 * - Referenced by internship.current_status_id (current state)
 * - Referenced by internship_status_change.internship_status_id (status history)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('internship_status', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();
            $table->string('internship_status_name', 100)->unique(); // Unique status name
            $table->string('description', 255)->nullable(); // Status description
            $table->integer('order')->default(0)->comment('Display order for status progression'); // Sequence order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('internship_status');
    }
};
