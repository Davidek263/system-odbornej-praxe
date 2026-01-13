<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email Notifications Table Migration
 *
 * Creates the email_notifications table for tracking all email communications
 * sent by the system. This table serves as both a queue for pending emails
 * and an archive of sent notifications.
 *
 * Purpose:
 * - Queue and track email notifications (FR-06, FR-07)
 * - Support email retry mechanism for failed sends
 * - Maintain audit trail of all system communications
 * - Enable debugging of email delivery issues
 * - Track notification history per user
 *
 * Key Features:
 * - Polymorphic relationship to related entities (internship, document, etc.)
 * - Retry mechanism with error tracking
 * - Delivery status tracking
 * - Comprehensive indexing for queue processing
 *
 * Expected Notification Types:
 * - status_change: Internship status changed
 * - registration: New user registered
 * - activation: Account activation link
 * - password_reset: Password reset request
 * - document_uploaded: New document available for review
 * - document_verified: Document approved/rejected
 *
 * Key Columns:
 * - user_id: Recipient of the notification
 * - type: Category of notification (status_change, registration, etc.)
 * - subject: Email subject line
 * - body: Email content (HTML or plain text)
 * - related: Polymorphic link to related entity (internship, document, etc.)
 * - is_sent: Whether email was successfully sent
 * - sent_at: When email was delivered
 * - error_message: Error details if sending failed
 * - retry_count: Number of send attempts
 *
 * Relationships:
 * - users: Many-to-one (recipient of the notification)
 * - related: Polymorphic to any entity (internship, document, etc.)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('email_notifications', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();

            // Notification content
            $table->string('type', 50)->comment('status_change, registration, activation, etc.'); // Notification type
            $table->string('subject', 255); // Email subject line
            $table->text('body'); // Email content (HTML or plain text)

            // Delivery status
            $table->boolean('is_sent')->default(false); // Was email successfully sent?
            $table->timestamp('sent_at')->nullable(); // When email was delivered
            $table->text('error_message')->nullable(); // Error details if sending failed
            $table->tinyInteger('retry_count')->default(0); // Number of send attempts

            $table->timestamps();

            // Foreign key column
            $table->unsignedBigInteger('user_id'); // Recipient of the notification

            // ==================== FOREIGN KEYS ====================
            // Recipient user
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // Delete notifications when user deleted

            // ==================== POLYMORPHIC RELATIONSHIPS ====================
            // Related entity (internship, document, user, etc.)
            $table->nullableMorphs('related'); // Creates related_type and related_id columns

            // ==================== INDEXES ====================
            $table->index(['user_id', 'is_sent']); // Find pending notifications for a user
            $table->index(['type', 'is_sent']); // Find pending notifications by type
            $table->index('created_at'); // Process notifications in chronological order
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('email_notifications');
    }
};