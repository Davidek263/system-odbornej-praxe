<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Password reset tokens (FR-04)
     */
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            
            // Add index for faster token lookup
            $table->index(['email', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
