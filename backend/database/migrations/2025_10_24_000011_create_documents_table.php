<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_name', 100)->nullable();
            $table->foreignId('internship_id')->nullable()->constrained('internship')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('document_type_id')->nullable()->constrained('document_type')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
