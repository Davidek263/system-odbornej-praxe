<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timesheet Status Table Migration
 *
 * Creates the timesheet_status table for tracking the approval state of timesheets (výkaz).
 * Timesheets document the hours worked during an internship and must be approved
 * by both the company and the guarantor.
 *
 * Purpose:
 * - Define possible states for timesheet documents (FR-08)
 * - Track timesheet approval workflow
 * - Enable status-based filtering of timesheets
 * - Support multi-party approval process
 *
 * Expected Statuses:
 * - Vytvorený (Created): Initial state when timesheet is uploaded
 * - Na schválenie (Pending Approval): Awaiting review
 * - Schválený (Approved): Approved by company and/or guarantor
 * - Zamietnutý (Rejected): Rejected and requires revision
 *
 * Key Columns:
 * - timesheet_status_name: Unique status identifier
 * - description: Explanation of the status meaning
 *
 * Relationships:
 * - Referenced by timesheet_status_history.timesheet_status_id
 * - Used to track approval state of timesheet documents
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('timesheet_status', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();
            $table->string('timesheet_status_name', 100)->unique(); // Unique status identifier
            $table->string('description', 255)->nullable(); // Status description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('timesheet_status');
    }
};
