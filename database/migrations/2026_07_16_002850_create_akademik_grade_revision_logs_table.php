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
        Schema::create('akademik_grade_revision_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('krs_detail_id')->index('akademik_grade_revision_logs_krs_detail_id_foreign');
            $table->decimal('old_nilai_angka', 5);
            $table->string('old_nilai_huruf', 2);
            $table->decimal('new_nilai_angka', 5);
            $table->string('new_nilai_huruf', 2);
            $table->text('alasan_perbaikan');
            $table->string('nomor_sk_perbaikan')->nullable();
            $table->char('executed_by', 36)->index('akademik_grade_revision_logs_executed_by_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akademik_grade_revision_logs');
    }
};
