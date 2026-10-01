<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MahasiswaExport extends BaseKopExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    /** @var array<int, string> */
    private array $infoFilter;

    /**
     * @param  array<int, string>  $infoFilter  Keterangan filter aktif yang tampil di bawah judul.
     */
    public function __construct(protected Builder $query, array $infoFilter = [])
    {
        parent::__construct();

        $this->infoFilter = $infoFilter;
    }

    protected function judul(): string
    {
        return 'DAFTAR DATA MAHASISWA';
    }

    protected function jumlahKolom(): int
    {
        return count(MahasiswaExportMapper::headings());
    }

    protected function keteranganTengah(): array
    {
        $total = (clone $this->query)->reorder()->count();

        return [
            implode('  |  ', array_merge(
                empty($this->infoFilter) ? ['Semua data'] : $this->infoFilter,
                [
                    'Total: ' . number_format($total, 0, ',', '.') . ' mahasiswa',
                    'Dicetak: ' . now()->timezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y, H:i') . ' WITA',
                    'Oleh: ' . (auth()->user()?->name ?? '-'),
                ]
            )),
        ];
    }

    /** NIM, NIK, NISN: wajib teks agar angka nol di depan tidak hilang. */
    protected function kolomTeks(): array
    {
        return ['A', 'D', 'E'];
    }

    protected function autoFilter(): bool
    {
        return true;
    }

    protected function alignVertikalData(): string
    {
        return Alignment::VERTICAL_TOP;
    }

    public function query(): Builder
    {
        return $this->query->with(MahasiswaExportMapper::RELASI);
    }

    public function headings(): array
    {
        return MahasiswaExportMapper::headings();
    }

    /**
     * @param  Mahasiswa  $row
     */
    public function map($row): array
    {
        return MahasiswaExportMapper::baris($row);
    }

    public function title(): string
    {
        return 'Data Mahasiswa';
    }
}
