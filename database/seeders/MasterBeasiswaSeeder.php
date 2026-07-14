<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KeuanganMasterBeasiswa;

class MasterBeasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $beasiswas = [
            [
                'nama_beasiswa' => 'Beasiswa Yayasan 100%',
                'kategori'      => 'INTERNAL',
                'keterangan'    => 'Pembebasan biaya SPP secara penuh dari Yayasan untuk mahasiswa terpilih.',
                'is_active'     => true,
            ],
            [
                'nama_beasiswa' => 'Beasiswa Prestasi Akademik',
                'kategori'      => 'INTERNAL',
                'keterangan'    => 'Potongan biaya kuliah untuk mahasiswa dengan IPK tertinggi di prodinya.',
                'is_active'     => true,
            ],
            [
                'nama_beasiswa' => 'Diskon Anak Pegawai / Dosen',
                'kategori'      => 'INTERNAL',
                'keterangan'    => 'Diskon otomatis untuk mahasiswa yang merupakan anak kandung dari pegawai tetap.',
                'is_active'     => true,
            ],
            [
                'nama_beasiswa' => 'KIP-Kuliah (Pemerintah)',
                'kategori'      => 'PEMERINTAH',
                'keterangan'    => 'Beasiswa Kartu Indonesia Pintar Kuliah dari Kemdikbudristek.',
                'is_active'     => true,
            ],
            [
                'nama_beasiswa' => 'Beasiswa CSR Bank Mitra',
                'kategori'      => 'EKSTERNAL',
                'keterangan'    => 'Beasiswa bantuan pendidikan dari CSR Bank yang bekerja sama dengan kampus.',
                'is_active'     => true,
            ],
        ];

        foreach ($beasiswas as $beasiswa) {
            // Menggunakan firstOrCreate agar aman jika dijalankan berulang kali (idempotent)
            KeuanganMasterBeasiswa::firstOrCreate(
                ['nama_beasiswa' => $beasiswa['nama_beasiswa']],
                $beasiswa
            );
        }
        
        $this->command->info('Seeder Master Beasiswa berhasil dijalankan.');
    }
}