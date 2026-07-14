<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\RefProdi as Prodi;
use Carbon\Carbon;

class KurikulumMataKuliahArsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding Relasi Kurikulum Mata Kuliah ARS...');

        // 1. Ambil Prodi ARS
        $prodi = Prodi::where('kode_prodi_internal', 'ARS')->first();
        if (!$prodi) {
            $this->command->error("Prodi ARS tidak ditemukan.");
            return;
        }

        // 2. Ambil Master Kurikulum ARS yang aktif
        $kurikulum = DB::table('master_kurikulums')
            ->where('prodi_id', $prodi->id)
            ->where('is_active', 1)
            ->first();

        if (!$kurikulum) {
            $this->command->error("Master Kurikulum ARS aktif tidak ditemukan. Harap jalankan MasterKurikulumArsSeeder terlebih dahulu.");
            return;
        }

        // 3. Daftar Mata Kuliah beserta penempatan semesternya
        $courses = [
            // --- SEMESTER 1 ---
            ['kode' => 'ARS-113261001', 'smt' => 1],
            ['kode' => 'ARS-113261002', 'smt' => 1],
            ['kode' => 'ARS-113261003', 'smt' => 1],
            ['kode' => 'ARS-113261004', 'smt' => 1],
            ['kode' => 'ARS-113261005', 'smt' => 1],
            ['kode' => 'ARS-113261006', 'smt' => 1],
            ['kode' => 'ARS-113261007', 'smt' => 1],
            ['kode' => 'ARS-113261008', 'smt' => 1],
            ['kode' => 'ARS-113261009', 'smt' => 1],
            ['kode' => 'ARS-113261010', 'smt' => 1],

            // --- SEMESTER 2 ---
            ['kode' => 'ARS-213261001', 'smt' => 2],
            ['kode' => 'ARS-213261002', 'smt' => 2],
            ['kode' => 'ARS-213261003', 'smt' => 2],
            ['kode' => 'ARS-213261004', 'smt' => 2],
            ['kode' => 'ARS-213261005', 'smt' => 2],
            ['kode' => 'ARS-213261006', 'smt' => 2],
            ['kode' => 'ARS-213261007', 'smt' => 2],
            ['kode' => 'ARS-213261008', 'smt' => 2],
            ['kode' => 'ARS-2113261009', 'smt' => 2],
            ['kode' => 'ARS-2113261010', 'smt' => 2],

            // --- SEMESTER 3 ---
            ['kode' => 'ARS-3113261001', 'smt' => 3],
            ['kode' => 'ARS-3113261002', 'smt' => 3],
            ['kode' => 'ARS-3113261003', 'smt' => 3],
            ['kode' => 'ARS-313261004', 'smt' => 3],
            ['kode' => 'ARS-3113261005', 'smt' => 3],
            ['kode' => 'ARS-313261006', 'smt' => 3],
            ['kode' => 'ARS-3113261007', 'smt' => 3],
            ['kode' => 'ARS-313261008', 'smt' => 3],

            // --- SEMESTER 4 ---
            ['kode' => 'ARS-4113261001', 'smt' => 4],
            ['kode' => 'ARS-4113261002', 'smt' => 4],
            ['kode' => 'ARS-4113261003', 'smt' => 4],
            ['kode' => 'ARS-4113261004', 'smt' => 4],
            ['kode' => 'ARS-413261005', 'smt' => 4],
            ['kode' => 'ARS-4113261006', 'smt' => 4],
            ['kode' => 'ARS-413261007', 'smt' => 4],

            // --- SEMESTER 5 ---
            ['kode' => 'ARS-5113261001', 'smt' => 5],
            ['kode' => 'ARS-513261002', 'smt' => 5],
            ['kode' => 'ARS-5113261003', 'smt' => 5],
            ['kode' => 'ARS-5113261004', 'smt' => 5],
            ['kode' => 'ARS-5113261005', 'smt' => 5],
            ['kode' => 'ARS-513261006', 'smt' => 5],
            ['kode' => 'ARS-5113261007', 'smt' => 5],
            ['kode' => 'ARS-5113261008', 'smt' => 5],

            // --- SEMESTER 6 ---
            ['kode' => 'ARS-6113261001', 'smt' => 6],
            ['kode' => 'ARS-613261002', 'smt' => 6],
            ['kode' => 'ARS-6113261003', 'smt' => 6],
            ['kode' => 'ARS-613261004', 'smt' => 6],
            ['kode' => 'ARS-6113261005', 'smt' => 6],
            ['kode' => 'ARS-613261006', 'smt' => 6],
            ['kode' => 'ARS-6113261007', 'smt' => 6],
            ['kode' => 'ARS-6113261008', 'smt' => 6],
            ['kode' => 'ARS-6113261009', 'smt' => 6],

            // --- SEMESTER 7 ---
            ['kode' => 'ARS-7113261008', 'smt' => 7],

            // --- SEMESTER 8 ---
            ['kode' => 'ARS-8113261002', 'smt' => 8],
        ];

        $count = 0;

        foreach ($courses as $item) {
            // Ambil ID dan data SKS dari master_mata_kuliahs berdasarkan kode_mk
            $mk = DB::table('master_mata_kuliahs')
                ->where('kode_mk', $item['kode'])
                ->where('prodi_id', $prodi->id)
                ->first();

            if ($mk) {
                // Gunakan updateOrInsert untuk mencegah duplikasi jika seeder dijalankan ulang
                DB::table('kurikulum_mata_kuliah')->updateOrInsert(
                    [
                        'kurikulum_id'   => $kurikulum->id,
                        'mata_kuliah_id' => $mk->id,
                    ],
                    [
                        'semester_paket' => $item['smt'],
                        'sks_tatap_muka' => $mk->sks_tatap_muka,
                        'sks_praktek'    => $mk->sks_praktek,
                        'sks_lapangan'   => $mk->sks_lapangan,
                        'sifat_mk'       => 'W', // Semua diset Wajib ('W') sesuai kurikulum dasar KPT 4.0
                        'created_at'     => Carbon::now(),
                        'updated_at'     => Carbon::now(),
                    ]
                );
                $count++;
            } else {
                $this->command->warn("Mata Kuliah dengan kode {$item['kode']} tidak ditemukan di master. Dilewati.");
            }
        }

        $this->command->info("Berhasil merelasikan {$count} Mata Kuliah ke Kurikulum ARS.");
    }
}
