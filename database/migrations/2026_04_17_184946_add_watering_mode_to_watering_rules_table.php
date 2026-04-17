<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('watering_rules', function (Blueprint $table) {
            $table->string('watering_mode', 20)
                ->default('schedule')
                ->after('auto_mode_enabled');
        });

        DB::table('watering_rules')
            ->where('auto_mode_enabled', true)
            ->update([
                'watering_mode' => 'auto',
            ]);

        DB::table('watering_rules')
            ->where(function ($query) {
                $query->where('auto_mode_enabled', false)
                    ->orWhereNull('auto_mode_enabled');
            })
            ->update([
                'watering_mode' => 'schedule',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watering_rules', function (Blueprint $table) {
            $table->dropColumn('watering_mode');
        });
    }
};