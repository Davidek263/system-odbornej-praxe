<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Internship table with proper date types and current status (FR-05, FR-06, FR-07)
     */
    public function up(): void
    {
        Schema::create('internship', function (Blueprint $table) {
            $table->id();
            
            // Academic information (FR-05)
            $table->string('academic_year', 9)->comment('Format: YYYY/YYYY, e.g., 2024/2025');
            $table->tinyInteger('semester')->comment('1 = winter, 2 = summer');
            
            // Dates (FIXED: Now proper date type, not string)
            $table->date('date_start');
            $table->date('date_end');
            
            // Current status for efficient queries (NEW)
            $table->foreignId('current_status_id')
                ->nullable()
                ->constrained('internship_status')
                ->onDelete('set null')
                ->onUpdate('cascade');
            
            // Relationships
            $table->foreignId('users_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
                
            $table->foreignId('company_id')
                ->constrained('company')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->timestamps();
            $table->softDeletes(); // Soft delete for archiving
            
            // Indexes for filtering (FR-07)
            $table->index(['users_id', 'academic_year']);
            $table->index(['company_id', 'semester']);
            $table->index('academic_year');
            $table->index('current_status_id');
            $table->index(['academic_year', 'semester']); // Combined filter
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship');
    }
};
