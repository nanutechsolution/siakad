<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kurikulums', function (Blueprint $table) {
            $table->enum('mode_krs', ['PAKET', 'BEBAS'])
                ->default('PAKET')
                ->after('is_active')
                ->comment('PAKET: MK ditentukan kurikulum via kelas, GATE SKS berbasis IPS di-skip. BEBAS: mahasiswa pilih sendiri, tunduk GATE SKS Maksimal berbasis IPS.');
        });
    }

    public function down(): void
    {
        Schema::table('master_kurikulums', function (Blueprint $table) {
            $table->dropColumn('mode_krs');
        });
    }
};