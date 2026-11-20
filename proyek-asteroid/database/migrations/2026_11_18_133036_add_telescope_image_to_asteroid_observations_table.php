<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asteroid_observations', function (Blueprint $table) {
            $table->string('telescope_image')->nullable()->after('analysis_notes');
        });
    }

    public function down(): void
    {
        Schema::table('asteroid_observations', function (Blueprint $table) {
            $table->dropColumn('telescope_image');
        });
    }
};