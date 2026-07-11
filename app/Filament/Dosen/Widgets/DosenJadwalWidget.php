<?php

namespace App\Filament\Dosen\Widgets;

use App\Models\JadwalKuliah;
use App\Models\TrxDosen;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DosenJadwalWidget extends Widget
{
    protected  string $view = 'filament.dosen.widgets.dosen-jadwal-widget';
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $dosen = TrxDosen::where('person_id', Auth::user()->person_id)->first();

        $jadwal = JadwalKuliah::query()
            ->join('jadwal_kuliah_dosen', 'jadwal_kuliah.id', '=', 'jadwal_kuliah_dosen.jadwal_kuliah_id')
            ->where('jadwal_kuliah_dosen.dosen_id', $dosen->id)
            ->where('jadwal_kuliah.hari', now()->format('l')) // Filter hari ini
            ->with(['mataKuliah', 'ruang'])
            ->get();

        return [
            'jadwal' => $jadwal,
        ];
    }
}
