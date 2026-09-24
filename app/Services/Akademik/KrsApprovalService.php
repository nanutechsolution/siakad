<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Enums\KrsStatusEnum;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\RefTahunAkademik;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KrsApprovalService
{
    public function __construct(
        protected KrsValidationService $validationService,
    ) {}

    public function canApprove(User $user, Krs $krs): bool
    {
        return $user->can('approve', $krs);
    }

    public function approve(Krs $krs, ?string $catatan = null): void
    {
        $this->assertActorCanDecide($krs);

        DB::transaction(function () use ($krs, $catatan): void {
            $locked = Krs::query()->whereKey($krs->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status_krs !== KrsStatusEnum::DIAJUKAN) {
                throw new Exception('Hanya KRS berstatus Diajukan yang dapat disetujui.');
            }

            if (! $locked->is_financial_verified) {
                $mahasiswa = $locked->mahasiswa;
                $ta = $locked->tahunAkademik;
                $financialGate = ($mahasiswa && $ta)
                    ? $this->validationService->checkKeuangan($mahasiswa, $ta)
                    : null;

                if (! $financialGate?->passed) {
                    throw new Exception($financialGate?->message ?? 'KRS belum lolos verifikasi keuangan.');
                }

                // Sinkronkan flag historis agar tombol approve tidak berbeda
                // dengan hasil gate keuangan yang baru saja dihitung.
                $locked->forceFill(['is_financial_verified' => true])->save();
            }

            $totalSks = (int) $locked->details()->sum('sks_snapshot');

            // KrsDetailObserver TIDAK didaftarkan (lihat AppServiceProvider),
            // jadi `isi_kelas` dijaga HANYA di sini: dicek saat approve dan
            // dikembalikan saat KRS dibatalkan.
            $jadwalIds = $locked->details()->pluck('jadwal_kuliah_id')->filter()->values()->all();

            if ($jadwalIds !== []) {
                $jadwals = JadwalKuliah::query()
                    ->whereIn('id', $jadwalIds)
                    ->lockForUpdate()
                    ->get();

                foreach ($jadwals as $jadwal) {
                    if ($jadwal->isi_kelas >= $jadwal->kuota_kelas) {
                        throw new Exception(
                            "Gagal menyetujui. Kuota kelas untuk mata kuliah {$jadwal->mataKuliah?->nama_mk} baru saja penuh."
                        );
                    }

                    $jadwal->increment('isi_kelas');
                }
            }

            $before = $locked->status_krs->value;
            $locked->forceFill([
                'status_krs' => KrsStatusEnum::DISETUJUI,
                'disetujui_oleh' => Auth::id(),
                'disetujui_pada' => now(),
                'total_sks_diambil' => $totalSks,
                'catatan_admin' => filled($catatan) ? $catatan : $locked->catatan_admin,
            ])->save();

            $this->writeAudit($locked, 'DISETUJUI', $before, $catatan ?? 'KRS disetujui.');
        });
    }

    public function reject(Krs $krs, string $catatan): void
    {
        if (blank(trim($catatan))) {
            throw new Exception('Alasan penolakan wajib diisi.');
        }

        $this->assertActorCanDecide($krs);

        DB::transaction(function () use ($krs, $catatan): void {
            $locked = Krs::query()->whereKey($krs->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status_krs !== KrsStatusEnum::DIAJUKAN) {
                throw new Exception('Hanya KRS berstatus Diajukan yang dapat ditolak.');
            }

            $before = $locked->status_krs->value;
            $locked->forceFill([
                'status_krs' => KrsStatusEnum::DITOLAK,
                'ditolak_oleh' => Auth::id(),
                'ditolak_pada' => now(),
                'catatan_admin' => $catatan,
            ])->save();

            $this->writeAudit($locked, 'DITOLAK', $before, $catatan);
        });
    }

    private function assertActorCanDecide(Krs $krs): void
    {
        $user = Auth::user();

        if (! $user || ! $this->canApprove($user, $krs)) {
            throw new Exception('Anda tidak berwenang memproses KRS ini.');
        }
    }

    private function writeAudit(Krs $krs, string $aksi, string $beforeStatus, string $catatan): void
    {
        DB::table('krs_status_logs')->insert([
            'krs_id' => $krs->getKey(),
            'aksi' => $aksi,
            'dilakukan_oleh' => Auth::id(),
            'before_data' => json_encode(['status_krs' => $beforeStatus]),
            'after_data' => json_encode(['status_krs' => $krs->status_krs->value]),
            'catatan' => $catatan,
            'created_at' => now(),
        ]);
    }
}
