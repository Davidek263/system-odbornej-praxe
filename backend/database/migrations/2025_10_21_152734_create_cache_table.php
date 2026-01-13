<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cache Tables Migration
 *
 * Creates the cache storage tables for Laravel's cache system.
 * This migration creates two related tables:
 * - cache: Stores cached data with expiration times
 * - cache_locks: Manages distributed locks for cache operations
 *
 * Key Features:
 * - String-based keys for flexible cache naming
 * - Medium text storage for cached values (up to 16MB)
 * - Integer expiration timestamps for automatic cleanup
 * - Lock mechanism for preventing race conditions
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION: cache ====================
        Schema::create('cache', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->string('key')->primary(); // Unique cache key identifier
            $table->mediumText('value'); // Serialized cached data
            $table->integer('expiration'); // Unix timestamp when cache expires
        });

        // ==================== TABLE CREATION: cache_locks ====================
        Schema::create('cache_locks', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->string('key')->primary(); // Lock identifier
            $table->string('owner'); // Process/thread that owns the lock
            $table->integer('expiration'); // Unix timestamp when lock expires
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLES ====================
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
