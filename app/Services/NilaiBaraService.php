<?php

namespace App\Services;

use App\Models\AkademikGradeRevisionLog;
use App\Models\JadwalKuliah;
use App\Models\KrsDetail;
use App\Models\RefSkalaNilai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NilaiBaraService
{
    /**
     * Lock seluruh nilai pada satu kelas kuliah (dipanggil BARA).
     * Syarat: seluruh krs_detail pada kelas tsb sudah published.
     */
    public function lockKelas(JadwalKuliah $jadwal, ?string $catatan = null): void
    {
        $belumPublish = $jadwal->krsDetail()->where('is_published', false)->exists();

        if ($belumPublish) {
            throw ValidationException::withMessages([
                'lock' => 'Tidak dapat mengunci: masih ada nilai yang belum dipublish oleh dosen.',
            ]);
        }

        DB::transaction(function () use ($jadwal, $catatan) {
            $ids = $jadwal->krsDetail()->where('is_locked', false)->pluck('id');

            KrsDetail::whereIn('id', $ids)->update(['is_locked' => true]);

            activity('nilai')
                ->performedOn($jadwal)
                ->causedBy(Auth::user())
                ->withProperties([
                    'aksi' => 'LOCK_KELAS',
                    'jadwal_kuliah_id' => $jadwal->id,
                    'jumlah_krs_detail' => $ids->count(),
                    'catatan' => $catatan,
                ])
                ->log("BARA mengunci nilai kelas kuliah {$jadwal->id}");
        });
    }

    /**
     * Unlock — WAJIB alasan. Membuka kembali seluruh nilai pada kelas
     * agar bisa dikoreksi/di-input ulang oleh dosen atau BARA.
     */
    public function unlockKelas(JadwalKuliah $jadwal, string $alasan): void
    {
        if (blank($alasan)) {
            throw ValidationException::withMessages([
                'alasan' => 'Alasan unlock wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($jadwal, $alasan) {
            $ids = $jadwal->krsDetail()->where('is_locked', true)->pluck('id');

            KrsDetail::whereIn('id', $ids)->update(['is_locked' => false]);

            activity('nilai')
                ->performedOn($jadwal)
                ->causedBy(Auth::user())
                ->withProperties([
                    'aksi' => 'UNLOCK_KELAS',
                    'jadwal_kuliah_id' => $jadwal->id,
                    'jumlah_krs_detail' => $ids->count(),
                    'alasan' => $alasan,
                ])
                ->log("BARA membuka kunci nilai kelas kuliah {$jadwal->id}: {$alasan}");
        });
    }

    /**
     * Koreksi nilai satu mahasiswa oleh BARA.
     * - Wajib alasan
     * - Menyimpan histori lama ke akademik_grade_revision_logs (tidak menimpa/menghapus)
     * - Menyimpan user & waktu perubahan
     */
    public function koreksiNilai(
        KrsDetail $detail,
        float $nilaiAngkaBaru,
        string $nilaiHurufBaru,
        string $alasan,
        ?string $nomorSk = null,
    ): AkademikGradeRevisionLog {
        if (blank($alasan)) {
            throw ValidationException::withMessages([
                'alasan_perbaikan' => 'Alasan koreksi wajib diisi.',
            ]);
        }

        return DB::transaction(function () use ($detail, $nilaiAngkaBaru, $nilaiHurufBaru, $alasan, $nomorSk) {
            $skala = RefSkalaNilai::where('huruf', $nilaiHurufBaru)->first();

            $log = AkademikGradeRevisionLog::create([
                'krs_detail_id' => $detail->id,
                'old_nilai_angka' => $detail->nilai_angka,
                'old_nilai_huruf' => $detail->nilai_huruf,
                'new_nilai_angka' => $nilaiAngkaBaru,
                'new_nilai_huruf' => $nilaiHurufBaru,
                'alasan_perbaikan' => $alasan,
                'nomor_sk_perbaikan' => $nomorSk,
                'executed_by' => Auth::id(),
            ]);

            $detail->update([
                'nilai_angka' => $nilaiAngkaBaru,
                'nilai_huruf' => $nilaiHurufBaru,
                'nilai_indeks' => $skala?->bobot_indeks ?? $detail->nilai_indeks,
            ]);

            activity('nilai')
                ->performedOn($detail)
                ->causedBy(Auth::user())
                ->withProperties([
                    'aksi' => 'KOREKSI_NILAI_BARA',
                    'revision_log_id' => $log->id,
                    'alasan' => $alasan,
                ])
                ->log("BARA mengoreksi nilai krs_detail {$detail->id}: {$alasan}");

            return $log;
        });
    }

    /** Buka periode perbaikan nilai untuk satu kelas (unlock massal + catatan) */
    public function bukaPeriodePerbaikan(JadwalKuliah $jadwal, string $alasan): void
    {
        $this->unlockKelas($jadwal, "Buka periode perbaikan nilai: {$alasan}");
    }
}