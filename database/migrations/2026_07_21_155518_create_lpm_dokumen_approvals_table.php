<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_dokumen_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('lpm_dokumens')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('ref_person')->cascadeOnDelete();
            $table->enum('peran', ['PENYUSUN', 'PEMERIKSA', 'PENGESAH']);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('catatan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Riwayat approval per dokumen dibiarkan bertambah (tidak unique)
            // supaya jejak approval versi-versi sebelumnya tetap tersimpan;
            // index di bawah cukup untuk lookup approval aktif per peran.
            $table->index(['dokumen_id', 'peran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_dokumen_approvals');
    }
};
