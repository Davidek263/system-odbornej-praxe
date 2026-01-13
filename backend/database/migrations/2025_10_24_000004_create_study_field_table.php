<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Study Field Table Migration
 *
 * Creates the study_field table for storing academic study programs/fields.
 * Each student is associated with a study field, which is used for filtering,
 * reporting, and matching students with appropriate internships.
 *
 * Purpose:
 * - Store academic study programs (FR-03)
 * - Enable filtering students by field of study (FR-07)
 * - Support reporting and statistics by study program
 * - Track student academic context for internship matching
 *
 * Key Columns:
 * - study_field_name: Full name of the study program (e.g., "Informatics", "Software Engineering")
 * - abbreviation: Short code for the program (e.g., "INF", "SWE")
 *
 * Relationships:
 * - Referenced by users.study_field_id (students have one study field)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== TABLE CREATION ====================
        Schema::create('study_field', function (Blueprint $table) {
            // ==================== COLUMNS ====================
            $table->id();
            $table->string('study_field_name', 100)->unique(); // Full study program name
            $table->string('abbreviation', 20)->nullable(); // Short code for the program
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== DROP TABLE ====================
        Schema::dropIfExists('study_field');
    }
};
