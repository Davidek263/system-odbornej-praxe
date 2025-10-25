<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 9)->nullable();
            $table->integer('semester')->nullable();
            $table->string('date_start', 45)->nullable();
            $table->string('date_end', 45)->nullable();
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('company_id')->nullable()->constrained('company')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship');
    }
};
