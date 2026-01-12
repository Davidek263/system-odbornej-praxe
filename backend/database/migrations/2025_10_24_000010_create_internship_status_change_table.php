<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internship Status Change Table Migration
 *
 * Creates the internship_status_change table for maintaining a complete audit trail
 * of all status transitions for each internship. This table provides historical tracking
 * and enables reporting on workflow progression.
 *
 * Purpose:
 * - Track complete status change history for internships (FR-06, FR-07)
 * - Maintain audit trail of who changed status and when
 * - Support timeline views of internship progression
 * - Enable reporting on workflow bottlenecks
 * - Preserve notes/reasons for status changes
 *
 * Key Features:
 * - Chronological history of all status changes
 * - User attribution (who made the change)
 * - Timestamp tracking with default to current time
 * - Optional notes for explaining status changes
 * - Indexed for efficient history queries
 *
 * Key Columns:
 * - status_changed_at: When the status change occurred
 * - changed_by_user_id: User who performed the status change (nullable for system changes)
 * - internship_status_id: The new status that was set
 * - internship_id: Which internship this change applies to
 * - notes: Optional explanation or reason for the change
 *
 * Relationships:
 * - internship: Many-to-one (history entries belong to one internship)
 * - internship_status: Many-to-one (each change references a status)
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
        Schema::create('internship_status_change', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();
            $table->timestamp('status_changed_at')->useCurrent(); // When the change occurred
            $table->text('notes')->nullable(); // Optional explanation for the change
            $table->timestamps();

            // Foreign key columns
            $table->unsignedBigInteger('changed_by_user_id')->nullable(); // Who made the change
            $table->unsignedBigInteger('internship_status_id'); // The new status
            $table->unsignedBigInteger('internship_id'); // Which internship

            // ==================== FOREIGN KEYS ====================
            // User who performed the status change (nullable for system changes)
            $table->foreign('changed_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null') // Preserve history even if user deleted
                ->onUpdate('cascade');

            // The status that was set
            $table->foreign('internship_status_id')
                ->references('id')
                ->on('internship_status')
                ->onDelete('cascade') // Delete history if status deleted
                ->onUpdate('cascade');

            // The internship this change applies to
            $table->foreign('internship_id')
                ->references('id')
                ->on('internship')
                ->onDelete('cascade') // Delete history with internship
                ->onUpdate('cascade');

            // ==================== INDEXES ====================
            $table->index(['internship_id', 'status_changed_at']); // Chronological history per internship
            $table->index('changed_by_user_id'); // Find all changes by a user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('internship_status_change');
    }
};