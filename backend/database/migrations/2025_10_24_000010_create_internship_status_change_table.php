<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_status_change', function (Blueprint $table) {
            $table->id();
            $table->timestamp('status_changed_at')->useCurrent();
            $table->foreignId('internship_status_id')->nullable()->constrained('internship_status')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('users_id')->nullable()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('internship_id')->nullable()->constrained('internship')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_status_change');
    }
};
