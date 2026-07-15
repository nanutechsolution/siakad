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
        Schema::create('academic_history_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('tahun_akademik_id')->index('academic_history_logs_tahun_akademik_id_foreign');
            $table->string('previous_mode', 20)->nullable();
            $table->string('new_mode', 20);
            $table->text('trigger_event');
            $table->timestamps();

            $table->index(['mahasiswa_id', 'tahun_akademik_id'], 'idx_mhs_ta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_history_logs');
    }
};
