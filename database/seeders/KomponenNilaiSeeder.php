<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MasterKurikulum as Kurikulum;

class KomponenNilaiSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk mengisi komponen nilai dan bobot kurikulum.
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding Komponen Nilai Dinamis...');

        // 1. Data Master Komponen Nilai
        $komponens = [
            ['nama' => 'Kehadiran', 'slug' => 'kehadiran'],
            ['nama' => 'Tugas', 'slug' => 'tugas'],
            ['nama' => 'UTS',   'slug' => 'uts'],
            ['nama' => 'Quiz',  'slug' => 'quiz'],
            ['nama' => 'UAS',   'slug' => 'uas'],
        ];

        $mapKomponenId = [];

        foreach ($komponens as $k) {
            $id = DB::table('ref_komponen_nilai')->updateOrInsert(
                ['slug' => $k['slug']],
                [
                    'nama_komponen' => $k['nama'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Ambil ID-nya untuk pemetaan bobot nanti
            $mapKomponenId[$k['slug']] = DB::table('ref_komponen_nilai')
                ->where('slug', $k['slug'])
                ->value('id');
        }

        $this->command->info('Master komponen berhasil dibuat.');

        // 2. Mapping Bobot sesuai permintaan Warek 1 ke Kurikulum yang ada
        // Kita ambil semua kurikulum yang aktif
        $allKurikulum = Kurikulum::all();

        if ($allKurikulum->isEmpty()) {
            $this->command->warn('Tidak ada kurikulum ditemukan. Pastikan MataKuliahSeeder sudah dijalankan.');
            return;
        }

        // Definisi Bobot (Total 100%)
        $bobotRequest = [
            'kehadiran' => 10.00,
            'tugas' => 15.00,
            'uts'   => 20.00,
            'quiz'  => 25.00,
            'uas'   => 30.00,
        ];

        foreach ($allKurikulum as $kur) {
            $this->command->info("Mengatur bobot untuk: {$kur->nama_kurikulum}");

            foreach ($bobotRequest as $slug => $persen) {
                DB::table('kurikulum_komponen_nilai')->updateOrInsert(
                    [
                        'kurikulum_id' => $kur->id,
                        'komponen_id' => $mapKomponenId[$slug]
                    ],
                    [
                        'bobot_persen' => $persen,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Seeding Bobot Komponen Nilai selesai.');
    }
}
