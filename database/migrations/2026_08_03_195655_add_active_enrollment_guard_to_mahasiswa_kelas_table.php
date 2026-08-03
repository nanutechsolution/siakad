<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Jaga agar 1 mahasiswa hanya boleh punya SATU baris kelas yang masih
        // aktif (tanggal_keluar IS NULL) di satu waktu. Prasyarat mutlak supaya
        // resolusi wali mode PER_KELAS tidak ambigu.
        // Pakai trigger (bukan unique index) supaya migration ini tetap AMAN
        // dijalankan meski data mahasiswa_kelas yang sudah ada saat ini mungkin
        // sudah mengandung duplikat -- trigger hanya menahan pelanggaran BARU,
        // tidak memvalidasi ulang baris lama secara paksa.
        DB::unprepared("
            CREATE TRIGGER trg_mahasiswa_kelas_biu
            BEFORE INSERT ON mahasiswa_kelas
            FOR EACH ROW
            BEGIN
                IF NEW.tanggal_keluar IS NULL AND EXISTS (
                    SELECT 1 FROM mahasiswa_kelas
                    WHERE mahasiswa_id = NEW.mahasiswa_id AND tanggal_keluar IS NULL
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini masih memiliki kelas aktif lain. Isi tanggal_keluar pada baris sebelumnya terlebih dahulu.';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_mahasiswa_kelas_bu
            BEFORE UPDATE ON mahasiswa_kelas
            FOR EACH ROW
            BEGIN
                IF NEW.tanggal_keluar IS NULL AND EXISTS (
                    SELECT 1 FROM mahasiswa_kelas
                    WHERE mahasiswa_id = NEW.mahasiswa_id AND tanggal_keluar IS NULL AND id <> NEW.id
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini masih memiliki kelas aktif lain. Isi tanggal_keluar pada baris sebelumnya terlebih dahulu.';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_mahasiswa_kelas_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_mahasiswa_kelas_biu');
    }
};
