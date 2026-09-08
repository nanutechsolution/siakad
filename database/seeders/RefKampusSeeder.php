<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefKampus;

class RefKampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataKampus = [
            [
                'kode_kampus' => 'KMP-01',
                'nama_kampus' => 'Kampus Utama Tambolaka',
                'alamat'      => 'Tambolaka, Sumba Barat Daya',
                'is_active'   => true,
            ],
            [
                'kode_kampus' => 'KMP-02',
                'nama_kampus' => 'Kampus Waikabubak',
                'alamat'      => 'Waikabubak, Sumba Barat',
                'is_active'   => true,
            ],
        ];

        foreach ($dataKampus as $kampus) {
            // Menggunakan updateOrCreate agar aman jika seeder dijalankan berulang kali (tidak akan duplikat)
            RefKampus::updateOrCreate(
                ['kode_kampus' => $kampus['kode_kampus']],
                $kampus
            );
        }

        $this->command->info('✅ Data Kampus Utama & Waikabubak berhasil ditambahkan!');
    }
}
