<?php

namespace App\Services;

use App\Models\LpmKuisionerKelompok;
use App\Models\LpmSurveyJawaban;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    /**
     * Mengecek survey mana saja yang belum diisi oleh mahasiswa.
     */
    public function getAvailableSurveys(string $mahasiswaId, int $tahunAkademikId)
    {
        // Ambil semua kelompok kuesioner yang bukan EDOM
        $surveys = LpmKuisionerKelompok::where('kategori', '!=', 'EDOM')
            ->where('is_active', true)
            ->get();

        $availableSurveys = [];

        foreach ($surveys as $survey) {
            // Cek apakah mahasiswa sudah mensubmit minimal 1 jawaban untuk kelompok ini di TA berjalan
            $hasFilled = LpmSurveyJawaban::where('mahasiswa_id', $mahasiswaId)
                ->where('tahun_akademik_id', $tahunAkademikId)
                ->whereIn('pertanyaan_id', $survey->pertanyaans->pluck('id'))
                ->exists();

            if (!$hasFilled) {
                $availableSurveys[] = $survey;
            }
        }

        return $availableSurveys;
    }

    /**
     * Submit jawaban survey umum
     */
    public function submitSurvey(string $mahasiswaId, int $tahunAkademikId, array $answers): void
    {
        DB::transaction(function () use ($mahasiswaId, $tahunAkademikId, $answers) {
            foreach ($answers as $pertanyaanId => $jawaban) {
                LpmSurveyJawaban::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswaId,
                        'pertanyaan_id' => $pertanyaanId,
                        'tahun_akademik_id' => $tahunAkademikId,
                    ],
                    [
                        'jawaban_nilai' => (string) $jawaban,
                    ]
                );
            }
        });
    }
}
