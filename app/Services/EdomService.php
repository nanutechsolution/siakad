<?php

namespace App\Services;

use App\Models\KrsDetail;
use App\Models\LpmEdomJawaban;
use App\Models\LpmEdomSaran;
use App\Models\EdomProgress;
use Illuminate\Support\Facades\DB;

class EdomService
{
    public function getPendingEvaluations(string $mahasiswaId, int $tahunAkademikId): array
    {
        // 1. Ambil data KRS Detail yang disetujui beserta jadwal kuliah & dosen pengampu
        $krsDetails = KrsDetail::with([
                'jadwalKuliah.dosenPengampu.person',
                'jadwalKuliah.mataKuliah'
            ])
            ->whereHas('krs', function ($query) use ($mahasiswaId, $tahunAkademikId) {
                $query->where('mahasiswa_id', $mahasiswaId)
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->where('status_krs', 'DISETUJUI');
            })
            ->get();

        // 2. Ambil semua progres evaluasi mahasiswa yang sudah selesai (mencegah N+1 query)
        $completedEvaluations = EdomProgress::where('mahasiswa_id', $mahasiswaId)
            ->where('is_completed', true)
            ->get()
            ->groupBy('jadwal_kuliah_id')
            ->map(function ($items) {
                return $items->pluck('dosen_id')->toArray();
            })
            ->toArray();

        $pending = [];

        foreach ($krsDetails as $detail) {
            $jadwal = $detail->jadwalKuliah;
            if (!$jadwal) continue;

            $jadwalId = $jadwal->id;
            // Ambil daftar dosen yang sudah dievaluasi untuk jadwal ini
            $completedDosenIds = $completedEvaluations[$jadwalId] ?? [];

            foreach ($jadwal->dosenPengampu as $dosen) {
                // Jika dosen belum dievaluasi oleh mahasiswa untuk jadwal kuliah ini
                if (!in_array($dosen->id, $completedDosenIds)) {
                    $pending[] = [
                        'krs_detail_id'    => $detail->id,
                        'jadwal_kuliah_id' => $jadwalId,
                        'mata_kuliah'      => $detail->nama_mk_snapshot ?? $jadwal->mataKuliah->nama_mk ?? 'MK',
                        'dosen_id'         => $dosen->id,
                        'nama_dosen'       => $dosen->person->nama_dengan_gelar ?? 'Dosen Belum Dinamakan',
                    ];
                }
            }
        }

        return $pending;
    }

    public function submitEvaluation(int $krsDetailId, string $dosenId, array $answers, ?string $saran): void
    {
        // Temukan data KRS detail terlebih dahulu untuk mendapatkan jadwal_kuliah_id dan mahasiswa_id
        $krsDetail = KrsDetail::with('krs')->findOrFail($krsDetailId);
        $jadwalKuliahId = $krsDetail->jadwal_kuliah_id;
        $mahasiswaId = $krsDetail->krs->mahasiswa_id;

        DB::transaction(function () use ($krsDetail, $jadwalKuliahId, $mahasiswaId, $dosenId, $answers, $saran) {
            // 1. Simpan detail jawaban menggunakan 'jadwal_kuliah_id'
            foreach ($answers as $pertanyaanId => $jawabanNilai) {
                LpmEdomJawaban::updateOrCreate(
                    [
                        'jadwal_kuliah_id' => $jadwalKuliahId,
                        'pertanyaan_id'    => $pertanyaanId,
                        'dosen_id'         => $dosenId,
                    ],
                    ['jawaban_nilai' => (string) $jawabanNilai]
                );
            }

            // 2. Simpan Saran jika ada menggunakan 'jadwal_kuliah_id'
            if (!empty($saran)) {
                LpmEdomSaran::updateOrCreate(
                    [
                        'jadwal_kuliah_id' => $jadwalKuliahId,
                        'dosen_id'         => $dosenId,
                    ],
                    ['catatan' => trim($saran)]
                );
            }

            // 3. Catat progres penyelesaian mahasiswa ke tabel EdomProgress
            EdomProgress::updateOrCreate(
                [
                    'mahasiswa_id'     => $mahasiswaId,
                    'jadwal_kuliah_id' => $jadwalKuliahId,
                    'dosen_id'         => $dosenId,
                ],
                ['is_completed' => true]
            );

            // 4. Periksa dan ubah status pengisian di krs_details jika semua dosen kelas sudah dinilai
            $this->checkAndCompleteKrsDetailEdomStatus($krsDetail, $mahasiswaId);
        });
    }

    private function checkAndCompleteKrsDetailEdomStatus(KrsDetail $krsDetail, string $mahasiswaId): void
    {
        $krsDetail->loadMissing('jadwalKuliah.dosenPengampu');

        if (!$krsDetail->jadwalKuliah) return;

        $totalDosen = $krsDetail->jadwalKuliah->dosenPengampu->count();

        // Hitung berapa dosen yang sudah diselesaikan evaluasinya di jadwal ini oleh mahasiswa terkait
        $evaluatedDosenCount = EdomProgress::where([
                'mahasiswa_id'     => $mahasiswaId,
                'jadwal_kuliah_id' => $krsDetail->jadwal_kuliah_id,
                'is_completed'     => true,
            ])
            ->count();

        // Jika semua dosen pengampu sudah dievaluasi, tandai KRS Detail sebagai selesai
        if ($evaluatedDosenCount >= $totalDosen) {
            $krsDetail->update(['is_edom_filled' => true]);
        }
    }
}