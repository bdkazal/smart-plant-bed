<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->timestamp('last_reported_at')->nullable()->after('last_seen_at');
            $table->string('last_reported_operation_state')->nullable()->after('last_reported_at');
            $table->string('last_reported_valve_state')->nullable()->after('last_reported_operation_state');
            $table->string('last_reported_watering_state')->nullable()->after('last_reported_valve_state');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'last_reported_at',
                'last_reported_operation_state',
                'last_reported_valve_state',
                'last_reported_watering_state',
            ]);
        });
    }
};
