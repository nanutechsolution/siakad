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
        Schema::create('lppm_usulans', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('skema_id')->index('lppm_usulans_skema_id_foreign');
            $table->char('dosen_ketua_id', 36)->index('lppm_usulans_dosen_ketua_id_foreign');
            $table->text('judul_usulan');
            $table->longText('abstrak')->nullable();
            $table->decimal('dana_diajukan', 19)->default(0);
            $table->decimal('dana_disetujui', 19)->nullable();
            $table->string('file_proposal_path')->nullable();
            $table->string('status_usulan', 30)->default('DRAFT');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lppm_usulans');
    }
};
