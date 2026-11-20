<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asteroid_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('jpl_id')->nullable();
            $table->string('name');
            $table->integer('diameter_m');
            $table->bigInteger('distance_km');
            $table->integer('velocity_kmh');
            $table->boolean('is_hazardous')->default(false);
            $table->string('priority_level')->default('NORMAL');
            $table->date('observation_date');
            $table->longText('analysis_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asteroid_observations');
    }
};