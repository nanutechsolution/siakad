<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterKurikulumTLSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = DB::table('ref_prodi')->where('kode_prodi_internal', 'TL')->first();
        if (!$prodi) return;

        DB::table('master_kurikulums')->updateOrInsert(
            ['prodi_id' => $prodi->id, 'nama_kurikulum' => 'Kurikulum TL Berbasis OBE'],
            [
                'tahun_mulai'        => 2025,
                'id_semester_mulai'  => '20251',
                'is_active'          => 1,
                'jumlah_sks_lulus'   => 144,
                'jumlah_sks_wajib'   => 144,
                'jumlah_sks_pilihan' => 0,
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ]
        );
    }
}