<?php

namespace Database\Seeders;

use App\Models\RefFakultas as Fakultas;
use App\Models\RefProdi;
use Illuminate\Database\Seeder;

class FakultasProdiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Fakultas
        $fakultas = [
            ['kode' => 'FTEK', 'nama' => 'Fakultas Teknik'],
            ['kode' => 'FKES', 'nama' => 'Fakultas Kesehatan'],
            ['kode' => 'FKG',  'nama' => 'Fakultas Keguruan'],
            ['kode' => 'FEB',  'nama' => 'Fakultas Ekonomi Dan Bisnis'],
        ];

        foreach ($fakultas as $f) {
            Fakultas::updateOrCreate(
                ['kode_fakultas' => $f['kode']],
                ['nama_fakultas' => $f['nama']]
            );
        }

        $this->command->info('Master Fakultas berhasil di-generate.');

        // 2. Ambil ID Fakultas untuk relasi 
        $ft = Fakultas::where('kode_fakultas', 'FTEK')->first();
        $fe = Fakultas::where('kode_fakultas', 'FKES')->first();
        $fh = Fakultas::where('kode_fakultas', 'FKG')->first();
        $fk = Fakultas::where('kode_fakultas', 'FEB')->first();

        // 3. Data Program Studi
        $prodis = [
            // Fakultas Teknik
            [
                'fakultas_id' => $ft->id,
                'kode_internal' => 'MI',
                'kode_dikti' => '57401',
                'nama' => 'Manajemen Informatika',
                'jenjang' => 'D3',
                'gelar' => 'A.Md.Kom',
                'format_nim' => '{THN}57401{NO:3}'
            ],
            [
                'fakultas_id' => $ft->id,
                'kode_internal' => 'TI',
                'kode_dikti' => '55201',
                'nama' => 'Teknik Informatika',
                'jenjang' => 'S1',
                'gelar' => 'S.Kom',
                'format_nim' => '{THN}55201{NO:3}'
            ],
            [
                'fakultas_id' => $ft->id,
                'kode_internal' => 'TL',
                'kode_dikti' => '25302',
                'nama' => 'Teknik Lingkungan',
                'jenjang' => 'S1',
                'gelar' => 'S.T',
                'format_nim' => '{THN}25302{NO:3}'
            ],
            // Fakultas Kesehatan
            [
                'fakultas_id' => $fe->id,
                'kode_internal' => 'K3',
                'kode_dikti' => '13242',
                'nama' => 'Keselamatan dan Kesehatan Kerja (K3)',
                'jenjang' => 'S1',
                'gelar' => 'S.KKK',
                'format_nim' => '{THN}13242{NO:3}'
            ],
            [
                'fakultas_id' => $fe->id,
                'kode_internal' => 'ARS',
                'kode_dikti' => '13261',
                'nama' => 'Administrasi Rumah Sakit',
                'jenjang' => 'S1',
                'gelar' => 'S.ARS',
                'format_nim' => '{THN}13261{NO:3}'
            ],
            // Fakultas Keguruan
            [
                'fakultas_id' => $fh->id,
                'kode_internal' => 'PTI',
                'kode_dikti' => '83207',
                'nama' => 'Pendidikan Teknologi Informasi',
                'jenjang' => 'S1',
                'gelar' => 'S.Pd',
                'format_nim' => '{THN}83207{NO:3}'
            ],
            // Fakultas Ekonomi Dan Bisnis
            [
                'fakultas_id' => $fk->id,
                'kode_internal' => 'BD',
                'kode_dikti' => '61205',
                'nama' => 'Bisnis Digital',
                'jenjang' => 'S1',
                'gelar' => 'S.Bis',
                'format_nim' => '{THN}61205{NO:3}'
            ],
        ];


        foreach ($prodis as $p) {
            RefProdi::updateOrCreate(
                ['kode_prodi_internal' => $p['kode_internal']],
                [
                    'fakultas_id' => $p['fakultas_id'],
                    'kode_prodi_dikti' => $p['kode_dikti'],
                    'nama_prodi' => $p['nama'],
                    'jenjang' => $p['jenjang'],
                    'gelar_lulusan' => $p['gelar'],
                    'format_nim' => $p['format_nim'],
                    'is_active' => true,
                    'last_nim_seq' => 0,
                    'is_paket' => true,
                ]
            );
        }

        $this->command->info('Master Program Studi berhasil di-generate sesuai struktur terbaru.');
    }
}
