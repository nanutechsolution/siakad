<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_kuliah', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('kuota_kelas')
                ->comment('Jika true, jadwal ini aman dari timpaan generator ulang');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_kuliah', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });
    }
};
