<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timesheet Status History Table Migration
 *
 * Creates the timesheet_status_history table for tracking approval workflow
 * of timesheet documents. Timesheets (výkaz) require approval from both the
 * company and the guarantor, and this table maintains the complete audit trail.
 *
 * Purpose:
 * - Track timesheet approval/rejection history (FR-08)
 * - Maintain audit trail for timesheet workflow
 * - Record who approved or rejected timesheets and when
 * - Preserve notes/reasons for approval decisions
 * - Support reporting on timesheet processing times
 *
 * Key Features:
 * - Chronological history of status changes for each timesheet
 * - User attribution for accountability
 * - Timestamp tracking with default to current time
 * - Optional notes for explaining decisions
 * - Indexed for efficient history queries
 *
 * Key Columns:
 * - status_changed_at: When the status change occurred
 * - changed_by_user_id: User who performed the action (nullable for system changes)
 * - timesheet_status_id: The new status that was set
 * - documents_id: Which timesheet document this applies to
 * - notes: Optional explanation or feedback
 *
 * Relationships:
 * - documents: Many-to-one (history entries belong to one timesheet document)
 * - timesheet_status: Many-to-one (each change references a status)
 * - users: Many-to-one (who made the change)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('timesheet_status_history', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();
            $table->timestamp('status_changed_at')->useCurrent(); // When the change occurred
            $table->text('notes')->nullable(); // Optional explanation or feedback
            $table->timestamps();

            // Foreign key columns
            $table->unsignedBigInteger('changed_by_user_id')->nullable(); // Who made the change
            $table->unsignedBigInteger('timesheet_status_id'); // The new status
            $table->unsignedBigInteger('documents_id'); // Which timesheet document

            // ==================== FOREIGN KEYS ====================
            // User who performed the status change (nullable for system changes)
            $table->foreign('changed_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // Preserve history even if user deleted

            // The status that was set
            $table->foreign('timesheet_status_id')
                ->references('id')
                ->on('timesheet_status')
                ->onDelete('cascade') // Delete history if status deleted
                ->onUpdate('cascade');

            // The timesheet document this change applies to
            $table->foreign('documents_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade') // Delete history with document
                ->onUpdate('cascade');

            // ==================== INDEXES ====================
            $table->index(['documents_id', 'status_changed_at']); // Chronological history per timesheet
            $table->index('changed_by_user_id'); // Find all changes by a user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('timesheet_status_history');
    }
};