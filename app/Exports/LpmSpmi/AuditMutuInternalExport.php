<?php

declare(strict_types=1);

namespace App\Exports\LpmSpmi;

use App\Services\LpmSpmi\AuditMutuInternalService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditMutuInternalExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection()
    {
        return app(AuditMutuInternalService::class)->exportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            'Periode', 'Prodi', 'Standar', 'Klasifikasi', 'Jenis Temuan', 'Auditor',
            'Deskripsi Temuan', 'Status Workflow', 'Deadline Perbaikan', 'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row['periode'],
            $row['prodi'],
            $row['standar'],
            $row['klasifikasi'],
            $row['jenis_temuan'],
            $row['auditor'],
            $row['deskripsi_temuan'],
            $row['status_workflow'],
            $row['deadline_perbaikan'],
            $row['status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
