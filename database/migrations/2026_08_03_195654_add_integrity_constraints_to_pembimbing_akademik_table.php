<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. mahasiswa_id: CASCADE -> RESTRICT.
        // Histori pembimbingan (wali, PKL, skripsi, dst) tidak boleh ikut lenyap
        // hanya karena baris mahasiswa dihapus. Konsisten dengan pola audit-trail
        // yang sudah dipakai di riwayat_status_mahasiswas & riwayat_prodi_mahasiswas.
        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->dropForeign('pembimbing_akademik_mahasiswa_id_foreign');
        });

        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->foreign('mahasiswa_id')
                ->references('id')->on('mahasiswas')
                ->onDelete('restrict');
        });

        // 2. CHECK: kelas_id vs mahasiswa_id wajib konsisten dengan jenis.
        //    - DOSEN_WALI    : XOR -> kelas_id SAJA atau mahasiswa_id SAJA
        //    - jenis lainnya : WAJIB mahasiswa_id (PKL/MBKM/skripsi/tesis/
        //                      disertasi/penguji selalu per-individu)
        DB::statement("
            ALTER TABLE pembimbing_akademik
            ADD CONSTRAINT chk_pembimbing_scope CHECK (
                (jenis = 'DOSEN_WALI' AND (
                    (kelas_id IS NOT NULL AND mahasiswa_id IS NULL) OR
                    (kelas_id IS NULL AND mahasiswa_id IS NOT NULL)
                ))
                OR
                (jenis <> 'DOSEN_WALI' AND kelas_id IS NULL AND mahasiswa_id IS NOT NULL)
            )
        ");

        // 3. Trigger: cegah 2 wali utama AKTIF untuk kelas/mahasiswa yang sama.
        //    Sengaja di-scope HANYA jenis=DOSEN_WALI + is_primary=1 + status=AKTIF
        //    supaya tidak membatasi pembimbing 1 & 2 pada skripsi/tesis/disertasi,
        //    atau penguji skripsi yang lazim lebih dari satu orang.
        DB::unprepared("
            CREATE TRIGGER trg_pembimbing_akademik_biu
            BEFORE INSERT ON pembimbing_akademik
            FOR EACH ROW
            BEGIN
                IF NEW.jenis = 'DOSEN_WALI' AND NEW.is_primary = 1 AND NEW.status = 'AKTIF' THEN
                    IF NEW.kelas_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE kelas_id = NEW.kelas_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF'
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kelas ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                    IF NEW.mahasiswa_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE mahasiswa_id = NEW.mahasiswa_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF'
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_pembimbing_akademik_bu
            BEFORE UPDATE ON pembimbing_akademik
            FOR EACH ROW
            BEGIN
                IF NEW.jenis = 'DOSEN_WALI' AND NEW.is_primary = 1 AND NEW.status = 'AKTIF' THEN
                    IF NEW.kelas_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE kelas_id = NEW.kelas_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF' AND id <> NEW.id
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kelas ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                    IF NEW.mahasiswa_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE mahasiswa_id = NEW.mahasiswa_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF' AND id <> NEW.id
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_pembimbing_akademik_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_pembimbing_akademik_biu');

        // MySQL 8.0.16+: DROP CHECK. (Kalau pindah engine ke MariaDB, ganti ke DROP CONSTRAINT.)
        DB::statement('ALTER TABLE pembimbing_akademik DROP CHECK chk_pembimbing_scope');

        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->dropForeign('pembimbing_akademik_mahasiswa_id_foreign');
        });

        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->foreign('mahasiswa_id')
                ->references('id')->on('mahasiswas')
                ->onDelete('cascade');
        });
    }
};
