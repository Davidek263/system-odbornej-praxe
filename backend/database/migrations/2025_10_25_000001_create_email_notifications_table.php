<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Email notification tracking (FR-06, FR-07)
     */
    public function up(): void
    {
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            
            // Recipient
            $table->unsignedBigInteger('user_id');
            
            // Notification details
            $table->string('type', 50)->comment('status_change, registration, activation, etc.');
            $table->string('subject', 255);
            $table->text('body');
            
            // Related entity (polymorphic)
            $table->nullableMorphs('related');
            
            // Sending status
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->tinyInteger('retry_count')->default(0);
            
            $table->timestamps();
            
            // Define constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            // Indexes
            $table->index(['user_id', 'is_sent']);
            $table->index(['type', 'is_sent']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_notifications');
    }
};