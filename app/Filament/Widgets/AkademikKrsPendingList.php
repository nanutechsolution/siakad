<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class AkademikKrsPendingList extends Widget
{
protected string $view = 'filament.widgets.akademik-krs-pending-list';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $rows = DB::table('krs')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'krs.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->where('krs.status_krs', 'DIAJUKAN')
            ->orderByDesc('krs.diajukan_at')
            ->limit(5)
            ->select([
                'ref_person.nama_lengkap',
                'mahasiswas.nim',
                'ref_prodi.nama_prodi',
                'krs.diajukan_at',
            ])
            ->get();

        return [
            'rows' => $rows,
        ];
    }
}