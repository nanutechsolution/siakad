<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('keuangan_saldos', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('mahasiswa_id', 36)->index('keuangan_saldos_mahasiswa_id_foreign');
            $table->decimal('saldo', 15)->default(0);
            $table->timestamp('last_updated_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_saldos');
    }
};
