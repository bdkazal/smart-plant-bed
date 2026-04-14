<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('claim_code', 20)->unique()->after('api_key');
            $table->timestamp('claimed_at')->nullable()->after('claim_code');
            $table->string('provisioning_token', 64)->nullable()->after('claimed_at');
            $table->timestamp('provisioning_expires_at')->nullable()->after('provisioning_token');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'claim_code',
                'claimed_at',
                'provisioning_token',
                'provisioning_expires_at',
            ]);
        });
    }
};
