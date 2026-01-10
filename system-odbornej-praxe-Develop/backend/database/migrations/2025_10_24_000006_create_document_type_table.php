<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Document types: Dohoda, Zmluva, Výkaz (FR-08)
     */
    public function up(): void
    {
        Schema::create('document_type', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Explicitly set InnoDB
            
            $table->id();
            $table->string('document_type_name', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_required')->default(false)->comment('Is this document mandatory?');
            $table->string('required_at_status', 100)->nullable()->comment('Required at which internship status?');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_type');
    }
};
