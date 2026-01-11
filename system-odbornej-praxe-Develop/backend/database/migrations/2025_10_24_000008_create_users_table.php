<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Users table with all FR-03 and FR-04 requirements
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Basic student information (FR-03)
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            
            // Email fields (FR-03)
            $table->string('email', 100)->unique(); // Primary email (required)
            $table->string('student_email', 100)->unique()->nullable(); // Student-specific email
            $table->string('alternative_email', 100)->nullable(); // FR-03: Alternative email
            
            // Contact information
            $table->string('phone_number', 25)->nullable();
            
            // Authentication (FR-04)
            $table->string('password', 255);
            $table->boolean('must_change_password')->default(false)->comment('FR-03: Force password change on first login');
            $table->timestamp('password_changed_at')->nullable();
            
            // Account activation (FR-03)
            $table->boolean('active')->default(false);
            $table->string('activation_token', 64)->nullable()->unique();
            $table->timestamp('activation_token_expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            
            // Email verification
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('student_email_verified_at')->nullable();
            
            // Foreign keys
            $table->foreignId('study_field_id')
                ->nullable()
                ->constrained('study_field')
                ->onDelete('set null')
                ->onUpdate('cascade');
                
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('company')
                ->onDelete('set null')
                ->onUpdate('cascade');
                
            $table->foreignId('address_id')
                ->nullable()
                ->constrained('address')
                ->onDelete('set null')
                ->onUpdate('cascade');
                
            $table->foreignId('roles_id')
                ->nullable()
                ->constrained('roles')
                ->onDelete('set null')
                ->onUpdate('cascade');
            
            $table->timestamps();
            $table->softDeletes(); // Soft delete for data retention
            
            // Indexes for performance (FR-07 filtering requirements)
            $table->index('student_email');
            $table->index('alternative_email');
            $table->index(['roles_id', 'active']);
            $table->index('company_id');
            $table->index('study_field_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
