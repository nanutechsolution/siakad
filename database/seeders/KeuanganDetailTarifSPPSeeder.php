<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefProdi;
use App\Models\KeuanganSkemaTarif;
use Illuminate\Support\Facades\DB;

class KeuanganDetailTarifSPPSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil ID komponen SPP
        $komponenSpp = DB::table('keuangan_komponen_biaya')
            ->where('kode_komponen', 'SPP')
            ->first();

        if (!$komponenSpp) {
            $this->command->error('Komponen SPP tidak ditemukan!');
            return;
        }

        // 2. Mapping data dari gambar
        // Menggunakan kode_internal sebagai key untuk mencocokkan dengan tabel RefProdi
        $daftarTarif = [
            'MI'  => 2000000,
            'TI'  => 3500000,
            'TL'  => 3000000,
            'PTI' => 3000000,
            'BD'  => 3000000,
            'ARS' => 3000000,
            'K3'  => 3000000,
        ];

        $count = 0;

        foreach ($daftarTarif as $kodeInternal => $nominal) {
            // Ambil Prodi ID
            $prodi = RefProdi::where('kode_prodi_internal', $kodeInternal)->first();

            if ($prodi) {
                // Ambil Skema Tarif untuk prodi ini
                $skema = KeuanganSkemaTarif::where('prodi_id', $prodi->id)->first();

                if ($skema) {
                    DB::table('keuangan_detail_tarif')->updateOrInsert(
                        [
                            'skema_tarif_id' => $skema->id,
                            'komponen_biaya_id' => $komponenSpp->id,
                        ],
                        [
                            'nominal' => $nominal,
                            'penerapan' => 'FLAT',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                    $count++;
                }
            }
        }

        $this->command->info("Berhasil meng-generate {$count} data tarif SPP sesuai daftar.");
    }
}
