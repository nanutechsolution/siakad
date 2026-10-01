<?php

declare(strict_types=1);

namespace App\Exports\Absensi;

use App\DataTransferObjects\Absensi\AbsensiDocumentData;
use App\Exports\BaseKopExport;
use Maatwebsite\Excel\Concerns\FromArray;

class AbsensiDocumentExport extends BaseKopExport implements FromArray
{
    public function __construct(private readonly AbsensiDocumentData $document)
    {
        parent::__construct();
    }

    protected function judul(): string
    {
        return 'DAFTAR HADIR PERKULIAHAN';
    }

    protected function jumlahKolom(): int
    {
        return count($this->headers());
    }

    protected function keteranganLabel(): array
    {
        $akademik = $this->document->akademik;

        return [
            'Tahun Akademik' => trim(($akademik['tahun_akademik'] ?? '') . ' - ' . ($akademik['semester'] ?? ''), ' -'),
            'Program Studi' => (string) ($akademik['prodi'] ?? ''),
            'Mata Kuliah' => trim(($akademik['kode_mk'] ?? '') . ' - ' . ($akademik['mata_kuliah'] ?? ''), ' -'),
            'Kelas / SKS' => trim(($akademik['kelas'] ?? '') . ' / ' . ($akademik['sks'] ?? ''), ' /'),
            'Dosen' => (string) ($akademik['dosen'] ?? ''),
            'Pertemuan / Tanggal' => trim(
                (! empty($akademik['pertemuan']) ? 'P' . $akademik['pertemuan'] : '') . ' / ' . ($akademik['tanggal'] ?? ''),
                ' /'
            ),
        ];
    }

    protected function kolomTengah(): array
    {
        return ['A'];
    }

    protected function tinggiBarisData(): ?float
    {
        // Ruang untuk tanda tangan / paraf pada mode manual dan template
        return in_array($this->document->mode, ['manual', 'template'], true) ? 22.0 : null;
    }

    protected function warnaHeading(): string
    {
        return 'E8EEF5';
    }

    protected function warnaTeksHeading(): string
    {
        return '000000';
    }

    protected function warnaBorder(): string
    {
        return '9CA3AF';
    }

    /**
     * @return array<int, string>
     */
    private function headers(): array
    {
        return match ($this->document->mode) {
            'online' => ['No', 'NIM', 'Nama Mahasiswa', 'Status', 'Jam', 'Keterangan'],
            'template' => array_merge(
                ['No', 'NIM', 'Nama Mahasiswa'],
                array_map(fn (int $number) => 'P' . $number, $this->document->pertemuan)
            ),
            default => ['No', 'NIM', 'Nama Mahasiswa', 'Tanda Tangan / Keterangan'],
        };
    }

    public function array(): array
    {
        $rows = array_map(function (array $row) {
            return match ($this->document->mode) {
                'online' => [$row['no'], $row['nim'], $row['nama'], $row['status'], $row['waktu'], $row['keterangan']],
                'template' => array_merge(
                    [$row['no'], $row['nim'], $row['nama']],
                    array_fill(0, count($this->document->pertemuan), '')
                ),
                default => [$row['no'], $row['nim'], $row['nama'], ''],
            };
        }, $this->document->rows);

        return array_merge([$this->headers()], $rows);
    }
}