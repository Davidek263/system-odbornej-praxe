<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Internship statuses: Vytvorená, Potvrdená, Schválená, Obhájená, Neobhájená (FR-05, FR-06, FR-07)
     */
    public function up(): void
    {
        Schema::create('internship_status', function (Blueprint $table) {
            $table->id();
            $table->string('internship_status_name', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->integer('order')->default(0)->comment('Display order for status progression');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_status');
    }
};
