<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Users Table Migration
 *
 * Creates the central users table for all system users (students, company representatives,
 * guarantors, and external systems). This table implements comprehensive requirements
 * from FR-03 (user data) and FR-04 (authentication).
 *
 * Purpose:
 * - Store user profiles for all user types (FR-03)
 * - Support authentication and authorization (FR-04)
 * - Track account activation and email verification
 * - Enable user-based filtering and searching (FR-07)
 * - Link users to their roles, companies, addresses, and study fields
 *
 * Key Features:
 * - Multiple email addresses: primary, student-specific, and alternative
 * - Account activation workflow with tokens and expiration
 * - Password management with change tracking and forced resets
 * - Soft deletes for data retention and GDPR compliance
 * - Comprehensive indexing for efficient queries
 *
 * Key Columns:
 * - first_name, last_name: User's full name
 * - email: Primary email (unique, required) for login
 * - student_email: Student-specific institutional email (unique)
 * - alternative_email: Additional contact email
 * - password: Hashed password for authentication
 * - must_change_password: Force password change on first login (FR-03)
 * - active: Account activation status
 * - activation_token: Secure token for email-based account activation
 *
 * Relationships:
 * - roles: Many-to-one with roles table (defines user type and permissions)
 * - study_field: Many-to-one with study_field (for students)
 * - company: Many-to-one with company (for company representatives)
 * - address: Many-to-one with address (physical address)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('users', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();

            // Basic user information (FR-03)
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();

            // Email addresses (FR-03)
            $table->string('email', 100)->unique(); // Primary email for login
            $table->string('student_email', 100)->unique()->nullable(); // Institutional email
            $table->string('alternative_email', 100)->nullable(); // Additional contact email

            // Contact information
            $table->string('phone_number', 25)->nullable();

            // Authentication (FR-04)
            $table->string('password', 255); // Hashed password
            $table->boolean('must_change_password')->default(false)->comment('Force password change on first login'); // FR-03 requirement
            $table->timestamp('password_changed_at')->nullable(); // Track last password change

            // Account activation workflow (FR-03)
            $table->boolean('active')->default(false); // Account activation status
            $table->string('activation_token', 64)->nullable()->unique(); // Secure activation token
            $table->timestamp('activation_token_expires_at')->nullable(); // Token expiration
            $table->timestamp('activated_at')->nullable(); // When account was activated

            // Email verification tracking
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('student_email_verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Soft delete for data retention and GDPR

            // ==================== FOREIGN KEYS ====================
            // User role (student, company, guarantor, external_system)
            $table->foreignId('roles_id')
                ->nullable()
                ->constrained('roles')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Study field (for students only)
            $table->foreignId('study_field_id')
                ->nullable()
                ->constrained('study_field')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Company affiliation (for company representatives)
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('company')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Physical address
            $table->foreignId('address_id')
                ->nullable()
                ->constrained('address')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // ==================== INDEXES ====================
            // Performance indexes for filtering and searching (FR-07)
            $table->index('student_email'); // Student email lookups
            $table->index('alternative_email'); // Alternative email lookups
            $table->index(['roles_id', 'active']); // Role + active status filtering
            $table->index('company_id'); // Company-based filtering
            $table->index('study_field_id'); // Study field filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('users');
    }
};
