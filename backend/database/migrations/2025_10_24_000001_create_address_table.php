<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Address Table Migration
 *
 * Creates the address table for storing physical addresses of students and companies.
 * Addresses are shared entities that can be referenced by multiple users and companies
 * to maintain data normalization and consistency.
 *
 * Purpose:
 * - Store physical addresses for students (FR-03)
 * - Store company locations
 * - Enable address-based filtering and searching
 *
 * Key Columns:
 * - street: Street name (up to 100 characters)
 * - street_number: House/building number (up to 20 characters)
 * - city: City name (indexed for efficient searching)
 * - postal_code: ZIP/postal code
 * - country: Country name (defaults to 'Slovakia')
 *
 * Relationships:
 * - Referenced by users.address_id
 * - Referenced by company.address_id
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('address', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();
            $table->string('street', 100)->nullable(); // Street name
            $table->string('street_number', 20)->nullable(); // House/building number
            $table->string('city', 100)->nullable(); // City name
            $table->string('postal_code', 20)->nullable(); // ZIP/postal code
            $table->string('country', 100)->default('Slovakia')->nullable(); // Country (defaults to Slovakia)
            $table->timestamps();

            // ==================== INDEXES ====================
            $table->index('city'); // Enable efficient city-based filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('address');
    }
};
