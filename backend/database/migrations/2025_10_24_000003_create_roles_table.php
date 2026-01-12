<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roles Table Migration
 *
 * Creates the roles table for defining user roles in the internship management system.
 * This table is fundamental for access control and permission management (FR-01).
 *
 * Purpose:
 * - Define user roles for role-based access control (FR-01)
 * - Support multiple user types in the system
 * - Enable role-specific functionality and permissions
 *
 * Expected Roles:
 * - student: Students managing their internships
 * - company: Company representatives managing internship opportunities
 * - guarantor: Academic staff supervising and approving internships
 * - external_system: External integrations and API access
 *
 * Key Columns:
 * - role_name: Unique identifier for the role (e.g., 'student', 'guarantor')
 * - description: Human-readable explanation of the role's purpose
 *
 * Relationships:
 * - Referenced by users.roles_id (each user has one role)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('roles', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();
            $table->string('role_name', 45)->unique(); // Unique role identifier
            $table->string('description', 255)->nullable(); // Role description for documentation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('roles');
    }
};
