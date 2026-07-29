<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdf_signature_authorities', function (Blueprint $table) {
            // NONE = jabatan institusi-wide (Rektor, Kepala BAUK, dst — satu untuk semua)
            // PRODI = harus dicocokkan ke trx_person_jabatan.prodi_id milik mahasiswa
            // FAKULTAS = harus dicocokkan ke trx_person_jabatan.fakultas_id milik mahasiswa
            $table->string('scope', 20)->default('NONE')->after('jabatan_id');
        });
    }

    public function down(): void
    {
        Schema::table('pdf_signature_authorities', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
