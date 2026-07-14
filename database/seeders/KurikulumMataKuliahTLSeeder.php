<?php

namespace Database\Seeders;

use App\Models\RefProdi as Prodi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KurikulumMataKuliahTLSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = Prodi::where('kode_prodi_internal', 'TL')->first();
        if (!$prodi) {
            $this->command->error("Prodi TL tidak ditemukan.");
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

        $mapping = [
            1 => ['MKWN2530201', 'MKWN2530202', 'MKWN2530203', 'MKWN2530204', 'MKTL2530201', 'MKTL2530202', 'MKTL2530203', 'MKTL2530204', 'MKTL2530205'],
            2 => ['MKWN2530205', 'MKTL2530206', 'MKTL2530207', 'MKTL2530208', 'MKTL2530209', 'MKTL2530210', 'MKTL2530211', 'MKTL2530212'],
            3 => ['MKTL2530213', 'MKTL2530214', 'MKTL2530215', 'MKTL2530216', 'MKTL2530217', 'MKTL2530218', 'MKTL2530219', 'MKTL2530220', 'MKTL2530221'],
            4 => ['MKTL2530222', 'MKTL2530223', 'MKTL2530224', 'MKTL2530225', 'MKTL2530226', 'MKTL2530227', 'MKTL2530228', 'MKTL2530229', 'MKTL2530230'],
            5 => ['MKTL2530231', 'MKTL2530232', 'MKTL2530233', 'MKTL2530234', 'MKTL2530235', 'MKTL2530236', 'MKTL2530237', 'MKTL2530238', 'MKTL2530239'],
            6 => ['MKTL2530240', 'MKTL2530241', 'MKTL2530242', 'MKTL2530243', 'MKTL2530244', 'MKTL2530245', 'MKTL2530246', 'MKTL2530247', 'MKTL2530248'],
            7 => ['MKTL2530249', 'MKTL2530250'],
            8 => ['MKTL2530251', 'MKTL2530252'],
        ];

        $now = Carbon::now();
        foreach ($mapping as $semester => $kodeMks) {
            foreach ($kodeMks as $kode) {
                $mk = DB::table('master_mata_kuliahs')->where('kode_mk', $kode)->first();
                if ($mk) {
                    DB::table('kurikulum_mata_kuliah')->updateOrInsert(
                        ['kurikulum_id' => $kurikulum->id, 'mata_kuliah_id' => $mk->id],
                        [
                            'semester_paket' => $semester,
                            'sks_tatap_muka' => $mk->sks_tatap_muka,
                            'sks_praktek'    => $mk->sks_praktek,
                            'sks_lapangan'   => 0,
                            'sifat_mk'       => 'W',
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]
                    );
                }
            }
        }
    }
}
