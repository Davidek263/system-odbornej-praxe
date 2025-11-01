<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Study fields for students (FR-03)
     */
    public function up(): void
    {
        Schema::create('study_field', function (Blueprint $table) {
            $table->id();
            $table->string('study_field_name', 100)->unique();
            $table->string('abbreviation', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_field');
    }
};
