<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('device_type', 50)
                ->default('plant_bed_controller')
                ->after('name');
        });

        DB::table('devices')
            ->whereNull('device_type')
            ->update([
                'device_type' => 'plant_bed_controller',
            ]);
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('device_type');
        });
    }
};
