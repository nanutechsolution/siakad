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
        /*
         * Tambahkan dosen_id jika belum ada
         * (karena migration sebelumnya sempat gagal setelah membuat kolom)
         */
        if (! Schema::hasColumn('lpm_edom_progress', 'dosen_id')) {
            Schema::table('lpm_edom_progress', function (Blueprint $table) {
                $table->char('dosen_id', 36)
                    ->nullable()
                    ->after('jadwal_kuliah_id');
            });
        }


        /*
         * Backfill data lama
         *
         * Data lama:
         * mahasiswa + jadwal
         *
         * Data baru:
         * mahasiswa + jadwal + dosen
         */
        $rows = DB::table('lpm_edom_progress')
            ->whereNull('dosen_id')
            ->get();


        foreach ($rows as $row) {

            $dosenIds = DB::table('jadwal_kuliah_dosen')
                ->where('jadwal_kuliah_id', $row->jadwal_kuliah_id)
                ->where('is_penilai', 1)
                ->pluck('dosen_id');


            foreach ($dosenIds as $index => $dosenId) {

                if ($index === 0) {

                    DB::table('lpm_edom_progress')
                        ->where('id', $row->id)
                        ->update([
                            'dosen_id' => $dosenId,
                        ]);
                } else {

                    DB::table('lpm_edom_progress')
                        ->insert([
                            'mahasiswa_id'     => $row->mahasiswa_id,
                            'jadwal_kuliah_id' => $row->jadwal_kuliah_id,
                            'dosen_id'         => $dosenId,
                            'is_completed'     => $row->is_completed,
                            'created_at'       => $row->created_at,
                            'updated_at'       => $row->updated_at,
                        ]);
                }
            }
        }



        /*
         * Lepas FK mahasiswa dulu.
         * Karena index lama dipakai oleh FK.
         */
        $foreignKeys = DB::select("
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'lpm_edom_progress'
    AND CONSTRAINT_NAME = 'lpm_edom_progress_mahasiswa_id_foreign'
");

        if (count($foreignKeys) > 0) {
            DB::statement("
        ALTER TABLE lpm_edom_progress
        DROP FOREIGN KEY lpm_edom_progress_mahasiswa_id_foreign
    ");
        }



        /*
         * Hapus unique lama
         *
         * UNIQUE:
         * mahasiswa_id
         * jadwal_kuliah_id
         */
        $indexes = DB::select("
    SHOW INDEX FROM lpm_edom_progress
    WHERE Key_name = 'uq_mhs_jadwal_edom'
");

        if (count($indexes) > 0) {
            DB::statement("
        ALTER TABLE lpm_edom_progress
        DROP INDEX uq_mhs_jadwal_edom
    ");
        }



        /*
         * Buat unique baru
         *
         * UNIQUE:
         * mahasiswa_id
         * jadwal_kuliah_id
         * dosen_id
         */
        $indexes = DB::select("
    SHOW INDEX FROM lpm_edom_progress
    WHERE Key_name = 'uq_mhs_jadwal_dosen_edom'
");

        if (count($indexes) === 0) {
            Schema::table('lpm_edom_progress', function (Blueprint $table) {

                $table->unique(
                    [
                        'mahasiswa_id',
                        'jadwal_kuliah_id',
                        'dosen_id'
                    ],
                    'uq_mhs_jadwal_dosen_edom'
                );
            });
        }



        /*
         * Pasang kembali FK
         */
        Schema::table('lpm_edom_progress', function (Blueprint $table) {


            $table->foreign(
                'mahasiswa_id',
                'lpm_edom_progress_mahasiswa_id_foreign'
            )
                ->references('id')
                ->on('mahasiswas')
                ->cascadeOnDelete();



            $table->foreign(
                'dosen_id',
                'lpm_edom_progress_dosen_id_foreign'
            )
                ->references('id')
                ->on('trx_dosen')
                ->cascadeOnDelete();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_edom_progress', function (Blueprint $table) {

            $table->dropForeign(
                'lpm_edom_progress_dosen_id_foreign'
            );


            $table->dropForeign(
                'lpm_edom_progress_mahasiswa_id_foreign'
            );
        });


        Schema::table('lpm_edom_progress', function (Blueprint $table) {

            $table->dropUnique(
                'uq_mhs_jadwal_dosen_edom'
            );


            $table->unique(
                [
                    'mahasiswa_id',
                    'jadwal_kuliah_id'
                ],
                'uq_mhs_jadwal_edom'
            );
        });


        Schema::table('lpm_edom_progress', function (Blueprint $table) {

            $table->foreign(
                'mahasiswa_id',
                'lpm_edom_progress_mahasiswa_id_foreign'
            )
                ->references('id')
                ->on('mahasiswas')
                ->cascadeOnDelete();


            $table->dropColumn('dosen_id');
        });
    }
};
