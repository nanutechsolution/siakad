<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perkuliahan_sesi', function (Blueprint $table) {
            $table->timestamp('token_generated_at')->nullable()->after('token_sesi');
        });

        Schema::table('perkuliahan_absensi', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('bukti_validasi');
            $table->string('device_fingerprint', 64)->nullable()->after('ip_address');
            $table->boolean('is_flagged_duplikat')->default(false)->after('device_fingerprint');
            $table->index(['perkuliahan_sesi_id', 'device_fingerprint']);
            $table->index(['perkuliahan_sesi_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::table('perkuliahan_sesi', function (Blueprint $table) {
            $table->dropColumn('token_generated_at');
        });

        Schema::table('perkuliahan_absensi', function (Blueprint $table) {
            $table->dropIndex(['perkuliahan_sesi_id', 'device_fingerprint']);
            $table->dropIndex(['perkuliahan_sesi_id', 'ip_address']);
            $table->dropColumn(['ip_address', 'device_fingerprint', 'is_flagged_duplikat']);
        });
    }
};
