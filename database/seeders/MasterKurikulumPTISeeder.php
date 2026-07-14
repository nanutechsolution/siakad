<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefProdi as Prodi;
use App\Models\MasterMataKuliah as MataKuliah;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterKurikulumPTISeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding Master Kurikulum PTI 2024...');

        // 1. Ambil Prodi PTI
        $prodi = Prodi::where('kode_prodi_internal', 'PTI')->first();

        if (!$prodi) {
            $this->command->error("Prodi dengan kode 'PTI' tidak ditemukan. Jalankan seeder prodi terlebih dahulu.");
            return;
        }

        $now = Carbon::now();

        // 2. Buat atau Update Master Kurikulum
        $kurikulumId = DB::table('master_kurikulums')->insertGetId([
            'prodi_id'           => $prodi->id,
            'nama_kurikulum'     => 'Kurikulum PTI 2024',
            'tahun_mulai'        => 2024,
            'id_semester_mulai'  => '20241', // Asumsi mulai Ganjil 2024
            'is_active'          => 1,
            'jumlah_sks_lulus'   => 144,
            'jumlah_sks_wajib'   => 138, // Estimasi Wajib
            'jumlah_sks_pilihan' => 6,   // Estimasi Pilihan
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        // 3. Daftar Mapping Mata Kuliah ke Semester
        // sifat_mk: W = Wajib, P = Pilihan
        $kurikulumMataKuliah = [
            // --- SEMESTER 1 ---
            ['kode' => 'PTI24-4101', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4102', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4103', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4104', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4105', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4106', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4107', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4108', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4109', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'PTI24-4110', 'smt' => 1, 'sifat' => 'W'],

            // --- SEMESTER 2 ---
            ['kode' => 'PTI24-4211', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4212', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4213', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4214', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4215', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4216', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4217', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4218', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4219', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'PTI24-4220', 'smt' => 2, 'sifat' => 'W'],

            // --- SEMESTER 3 ---
            ['kode' => 'PTI24-4321', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4322', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4323', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4324', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4325', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4326', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4327', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4328', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'PTI24-4329', 'smt' => 3, 'sifat' => 'W'],

            // --- SEMESTER 4 ---
            ['kode' => 'PTI24-4430', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4431', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4432', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4433', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4434', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4435', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4436', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4437', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'PTI24-4438', 'smt' => 4, 'sifat' => 'P'], // Game Edukasi (Pilihan)

            // --- SEMESTER 5 ---
            ['kode' => 'PTI24-4539', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'PTI24-4540', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'PTI24-4541', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'PTI24-4542', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'PTI24-4543', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'PTI24-4544', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'PTI24-4545', 'smt' => 5, 'sifat' => 'P'], // Publik Speaking (Pilihan)

            // --- SEMESTER 6 ---
            ['kode' => 'PTI24-4647', 'smt' => 6, 'sifat' => 'W'],
            ['kode' => 'PTI24-4648', 'smt' => 6, 'sifat' => 'W'],
            ['kode' => 'PTI24-4649', 'smt' => 6, 'sifat' => 'W'],
            ['kode' => 'PTI24-4650', 'smt' => 6, 'sifat' => 'W'],
            ['kode' => 'PTI24-4651', 'smt' => 6, 'sifat' => 'P'], // Interaksi Komputer dan Manusia (Pilihan)

            // --- SEMESTER 7 ---
            ['kode' => 'PTI24-4751', 'smt' => 7, 'sifat' => 'W'], // Magang/PPL

            // --- SEMESTER 8 ---
            ['kode' => 'PTI24-4852', 'smt' => 8, 'sifat' => 'W'], // Proposal
            ['kode' => 'PTI24-4853', 'smt' => 8, 'sifat' => 'W'], // Skripsi
        ];

        $insertData = [];

        foreach ($kurikulumMataKuliah as $item) {
            // Cari ID mata kuliah berdasarkan kode dan prodi
            $mk = MataKuliah::where('kode_mk', $item['kode'])
                ->where('prodi_id', $prodi->id)
                ->first();

            if ($mk) {
                // Siapkan data pivot
                $insertData[] = [
                    'kurikulum_id'   => $kurikulumId,
                    'mata_kuliah_id' => $mk->id,
                    'semester_paket' => $item['smt'],
                    'sifat_mk'       => $item['sifat'],

                    // Ambil detail SKS langsung dari master mata kuliah
                    'sks_tatap_muka' => $mk->sks_tatap_muka,
                    'sks_praktek'    => $mk->sks_praktek,
                    'sks_lapangan'   => $mk->sks_lapangan,

                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            } else {
                $this->command->warn("Mata Kuliah dengan kode {$item['kode']} tidak ditemukan. Terlewat di relasi kurikulum.");
            }
        }

        // 4. Insert Batch ke tabel Pivot `kurikulum_mata_kuliah`
        if (!empty($insertData)) {
            // Gunakan insertOrIgnore atau hapus dulu data lama jika sudah ada relasinya
            // Karena ini insert awal, kita langsung DB::table insert
            DB::table('kurikulum_mata_kuliah')->insert($insertData);
            $this->command->info("Berhasil mengaitkan " . count($insertData) . " mata kuliah ke Kurikulum PTI 2024.");
        }
    }
}
