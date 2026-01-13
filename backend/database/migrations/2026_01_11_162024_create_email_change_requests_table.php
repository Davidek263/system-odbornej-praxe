<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('old_email', 100)->index();
            $table->string('new_email', 100);
            $table->string('email_type', 20); // 'student' or 'company'
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            // Indexes for performance
            $table->index(['user_id', 'token']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_change_requests');
    }
};
