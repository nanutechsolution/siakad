<?php

namespace Database\Seeders;

use App\Models\RefTahunAkademik as TahunAkademik;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TahunAkademikSeeder extends Seeder
{
    public function run(): void
    {
        $startYear = 2022;
        // Kita tambahkan +1 tahun agar sistem memiliki data simulasi semester berikutnya
        $currentYear = date('Y');
        $endYear = $currentYear + 1;

        $this->command->info("Menjana (Generating) Tahun Akademik dari {$startYear} hingga {$endYear}...");

        for ($year = $startYear; $year <= $endYear; $year++) {
            $nextYear = $year + 1;

            // ==========================================
            // 1. SEMESTER GANJIL
            // ==========================================
            TahunAkademik::updateOrCreate(
                ['kode_tahun' => $year . '1'],
                [
                    'nama_tahun' => "Ganjil {$year}/{$nextYear}",
                    'semester' => 1,

                    // Global
                    'tanggal_mulai' => "{$year}-09-01",
                    'tanggal_selesai' => "{$nextYear}-01-31",

                    // KRS
                    'tgl_mulai_krs' => "{$year}-08-20",
                    'tgl_selesai_krs' => "{$year}-09-10",

                    // Perkuliahan
                    'tgl_mulai_perkuliahan' => "{$year}-09-15",
                    'tgl_selesai_perkuliahan' => "{$nextYear}-01-15",

                    // Ujian Tengah Semester (UTS)
                    'tgl_mulai_uts' => "{$year}-11-01",
                    'tgl_selesai_uts' => "{$year}-11-14",

                    // Ujian Akhir Semester (UAS)
                    'tgl_mulai_uas' => "{$nextYear}-01-16",
                    'tgl_selesai_uas' => "{$nextYear}-01-30",

                    // Penilaian & KHS
                    'tgl_mulai_input_nilai' => "{$nextYear}-01-16",
                    'tgl_selesai_input_nilai' => "{$nextYear}-02-05",
                    'tgl_publish_nilai' => "{$nextYear}-02-07",

                    // Toggles & Defaults
                    'is_active' => false,
                    'buka_krs' => false,
                    'is_locked_krs' => false,
                    'buka_input_nilai' => false,
                    'is_locked_nilai' => false,
                ]
            );

            // ==========================================
            // 2. SEMESTER GENAP
            // ==========================================
            TahunAkademik::updateOrCreate(
                ['kode_tahun' => $year . '2'],
                [
                    'nama_tahun' => "Genap {$year}/{$nextYear}",
                    'semester' => 2,

                    // Global
                    'tanggal_mulai' => "{$nextYear}-02-01",
                    'tanggal_selesai' => "{$nextYear}-07-31",

                    // KRS
                    'tgl_mulai_krs' => "{$nextYear}-01-20",
                    'tgl_selesai_krs' => "{$nextYear}-02-10",

                    // Perkuliahan
                    'tgl_mulai_perkuliahan' => "{$nextYear}-02-15",
                    'tgl_selesai_perkuliahan' => "{$nextYear}-06-15",

                    // Ujian Tengah Semester (UTS)
                    'tgl_mulai_uts' => "{$nextYear}-04-01",
                    'tgl_selesai_uts' => "{$nextYear}-04-14",

                    // Ujian Akhir Semester (UAS)
                    'tgl_mulai_uas' => "{$nextYear}-06-16",
                    'tgl_selesai_uas' => "{$nextYear}-06-30",

                    // Penilaian & KHS
                    'tgl_mulai_input_nilai' => "{$nextYear}-06-16",
                    'tgl_selesai_input_nilai' => "{$nextYear}-07-05",
                    'tgl_publish_nilai' => "{$nextYear}-07-07",

                    // Toggles & Defaults
                    'is_active' => false,
                    'buka_krs' => false,
                    'is_locked_krs' => false,
                    'buka_input_nilai' => false,
                    'is_locked_nilai' => false,
                ]
            );
        }

        // ==========================================
        // 3. AUTO-ACTIVATE BERDASARKAN TANGGAL HARI INI
        // ==========================================
        $today = Carbon::now();
        $activeTa = TahunAkademik::where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->orderBy('kode_tahun', 'desc') // Ambil yang paling relevan
            ->first();

        if ($activeTa) {
            // Logika cerdas: Otomatis buka KRS atau Nilai jika hari ini berada di dalam rentangnya
            $isKrsPhase = $today->between(Carbon::parse($activeTa->tgl_mulai_krs), Carbon::parse($activeTa->tgl_selesai_krs));
            $isNilaiPhase = $today->between(Carbon::parse($activeTa->tgl_mulai_input_nilai), Carbon::parse($activeTa->tgl_selesai_input_nilai));

            $activeTa->update([
                'is_active' => true,
                'buka_krs' => $isKrsPhase,
                'buka_input_nilai' => $isNilaiPhase
            ]);

            $this->command->info("Semester Aktif diset ke: {$activeTa->nama_tahun} ({$activeTa->kode_tahun})");

            if ($isKrsPhase) $this->command->info("- Mode KRS otomatis terbuka sesuai jadwal.");
            if ($isNilaiPhase) $this->command->info("- Mode Input Nilai otomatis terbuka sesuai jadwal.");
        } else {
            // Fallback: Set semester terakhir sebagai aktif jika tidak ada tanggal yang cocok
            $lastTa = TahunAkademik::orderBy('kode_tahun', 'desc')->first();
            if ($lastTa) {
                $lastTa->update(['is_active' => true]);
                $this->command->info("Semester Aktif (Fallback) diset ke: {$lastTa->nama_tahun}");
            }
        }
    }
}
