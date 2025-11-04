<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Addresses for students and companies
     */
    public function up(): void
    {
        Schema::create('address', function (Blueprint $table) {
            $table->id();
            $table->string('street', 100)->nullable();
            $table->string('street_number', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Slovakia')->nullable();
            $table->timestamps();
            
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
