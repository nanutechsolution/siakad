<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: kolom `auditor_name` (varchar bebas) TETAP dipertahankan untuk
 * kompatibilitas data lama / kasus auditor eksternal yang belum tentu
 * tercatat sebagai lpm_auditors. Kolom baru `auditor_id` dipakai untuk
 * temuan baru yang auditornya sudah relasional. `rencana_tindak_lanjut`
 * (existing) tetap berfungsi sebagai Corrective Action; `preventive_action`
 * baru ditambahkan sesuai requirement supaya keduanya terpisah eksplisit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpm_ami_findings', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('periode_id')->constrained('lpm_ami_programs')->nullOnDelete();
            $table->foreignId('auditor_id')->nullable()->after('auditor_name')->constrained('lpm_auditors')->nullOnDelete();
            $table->text('preventive_action')->nullable()->after('rencana_tindak_lanjut');
        });
    }

    public function down(): void
    {
        Schema::table('lpm_ami_findings', function (Blueprint $table) {
            $table->dropColumn('preventive_action');
            $table->dropConstrainedForeignId('auditor_id');
            $table->dropConstrainedForeignId('program_id');
        });
    }
};