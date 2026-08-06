<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ref_tahun_akademik', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('semester');

            $table->timestamp('krs_dibuka_at')->nullable();
            $table->timestamp('krs_ditutup_at')->nullable();
            $table->timestamp('nilai_dikunci_at')->nullable();
            $table->timestamp('nilai_dipublish_at')->nullable();
            $table->timestamp('semester_ditutup_at')->nullable();

            $table->char('ditutup_by', 36)->nullable();
            $table->foreign('ditutup_by')->references('id')->on('users')->nullOnDelete();

            $table->index('status');
        });

        // Backfill status dari kolom boolean lama (urutan penting: paling maju duluan)
        DB::table('ref_tahun_akademik')->where('is_locked_nilai', true)->update(['status' => 'nilai_terkunci']);
        DB::table('ref_tahun_akademik')->where('buka_input_nilai', true)->update(['status' => 'input_nilai']);
        DB::table('ref_tahun_akademik')
            ->where('is_locked_krs', true)
            ->where('buka_input_nilai', false)
            ->update(['status' => 'krs_tutup']);
        DB::table('ref_tahun_akademik')->where('buka_krs', true)->update(['status' => 'krs_buka']);
    }

    public function down(): void
    {
        Schema::table('ref_tahun_akademik', function (Blueprint $table) {
            $table->dropForeign(['ditutup_by']);
            $table->dropColumn([
                'status',
                'krs_dibuka_at',
                'krs_ditutup_at',
                'nilai_dikunci_at',
                'nilai_dipublish_at',
                'semester_ditutup_at',
                'ditutup_by',
            ]);
        });
    }
};
