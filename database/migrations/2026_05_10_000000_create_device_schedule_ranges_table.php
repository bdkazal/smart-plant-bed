<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_schedule_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('days_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('start_scene_id')->constrained('device_scenes')->cascadeOnDelete();
            $table->foreignId('end_scene_id')->constrained('device_scenes')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->date('last_started_on')->nullable();
            $table->date('last_ended_on')->nullable();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('last_ended_at')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'is_enabled']);
            $table->index(['start_scene_id']);
            $table->index(['end_scene_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_schedule_ranges');
    }
};
