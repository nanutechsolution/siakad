<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Tambah kolom baru dulu (nullable sementara untuk proses backfill)
        Schema::table('lpm_edom_saran', function (Blueprint $table) {
            $table->char('jadwal_kuliah_id', 36)->nullable()->after('id');
        });

        // 2) Backfill data lama. Ini SATU-SATUNYA titik di mana krs_detail masih
        //    "disentuh" — sebagai migrasi data sekali jalan, bukan relasi permanen.
        //    Setelah baris ini jalan, krs_detail_id akan dihapus total.
        DB::table('lpm_edom_saran as s')
            ->join('krs_detail as kd', 'kd.id', '=', 's.krs_detail_id')
            ->whereNotNull('kd.jadwal_kuliah_id')
            ->update([
                's.jadwal_kuliah_id' => DB::raw('kd.jadwal_kuliah_id'),
            ]);

        // Baris yatim (krs_detail tanpa jadwal_kuliah_id) tidak bisa di-backfill —
        // hapus saja karena datanya sudah tidak konsisten sejak awal.
        DB::table('lpm_edom_saran')->whereNull('jadwal_kuliah_id')->delete();

        // 3) Kunci NOT NULL, pasang FK baru, index baru
        Schema::table('lpm_edom_saran', function (Blueprint $table) {
            $table->char('jadwal_kuliah_id', 36)->nullable(false)->change();

            $table->foreign('jadwal_kuliah_id', 'lpm_edom_saran_jadwal_kuliah_id_foreign')
                ->references('id')->on('jadwal_kuliah')
                ->onDelete('cascade');

            $table->index(['jadwal_kuliah_id', 'dosen_id'], 'idx_edom_saran_jadwal_dosen');
        });

        // 4) PUTUSKAN RELASI LAMA — hapus FK, unique key, dan kolom krs_detail_id
        Schema::table('lpm_edom_saran', function (Blueprint $table) {
            $table->dropUnique('unique_edom_saran_dosen');
            $table->dropForeign('lpm_edom_saran_krs_detail_id_foreign');
            $table->dropColumn('krs_detail_id'); // <-- celah anonimitas diputus di sini
        });
    }

    public function down(): void
    {
        Schema::table('lpm_edom_saran', function (Blueprint $table) {
            $table->unsignedBigInteger('krs_detail_id')->nullable()->after('id');
        });

        Schema::table('lpm_edom_saran', function (Blueprint $table) {
            $table->foreign('krs_detail_id')
                ->references('id')->on('krs_detail')
                ->onDelete('cascade');
            $table->unique(['krs_detail_id', 'dosen_id'], 'unique_edom_saran_dosen');

            $table->dropForeign('lpm_edom_saran_jadwal_kuliah_id_foreign');
            $table->dropIndex('idx_edom_saran_jadwal_dosen');
            $table->dropColumn('jadwal_kuliah_id');
        });
    }
};
