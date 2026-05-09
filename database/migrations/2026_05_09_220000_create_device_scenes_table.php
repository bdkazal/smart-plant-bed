<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('outputs');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['device_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_scenes');
    }
};
