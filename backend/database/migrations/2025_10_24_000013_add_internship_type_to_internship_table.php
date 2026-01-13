<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Internship Type Column Migration
 *
 * Modifies the internship table to add an internship_type column that distinguishes
 * between academic internships (odborná prax) and student jobs/brigades.
 * This distinction is important because they have different agreement requirements.
 *
 * Purpose:
 * - Differentiate between internship types in the system
 * - Determine which type of agreement document is required
 * - Enable filtering and reporting by internship type
 * - Support different workflows for different internship categories
 *
 * Internship Types:
 * - prax (odborná prax): Academic internship where school generates the agreement (Dohoda)
 * - brigada: Student job/brigade where employer provides the contract (Zmluva)
 *
 * Key Impact:
 * - 'prax': System generates school agreement document automatically
 * - 'brigada': Student must upload employer-provided contract document
 *
 * Key Column:
 * - internship_type: ENUM('prax', 'brigada') - type of internship
 *   - Default: 'prax' (most common case for academic internships)
 *   - Indexed for efficient filtering and reporting
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE MODIFICATION ====================
        Schema::table('internship', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            // Add internship type discriminator
            // 'prax' = odborná prax (school agreement generated automatically)
            // 'brigada' = brigáda/práca (employer agreement required via upload)
            $table->enum('internship_type', ['prax', 'brigada'])
                ->default('prax') // Default to academic internship
                ->after('semester') // Place after semester column for logical ordering
                ->comment('prax = school agreement, brigada = employer agreement required');

            // ==================== INDEXES ====================
            $table->index('internship_type'); // Enable efficient filtering by type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== TABLE MODIFICATION ====================
        Schema::table('internship', function (Blueprint $table) {
            // ==================== DROP INDEX ====================
            $table->dropIndex(['internship_type']);

            // ==================== DROP COLUMN ====================
            $table->dropColumn('internship_type');
        });
    }
};
