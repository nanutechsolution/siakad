<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BankKampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            [
                'nama_bank' => 'BNI',
                'no_rekening' => '1234567890',
                'atas_nama' => 'Rektorat UNMARIS',
                'logo' => null, // Dikosongkan, bisa diupload nanti via Filament Admin
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_bank' => 'BRI',
                'no_rekening' => '0987654321',
                'atas_nama' => 'Yayasan UNMARIS',
                'logo' => null,
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_bank' => 'Bank Mandiri',
                'no_rekening' => '112233445566',
                'atas_nama' => 'PMB UNMARIS',
                'logo' => null,
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama_bank' => 'BCA',
                'no_rekening' => '8877665544',
                'atas_nama' => 'Universitas Maritim',
                'logo' => null,
                'is_active' => 0, // Contoh bank yang sedang tidak aktif / dinonaktifkan
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('bank_kampuses')->insert($banks);
    }
}