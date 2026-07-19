<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->index(
                ['tahun_akademik_id', 'status_krs'],
                'idx_status_ta'
            );
        });
    }

    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropIndex('idx_status_ta');
        });
    }
};
