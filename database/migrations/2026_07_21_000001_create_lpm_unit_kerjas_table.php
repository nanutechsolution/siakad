<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_unit_kerjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('lpm_unit_kerjas')->nullOnDelete();
            $table->enum('jenis_unit', ['UNIVERSITAS', 'FAKULTAS', 'PRODI', 'LEMBAGA', 'BIRO', 'UPT']);
            $table->string('kode_unit', 30)->unique();
            $table->string('nama_unit', 255);
            $table->foreignId('fakultas_id')->nullable()->constrained('ref_fakultas')->nullOnDelete();
            $table->foreignId('prodi_id')->nullable()->constrained('ref_prodi')->nullOnDelete();
            $table->foreignId('kepala_unit_person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_unit_kerjas');
    }
};
