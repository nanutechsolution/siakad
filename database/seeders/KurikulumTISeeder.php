<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterKurikulum as Kurikulum;
use App\Models\MasterMataKuliah as MataKuliah;
use App\Models\RefProdi as Prodi;
use App\Models\KurikulumMataKuliah;

class KurikulumTISeeder extends Seeder
{
    /**
     * Seeder untuk Pemetaan Kurikulum TI (UNMARIS)
     */
    public function run(): void
    {
        $this->command->info('Memproses Pemetaan Kurikulum TI 2023-2028...');

        // 1. Ambil Prodi Teknik Informatika (Sesuaikan kode_prodi_internal Anda)
        $prodi = Prodi::where('kode_prodi_internal', 'TI')->first();

        if (!$prodi) {
            $this->command->error("Prodi TI tidak ditemukan. Pastikan MasterMataKuliahSeeder sudah dijalankan.");
            return;
        }

        // 2. Buat/Update Header Kurikulum
        $kurikulum = Kurikulum::updateOrCreate(
            [
                'prodi_id' => $prodi->id,
                'nama_kurikulum' => 'Kurikulum TI 2023'
            ],
            [
                'tahun_mulai' => 2023,
                'id_semester_mulai' => '20231',
                'jumlah_sks_lulus' => 144, // Standar S1
                'is_active' => true,
            ]
        );

        // 3. Definisi Struktur Mata Kuliah (Data Riil Excel)
        // 'pre' diisi dengan array kode MK prasyarat jika ada
        $mappings = [
            // --- SEMESTER 1 ---
            ['kode' => 'TI1120001', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'TI1120002', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'TI1120003', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'TI1120004', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'TI1120005', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'TI1120006', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'TI1120007', 'smt' => 1, 'sifat' => 'W'],
            ['kode' => 'TI1120008', 'smt' => 1, 'sifat' => 'W'],

            // --- SEMESTER 2 ---
            ['kode' => 'TI2120009', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'TI2120010', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'TI2120011', 'smt' => 2, 'sifat' => 'W', 'pre' => ['TI1120005']], // Java prasyarat Dasar Progres
            ['kode' => 'TI2120012', 'smt' => 2, 'sifat' => 'W', 'pre' => ['TI1120003']], // Arsitektur prasyarat PTI
            ['kode' => 'TI2120013', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'TI2120014', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'TI2120015', 'smt' => 2, 'sifat' => 'W'],
            ['kode' => 'TI2120016', 'smt' => 2, 'sifat' => 'W', 'pre' => ['TI1120008']], // Inggris Progres prasyarat Inggris I

            // --- SEMESTER 3 ---
            ['kode' => 'TI3120017', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'TI3120018', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'TI3120019', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'TI3120020', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'TI3120021', 'smt' => 3, 'sifat' => 'W'],
            ['kode' => 'TI3120022', 'smt' => 3, 'sifat' => 'W', 'pre' => ['TI1120005', 'TI2120011']], // C++ prasyarat Java/C
            ['kode' => 'TI3120023', 'smt' => 3, 'sifat' => 'W', 'pre' => ['TI2120013']], // Jaringan prasyarat Konsep Jaringan
            ['kode' => 'TI3120024', 'smt' => 3, 'sifat' => 'W'],

            // --- SEMESTER 4 ---
            ['kode' => 'TI4120025', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'TI4120026', 'smt' => 4, 'sifat' => 'W', 'pre' => ['TI3120022']], // RPL prasyarat PBO/C++
            ['kode' => 'TI4120027', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'TI4120028', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'TI4120029', 'smt' => 4, 'sifat' => 'W'],
            ['kode' => 'TI4120030', 'smt' => 4, 'sifat' => 'W', 'pre' => ['TI2120010']], // Grafika prasyarat Aljabar
            ['kode' => 'TI4120031', 'smt' => 4, 'sifat' => 'W'],

            // --- SEMESTER 5 ---
            ['kode' => 'TI5120033', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'TI5120034', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'TI5120035', 'smt' => 5, 'sifat' => 'W', 'pre' => ['TI2120015']], // GIS prasyarat Basis Data
            ['kode' => 'TI5120036', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'TI5120037', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'TI5120038', 'smt' => 5, 'sifat' => 'W'],
            ['kode' => 'TI5120039', 'smt' => 5, 'sifat' => 'W'],

            // --- SEMESTER 6 ---
            ['kode' => 'TI6120040', 'smt' => 6, 'sifat' => 'W'],
            ['kode' => 'TI6120041', 'smt' => 6, 'sifat' => 'W'],
            ['kode' => 'TI6120042', 'smt' => 6, 'sifat' => 'P'],
            ['kode' => 'TI6120043', 'smt' => 6, 'sifat' => 'P'],
            ['kode' => 'TI6120044', 'smt' => 6, 'sifat' => 'P'],
            ['kode' => 'TI6120045', 'smt' => 6, 'sifat' => 'W'],
            ['kode' => 'TI6120046', 'smt' => 6, 'sifat' => 'W'],

            // --- SEMESTER 7 & 8 ---
            ['kode' => 'TI7120047', 'smt' => 7, 'sifat' => 'W'], // Jalur Pilihan
            ['kode' => 'TI8120048', 'smt' => 8, 'sifat' => 'W', 'pre' => ['TI6120041']], // Skripsi prasyarat Metopen
        ];

        // 4. Proses Sinkronisasi   
        $totalWajib = 0;
        $totalPilihan = 0;

        foreach ($mappings as $map) {
            // Cari MK berdasarkan kode_mk dan prodi_id
            $mk = MataKuliah::where('kode_mk', $map['kode'])
                ->where('prodi_id', $prodi->id)
                ->first();

            if (!$mk) {
                // DEBUG: Cek apakah kode ini sebenarnya ada di tabel tapi prodi_id-nya beda?
                $cekTanpaProdi = MataKuliah::where('kode_mk', $map['kode'])->first();

                if ($cekTanpaProdi) {
                    $this->command->error("Kode {$map['kode']} ADA, tapi prodi_id salah! Harusnya {$prodi->id}, di DB isinya {$cekTanpaProdi->prodi_id}");
                } else {
                    $this->command->warn("Mata kuliah {$map['kode']} benar-benar TIDAK ADA di tabel master_mata_kuliahs.");
                }
                continue;
            }
            // A. Hubungkan Kurikulum dengan Mata Kuliah (Pivot)
            $kurMk = KurikulumMataKuliah::updateOrCreate(
                [
                    'kurikulum_id' => $kurikulum->id,
                    'mata_kuliah_id' => $mk->id
                ],
                [
                    'semester_paket' => $map['smt'],
                    'sks_tatap_muka' => $mk->sks_tatap_muka,
                    'sks_praktek'    => $mk->sks_praktek,
                    'sks_lapangan'   => $mk->sks_lapangan,
                    'sifat_mk'       => $map['sifat'], // W (Wajib) / P (Pilihan)
                ]
            );

            // B. Sinkronisasi Prasyarat (Many-to-Many)
            if (isset($map['pre']) && !empty($map['pre'])) {
                $syncData = [];
                foreach ($map['pre'] as $preKode) {
                    $preMk = MataKuliah::where('kode_mk', $preKode)->where('prodi_id', $prodi->id)->first();
                    if ($preMk) {
                        // Memetakan prasyarat ke tabel pivot prasyarat (Many-to-Many)
                        $syncData[$preMk->id] = ['min_nilai_huruf' => 'D'];
                    }
                }
                // Pastikan model KurikulumMataKuliah punya relasi prasyarats()
                if (method_exists($kurMk, 'prasyarats')) {
                    $kurMk->prasyarats()->sync($syncData);
                }
            }

            // Akumulasi SKS untuk Header
            $sks = $mk->sks_default;
            if ($map['sifat'] == 'W') {
                $totalWajib += $sks;
            } else {
                $totalPilihan += $sks;
            }
        }

        // 5. Update Total SKS di Header Kurikulum
        $kurikulum->update([
            'jumlah_sks_wajib' => $totalWajib,
            'jumlah_sks_pilihan' => $totalPilihan
        ]);

        $this->command->info("Penyusunan Kurikulum TI Selesai.");
        $this->command->info("Total: {$totalWajib} SKS Wajib & {$totalPilihan} SKS Pilihan.");
    }
}
