<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status change history for internships (FR-06, FR-07)
     */
    public function up(): void
    {
        Schema::create('internship_status_change', function (Blueprint $table) {
            $table->id();
            $table->timestamp('status_changed_at')->useCurrent();
            
            // Foreign keys
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->unsignedBigInteger('internship_status_id');
            $table->unsignedBigInteger('internship_id');
            
            // Optional notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Define constraints
            $table->foreign('changed_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
            
            $table->foreign('internship_status_id')
                ->references('id')
                ->on('internship_status')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreign('internship_id')
                ->references('id')
                ->on('internship')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Indexes
            $table->index(['internship_id', 'status_changed_at']);
            $table->index('changed_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_status_change');
    }
};