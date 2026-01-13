<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sessions Table Migration
 *
 * Creates the sessions table for Laravel's session management.
 * This table stores session data for stateful features like CSRF protection,
 * flash messages, and temporary data between requests.
 *
 * IMPORTANT: This sessions table is for non-authentication purposes only.
 * Authentication MUST use personal_access_tokens per NFR-02 (OAuth 2.0).
 *
 * Purpose:
 * - Store session data for CSRF protection
 * - Maintain flash messages between requests
 * - Track temporary user state
 * - Store shopping cart or form data temporarily
 *
 * Key Features:
 * - String-based session IDs for flexibility
 * - Optional user association
 * - IP and user agent tracking for security
 * - Activity-based expiration
 *
 * Key Columns:
 * - id: Unique session identifier (string, not numeric)
 * - user_id: Optional link to authenticated user
 * - ip_address: Client IP for security auditing (supports IPv6 with 45 chars)
 * - user_agent: Browser/client information
 * - payload: Serialized session data
 * - last_activity: Unix timestamp of last activity (for expiration)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('sessions', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->string('id')->primary(); // Session identifier
            $table->foreignId('user_id')->nullable()->index(); // Optional authenticated user
            $table->string('ip_address', 45)->nullable(); // Client IP (IPv6 compatible)
            $table->text('user_agent')->nullable(); // Browser/client info
            $table->longText('payload'); // Serialized session data
            $table->integer('last_activity')->index(); // Unix timestamp for expiration

            // ==================== INDEXES ====================
            // Note: user_id and last_activity indexes created above
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('sessions');
    }
};
