<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company Table Migration
 *
 * Creates the company table for storing information about internship host organizations.
 * Each company record includes basic company information, contact person details,
 * and a relationship to a physical address.
 *
 * Purpose:
 * - Store company/organization information for internships (FR-03)
 * - Track contact person details for communication (FR-03 requirement)
 * - Enable company-based filtering and searching (FR-05, FR-07)
 * - Link companies to users (company representatives)
 * - Link companies to internships
 *
 * Key Columns:
 * - company_name: Name of the organization (indexed for efficient searching)
 * - contact_person_name: Name of the primary contact at the company
 * - contact_person_email: Email for company communication
 * - contact_person_phone: Phone number for direct contact
 * - address_id: Foreign key to address table (nullable)
 *
 * Relationships:
 * - address: Many-to-one relationship with address table
 * - Referenced by users.company_id (company representatives)
 * - Referenced by internship.company_id (internship host)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('company', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();

            // Company basic information
            $table->string('company_name', 100); // Organization name

            // Contact person details (FR-03 requirement)
            $table->string('contact_person_name', 100)->nullable(); // Primary contact name
            $table->string('contact_person_email', 100)->nullable(); // Contact email
            $table->string('contact_person_phone', 25)->nullable(); // Contact phone number

            $table->timestamps();

            // ==================== FOREIGN KEYS ====================
            // Link to physical address
            $table->foreignId('address_id')
                ->nullable()
                ->constrained('address')
                ->onDelete('cascade') // Delete address when company is deleted
                ->onUpdate('cascade');

            // ==================== INDEXES ====================
            $table->index('company_name'); // Enable efficient company name searching (FR-05, FR-07)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('company');
    }
};
