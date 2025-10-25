<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_status_history', function (Blueprint $table) {
            $table->id();
            $table->timestamp('status_changed_at')->useCurrent();
            $table->foreignId('timesheet_status_id')->nullable()->constrained('timesheet_status')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('documents_id')->nullable()->constrained('documents')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_status_history');
    }
};
