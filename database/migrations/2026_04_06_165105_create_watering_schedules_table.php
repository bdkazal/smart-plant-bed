<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watering_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedTinyInteger('day_of_week');
            $table->time('time_of_day');
            $table->unsignedInteger('duration_seconds')->default(30);

            $table->timestamps();

            $table->unique(['device_id', 'day_of_week', 'time_of_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watering_schedules');
    }
};
