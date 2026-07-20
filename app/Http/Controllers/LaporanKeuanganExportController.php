<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class LaporanKeuanganExportController extends Controller
{
    public function pdf(string $page)
    {
        abort_unless(class_exists($page), 404);

        $report = app($page);

        $rows = $report->tableRows([]);

        return Pdf::loadView('exports.laporan-keuangan.generic', [
            'title' => $report->reportTitle(),
            'headings' => $report->tableHeadings(),
            'rows' => $rows,
            'generatedAt' => now(),
        ])->download(
            $report->exportFileBaseName() . '.pdf'
        );
    }
}