<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefRuangSeeder extends Seeder
{
    /**
     * Jalankan database seeds untuk data referensi ruangan.
     */
    public function run(): void
    {
        $ruangan = [
            // Gedung / Kelas A
            ['kode_ruang' => 'A1', 'nama_ruang' => 'Ruang A1', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'A2', 'nama_ruang' => 'Ruang A2', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'A3', 'nama_ruang' => 'Ruang A3', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'A4', 'nama_ruang' => 'Ruang A4', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'A5', 'nama_ruang' => 'Ruang A5', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],

            // Laboratorium
            ['kode_ruang' => 'LAB.APLIKASI', 'nama_ruang' => 'Lab Aplikasi', 'kapasitas' => 30, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'LAB.JARINGAN', 'nama_ruang' => 'Lab Jaringan', 'kapasitas' => 30, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'LAB.MM.I', 'nama_ruang' => 'Lab Multimedia I', 'kapasitas' => 30, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'LAB.MM.II', 'nama_ruang' => 'Lab Multimedia II', 'kapasitas' => 30, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],

            // Ruang Kelas R.K3
            ['kode_ruang' => 'R.K3.A', 'nama_ruang' => 'Ruang K3 A', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'R.K3.B', 'nama_ruang' => 'Ruang K3 B', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],

            // Ruang Kelas R.B & R.BD
            ['kode_ruang' => 'R.B23', 'nama_ruang' => 'Ruang B23', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'R.B24', 'nama_ruang' => 'Ruang B24', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
            ['kode_ruang' => 'R.BD', 'nama_ruang' => 'Ruang BD', 'kapasitas' => 40, 'is_active' => true, 'latitude' => -9.422550485384914, 'longitude' => 119.24808411164993, 'radius_meter' => 50],
        ];

        foreach ($ruangan as $r) {
            DB::table('ref_ruang')->updateOrInsert(
                ['kode_ruang' => $r['kode_ruang']],
                [
                    'nama_ruang' => $r['nama_ruang'],
                    'kapasitas' => $r['kapasitas'],
                    'is_active' => $r['is_active'],
                    'latitude'     => $r['latitude'],    
                    'longitude'    => $r['longitude'],   
                    'radius_meter' => $r['radius_meter'],
                ]
            );
        }
    }
}
