<?php

namespace App\Exports;

use App\Models\Mahasiswa;

class MahasiswaExportMapper
{
    /**
     * Relasi yang di-eager load untuk keperluan export.
     */
    public const RELASI = ['person', 'prodi', 'angkatan', 'program', 'biodata'];

    /**
     * @return array<int, string>
     */
    public static function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Jenis Kelamin',
            'NIK',
            'NISN',
            'Agama',
            'Program Studi',
            'Program Kelas',
            'Angkatan',
            'Status PDDikti',
            'Kelengkapan Biodata',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function baris(Mahasiswa $mahasiswa): array
    {
        return [
            (string) $mahasiswa->nim,
            (string) ($mahasiswa->person?->nama_lengkap ?? '-'),
            match ($mahasiswa->person?->jenis_kelamin) {
                'L' => 'Laki-laki',
                'P' => 'Perempuan',
                default => '-',
            },
            (string) ($mahasiswa->person?->nik ?: '-'),
            (string) ($mahasiswa->nisn ?: '-'),
            (string) ($mahasiswa->biodata?->agama ?: 'Belum diisi'),
            (string) ($mahasiswa->prodi?->nama_prodi ?? '-'),
            (string) ($mahasiswa->program?->nama_program ?? '-'),
            (string) ($mahasiswa->angkatan?->id_tahun ?? $mahasiswa->angkatan_id),
            filled($mahasiswa->last_synced_at) ? 'Tersinkron' : 'Belum Tersinkron',
            static::statusBiodata($mahasiswa),
        ];
    }

    protected static function statusBiodata(Mahasiswa $mahasiswa): string
    {
        if (! $mahasiswa->biodata) {
            return 'Belum diisi';
        }

        $fields = ['alamat_ktp', 'nama_ayah', 'nama_ibu', 'agama', 'status_pernikahan'];

        $terisi = collect($fields)
            ->filter(fn(string $field) => filled($mahasiswa->biodata->{$field}))
            ->count();

        $total = count($fields);

        return match (true) {
            $terisi === $total => 'Lengkap',
            $terisi > 0 => "Sebagian ({$terisi}/{$total})",
            default => 'Belum diisi',
        };
    }
}
