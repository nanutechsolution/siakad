<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 7 — master_kurikulums dan ref_tahun_akademik belum punya
 * deleted_at, padahal keduanya master data akademik inti yang dirujuk
 * hampir semua transaksi. Menambahkan soft delete agar tidak ada hard
 * delete tidak sengaja terhadap master data ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kurikulums', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ref_tahun_akademik', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('master_kurikulums', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('ref_tahun_akademik', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
