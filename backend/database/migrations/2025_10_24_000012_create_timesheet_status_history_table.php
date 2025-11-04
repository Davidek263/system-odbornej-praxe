<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Timesheet status change history (FR-08)
     */
    public function up(): void
    {
        Schema::create('timesheet_status_history', function (Blueprint $table) {
            $table->id();
            $table->timestamp('status_changed_at')->useCurrent();
            
            // Foreign keys
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->unsignedBigInteger('timesheet_status_id');
            $table->unsignedBigInteger('documents_id');
            
            // Optional notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Define constraints
            $table->foreign('changed_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            $table->foreign('timesheet_status_id')
                ->references('id')
                ->on('timesheet_status')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreign('documents_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            // Indexes
            $table->index(['documents_id', 'status_changed_at']);
            $table->index('changed_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_status_history');
    }
};