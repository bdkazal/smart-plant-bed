<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->string('type');
            $table->string('name');

            $table->json('config')->nullable();
            $table->json('state')->nullable();

            $table->string('last_changed_source')->nullable();
            $table->timestamp('last_changed_at')->nullable();

            $table->timestamps();

            $table->unique(['device_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_outputs');
    }
};
