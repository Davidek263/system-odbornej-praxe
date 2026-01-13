<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit Logs Table Migration
 *
 * Creates the audit_logs table for comprehensive tracking of all important
 * changes in the system. This table provides full transparency and accountability
 * for data modifications, supporting compliance and security requirements.
 *
 * Purpose:
 * - Track all important changes in the system (recommended for transparency)
 * - Maintain complete audit trail for compliance
 * - Support security investigations and incident response
 * - Enable change rollback by preserving old values
 * - Track user activity for analytics and debugging
 *
 * Key Features:
 * - Before/after value comparison (old_values vs new_values)
 * - Request context (IP address, user agent) for security
 * - Flexible model tracking (works with any entity type)
 * - User attribution with null support for system changes
 * - Comprehensive indexing for audit queries
 *
 * Tracked Actions:
 * - create: New record created
 * - update: Existing record modified
 * - delete: Record deleted or soft-deleted
 * - status_change: Status field changed
 * - login: User authentication event
 * - logout: User session ended
 *
 * Key Columns:
 * - user_id: Who performed the action (nullable for system actions)
 * - action: Type of operation (create, update, delete, etc.)
 * - model_type: Entity class name (User, Internship, Document, etc.)
 * - model_id: ID of the affected entity
 * - old_values: JSON snapshot of values before change
 * - new_values: JSON snapshot of values after change
 * - ip_address: Client IP for security tracking (IPv6 compatible)
 * - user_agent: Browser/client information
 *
 * Relationships:
 * - users: Many-to-one (who performed the action)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('audit_logs', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();

            // Action details
            $table->string('action', 50)->comment('create, update, delete, status_change, etc.'); // Type of operation
            $table->string('model_type', 50)->comment('User, Internship, Document, etc.'); // Entity class name
            $table->unsignedBigInteger('model_id'); // ID of affected entity

            // Change tracking
            $table->json('old_values')->nullable(); // Values before change (for updates/deletes)
            $table->json('new_values')->nullable(); // Values after change (for creates/updates)

            // Request context for security
            $table->string('ip_address', 45)->nullable(); // Client IP (IPv6 compatible)
            $table->text('user_agent')->nullable(); // Browser/client information

            $table->timestamps();

            // Foreign key column
            $table->unsignedBigInteger('user_id')->nullable(); // Who performed the action

            // ==================== FOREIGN KEYS ====================
            // User who performed the action (nullable for system actions)
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // Preserve audit log even if user deleted

            // ==================== INDEXES ====================
            $table->index(['model_type', 'model_id']); // Find all changes to a specific entity
            $table->index('user_id'); // Find all actions by a user
            $table->index(['action', 'created_at']); // Find specific actions in time range
            $table->index('created_at'); // Chronological queries and cleanup
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('audit_logs');
    }
};