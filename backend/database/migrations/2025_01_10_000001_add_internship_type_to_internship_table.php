<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add internship_type column to distinguish between internship and job/brigade
     * This affects whether school agreement is generated or employer agreement is required
     */
    public function up(): void
    {
        Schema::table('internship', function (Blueprint $table) {
            // Add internship_type column
            // 'prax' = odborná prax (school agreement generated)
            // 'brigada' = brigáda/práca (employer agreement required via upload)
            $table->enum('internship_type', ['prax', 'brigada'])
                ->default('prax')
                ->after('semester')
                ->comment('prax = school agreement, brigada = employer agreement required');

            // Add index for filtering
            $table->index('internship_type');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('internship', function (Blueprint $table) {
            $table->dropIndex(['internship_type']);
            $table->dropColumn('internship_type');
        });
    }
};
