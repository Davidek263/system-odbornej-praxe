<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal Access Tokens Table Migration
 *
 * Creates the personal_access_tokens table for OAuth 2.0 token-based authentication.
 * This table is required for NFR-02 (Non-Functional Requirement 02).
 *
 * Purpose:
 * - Stores API tokens for authenticated users
 * - Supports polymorphic relationships (can be attached to any model)
 * - Tracks token usage and expiration
 * - Enables scoped permissions through abilities
 *
 * Key Columns:
 * - tokenable: Polymorphic relationship to the token owner (user, external system, etc.)
 * - token: Hashed authentication token (64 characters)
 * - abilities: JSON array of token permissions/scopes
 * - last_used_at: Tracks token activity for security auditing
 * - expires_at: Optional expiration timestamp for token lifecycle management
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();

            // Polymorphic relationship to token owner (User, ExternalSystem, etc.)
            $table->morphs('tokenable');

            $table->string('name'); // Human-readable token name/description
            $table->string('token', 64)->unique(); // Hashed authentication token
            $table->text('abilities')->nullable(); // JSON array of permissions/scopes
            $table->timestamp('last_used_at')->nullable(); // Last activity timestamp
            $table->timestamp('expires_at')->nullable()->index(); // Token expiration
            $table->timestamps();

            // ==================== INDEXES ====================
            // Note: morphs() automatically creates index on tokenable_type and tokenable_id
            // expires_at index created above for efficient expiration queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('personal_access_tokens');
    }
};
