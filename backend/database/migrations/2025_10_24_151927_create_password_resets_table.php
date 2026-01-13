<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Password Resets Table Migration
 *
 * Creates the password_resets table for managing password reset tokens.
 * This table supports the "forgot password" functionality required by FR-04.
 *
 * Purpose:
 * - Store temporary password reset tokens (FR-04)
 * - Enable secure password recovery workflow
 * - Track when reset requests were created
 * - Automatically expire old tokens
 *
 * Key Features:
 * - Email-based token association
 * - Timestamp for token expiration logic
 * - Composite index for efficient token validation
 * - No primary key (tokens are temporary and cleaned up)
 *
 * Key Columns:
 * - email: User's email address requesting password reset
 * - token: Hashed reset token sent to user via email
 * - created_at: When the reset request was made (for expiration checking)
 *
 * Note: This table does not have a primary key as it's designed for temporary
 * token storage. Old tokens should be periodically cleaned up.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('password_resets', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->string('email')->index(); // User email requesting reset
            $table->string('token'); // Hashed reset token
            $table->timestamp('created_at')->nullable(); // Token creation timestamp

            // ==================== INDEXES ====================
            $table->index(['email', 'token']); // Composite index for token validation queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('password_resets');
    }
};
