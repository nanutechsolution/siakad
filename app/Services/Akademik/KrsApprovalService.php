<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Enums\KrsStatusEnum;
use App\Models\Krs;
use App\Models\JadwalKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class KrsApprovalService
{
    public function __construct(
        protected KrsValidationService $validationService
    ) {}

    public function approve(Krs $krs, ?string $catatanDosen = null): void
    {
        DB::beginTransaction();

        try {
            $krsLocked = Krs::where('id', $krs->id)->lockForUpdate()->first();

            if ($krsLocked->status_krs === KrsStatusEnum::DISETUJUI) {
                throw new Exception('KRS ini sudah disetujui sebelumnya.');
            }

            $jadwalIds = $krsLocked->krsDetails()->pluck('jadwal_kuliah_id')->filter()->toArray();
            $totalSks = $krsLocked->krsDetails()->sum('sks_snapshot');

            // 1. Lock Jadwal Kuliah untuk mencegah Bentrok Kuota
            if (!empty($jadwalIds)) {
                $jadwals = JadwalKuliah::whereIn('id', $jadwalIds)->lockForUpdate()->get();
                foreach ($jadwals as $jadwal) {
                    if ($jadwal->isi_kelas >= $jadwal->kuota_kelas) {
                        throw new Exception("Gagal menyetujui. Kuota kelas untuk mata kuliah ID {$jadwal->mata_kuliah_id} baru saja penuh.");
                    }
                    $jadwal->increment('isi_kelas');
                }
            }

            // 2. Update Status dan Kolom Tracking Baru
            $krsLocked->status_krs = KrsStatusEnum::DISETUJUI;
            $krsLocked->disetujui_oleh = Auth::id(); // Menggunakan User ID (Sesuai FK users)
            $krsLocked->disetujui_pada = now();
            $krsLocked->total_sks_diambil = $totalSks; // Menyimpan total SKS ke kolom baru

            if ($catatanDosen) {
                $krsLocked->catatan_admin = $catatanDosen; // Asumsi kolom ini digunakan untuk menyimpan catatan
            }

            $krsLocked->save();

            // 3. Catat Audit Trail
            activity('krs_approval')->performedOn($krsLocked)->event('approved')
                ->withProperties(['catatan_dosen' => $catatanDosen, 'total_sks' => $totalSks])
                ->log('KRS disetujui oleh Dosen Wali.');

            DB::table('krs_status_logs')->insert([
                'krs_id'         => $krsLocked->id,
                'aksi'           => 'DISETUJUI',
                'dilakukan_oleh' => Auth::id(),
                'before_data'    => json_encode(['status_krs' => KrsStatusEnum::DIAJUKAN->value]),
                'after_data'     => json_encode([
                    'status_krs' => KrsStatusEnum::DISETUJUI->value,
                    'total_sks'  => $totalSks
                ]),
                'catatan'        => $catatanDosen ?? 'Disetujui oleh Dosen Wali',
                'created_at'     => now(),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('KRS Approval Failed: ' . $e->getMessage(), ['krs_id' => $krs->id]);
            throw $e;
        }
    }

    public function reject(Krs $krs, string $catatanDosen): void
    {
        if (empty(trim($catatanDosen))) {
            throw new Exception('Catatan penolakan wajib diisi.');
        }

        DB::beginTransaction();

        try {
            $krsLocked = Krs::where('id', $krs->id)->lockForUpdate()->first();

            // Update Status dan Kolom Tracking Penolakan
            $krsLocked->status_krs = KrsStatusEnum::DITOLAK;
            $krsLocked->ditolak_oleh = Auth::id();
            $krsLocked->ditolak_pada = now();
            $krsLocked->catatan_admin = $catatanDosen; // Menyimpan alasan penolakan
            $krsLocked->save();

            activity('krs_approval')->performedOn($krsLocked)->event('rejected')
                ->withProperties(['alasan_penolakan' => $catatanDosen])
                ->log('KRS ditolak oleh Dosen Wali.');
            // 3. Catat Audit Trail Domain-Specific
            DB::table('krs_status_logs')->insert([
                'krs_id'         => $krsLocked->id,
                'aksi'           => 'DITOLAK',
                'dilakukan_oleh' => Auth::id(),
                'before_data'    => json_encode(['status_krs' => KrsStatusEnum::DIAJUKAN->value]),
                'after_data'     => json_encode(['status_krs' => KrsStatusEnum::DITOLAK->value]),
                'catatan'        => $catatanDosen, // Wajib diisi sesuai validasi kita
                'created_at'     => now(),
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('KRS Rejection Failed: ' . $e->getMessage(), ['krs_id' => $krs->id]);
            throw $e;
        }
    }
}
