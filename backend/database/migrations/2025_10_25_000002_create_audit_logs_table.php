<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit log for tracking all important changes (Recommended for transparency)
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Who performed the action
            $table->unsignedBigInteger('user_id')->nullable();
            
            // What was done
            $table->string('action', 50)->comment('create, update, delete, status_change, etc.');
            $table->string('model_type', 50)->comment('User, Internship, Document, etc.');
            $table->unsignedBigInteger('model_id');
            
            // Change details
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Define constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Indexes for audit queries
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};