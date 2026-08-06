<?php

namespace App\Imports;

use App\Models\Beasiswa;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBeasiswa;
use App\Models\RefTahunAkademik;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MahasiswaBeasiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        $nim = trim((string) $row['nim']);
        $namaBeasiswa = trim((string) $row['program_beasiswa']);
        $tahunMulaiNama = trim((string) $row['tahun_akademik_mulai']);
        $tahunSelesaiNama = isset($row['tahun_akademik_selesai']) ? trim((string) $row['tahun_akademik_selesai']) : null;

        $mahasiswa = Mahasiswa::where('nim', $nim)->first();
        $beasiswa = Beasiswa::where('nama_beasiswa', $namaBeasiswa)->first();
        $tahunMulai = RefTahunAkademik::where('nama_tahun', $tahunMulaiNama)->first();
        $tahunSelesai = $tahunSelesaiNama ? RefTahunAkademik::where('nama_tahun', $tahunSelesaiNama)->first() : null;

        if (! $mahasiswa || ! $beasiswa || ! $tahunMulai) {
            return null;
        }

        $isActive = strtolower(trim((string) ($row['status_aktif'] ?? 'ya'))) === 'ya';

        return new MahasiswaBeasiswa([
            'mahasiswa_id' => $mahasiswa->id,
            'beasiswa_id' => $beasiswa->id,
            'tahun_akademik_mulai_id' => $tahunMulai->id,
            'tahun_akademik_akhir_id' => $tahunSelesai?->id,
            'is_active' => $isActive,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nim' => ['required', 'exists:mahasiswas,nim'],
            '*.program_beasiswa' => ['required', 'exists:beasiswas,nama_beasiswa'],
            '*.tahun_akademik_mulai' => ['required', 'exists:ref_tahun_akademiks,nama_tahun'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.nim.exists' => 'NIM :input tidak ditemukan di database.',
            '*.program_beasiswa.exists' => 'Program Beasiswa ":input" tidak ditemukan.',
            '*.tahun_akademik_mulai.exists' => 'Tahun Akademik ":input" tidak ditemukan.',
        ];
    }
}
