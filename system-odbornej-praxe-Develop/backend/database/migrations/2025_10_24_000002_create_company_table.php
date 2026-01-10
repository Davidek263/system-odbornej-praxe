<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company table with contact person information (FR-03)
     */
    public function up(): void
    {
        Schema::create('company', function (Blueprint $table) {
            $table->id();
            
            // Company basic info
            $table->string('company_name', 100);
            
            // Contact person (FR-03 requirement)
            $table->string('contact_person_name', 100)->nullable();
            $table->string('contact_person_email', 100)->nullable();
            $table->string('contact_person_phone', 25)->nullable();
            
            // Address relationship
            $table->foreignId('address_id')
                ->nullable()
                ->constrained('address')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->timestamps();
            
            // Index for company search (FR-05, FR-07)
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
