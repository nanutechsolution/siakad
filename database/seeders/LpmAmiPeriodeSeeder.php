<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LpmAmiPeriodeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $periodes = [
            [
                'nama_periode'     => 'AMI Tahun 2024',
                'tahun'            => 2024,
                'tanggal_mulai'    => '2024-01-01',
                'tanggal_selesai'  => '2024-12-31',
                'tgl_mulai'        => '2024-01-01',
                'tgl_selesai'      => '2024-12-31',
                'is_active'        => false,
                'status'           => 'FINISHED',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'nama_periode'     => 'AMI Tahun 2025',
                'tahun'            => 2025,
                'tanggal_mulai'    => '2025-01-01',
                'tanggal_selesai'  => '2025-12-31',
                'tgl_mulai'        => '2025-01-01',
                'tgl_selesai'      => '2025-12-31',
                'is_active'        => false,
                'status'           => 'FINISHED',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'nama_periode'     => 'AMI Tahun 2026',
                'tahun'            => 2026,
                'tanggal_mulai'    => '2026-01-01',
                'tanggal_selesai'  => '2026-12-31',
                'tgl_mulai'        => '2026-01-01',
                'tgl_selesai'      => '2026-12-31',
                'is_active'        => true,
                'status'           => 'ON-GOING',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'nama_periode'     => 'AMI Tahun 2027',
                'tahun'            => 2027,
                'tanggal_mulai'    => '2027-01-01',
                'tanggal_selesai'  => '2027-12-31',
                'tgl_mulai'        => '2027-01-01',
                'tgl_selesai'      => '2027-12-31',
                'is_active'        => false,
                'status'           => 'DRAFT',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        DB::table('lpm_ami_periodes')->upsert(
            $periodes,
            ['nama_periode'],
            [
                'tahun',
                'tanggal_mulai',
                'tanggal_selesai',
                'tgl_mulai',
                'tgl_selesai',
                'is_active',
                'status',
                'updated_at',
            ]
        );
    }
}