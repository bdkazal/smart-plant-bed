<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watering_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->boolean('auto_mode_enabled')->default(false);
            $table->unsignedTinyInteger('soil_moisture_threshold')->default(35);
            $table->unsignedInteger('max_watering_duration_seconds')->default(30);
            $table->unsignedInteger('cooldown_minutes')->default(60);
            $table->unsignedInteger('local_manual_duration_seconds')->default(30);

            $table->timestamps();

            $table->unique('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watering_rules');
    }
};
