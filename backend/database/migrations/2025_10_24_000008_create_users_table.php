<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 20)->nullable();
            $table->string('last_name', 20)->nullable();
            $table->string('email', 100)->unique();
            $table->string('phone_number', 20)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('user_address', 100)->nullable();
            $table->boolean('active')->nullable();

            $table->foreignId('study_field_id')->nullable()->constrained('study_field')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('company_id')->nullable()->constrained('company')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('address_id')->nullable()->constrained('address')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('roles_id')->nullable()->constrained('roles')->onDelete('set null')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
