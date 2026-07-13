<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefStatusVerifikasiPembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'kode' => 'PENDING', 'nama' => 'Menunggu Verifikasi', 'deskripsi' => 'Pembayaran baru masuk dan belum diperiksa oleh staf keuangan.', 'is_final' => 0],
            ['id' => 2, 'kode' => 'VERIFIED', 'nama' => 'Terverifikasi', 'deskripsi' => 'Pembayaran telah dikonfirmasi dan dialokasikan ke tagihan/saldo.', 'is_final' => 1],
            ['id' => 3, 'kode' => 'REJECTED', 'nama' => 'Ditolak', 'deskripsi' => 'Pembayaran ditolak, tidak ada mutasi tagihan/saldo yang terjadi.', 'is_final' => 1],
        ];

        foreach ($rows as $row) {
            DB::table('ref_status_verifikasi_pembayaran')->updateOrInsert(
                ['id' => $row['id']],
                [
                    'kode' => $row['kode'],
                    'nama' => $row['nama'],
                    'deskripsi' => $row['deskripsi'],
                    'is_final' => $row['is_final'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
