<?php
namespace App\Observers;

use App\Enums\StatusSesiEnum;
use App\Models\KrsDetail;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use Illuminate\Support\Str;

class PerkuliahanSesiObserver
{
    public function updated(PerkuliahanSesi $sesi): void
    {
        // Deteksi jika sesi baru saja di-trigger menjadi DIBUKA
        if ($sesi->wasChanged('status_sesi') && $sesi->status_sesi === StatusSesiEnum::DIBUKA) {
            
            // Ambil semua KRS Detail (Mahasiswa) yang mengambil jadwal ini dan statusnya disetujui ('B' / 'S')
            // Catatan: Di database, krs_detail berelasi dengan jadwal_kuliah_id
            $krsDetails = KrsDetail::where('jadwal_kuliah_id', $sesi->jadwal_kuliah_id)
                ->whereIn('status_ambil', ['B', 'S']) // Asumsi 'B' = Baru, 'S' = Sah/Disetujui
                ->get();

            $absensiRecords = [];
            $now = now();

            foreach ($krsDetails as $krsd) {
                $absensiRecords[] = [
                    'id' => Str::uuid()->toString(),
                    'perkuliahan_sesi_id' => $sesi->id,
                    'krs_detail_id' => $krsd->id,
                    'status_kehadiran' => 'A', // Default Alpa, menunggu update QR atau Dosen
                    'is_manual_update' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Insert massal untuk efisiensi
            if (!empty($absensiRecords)) {
                PerkuliahanAbsensi::insert($absensiRecords);
            }
        }
    }
}