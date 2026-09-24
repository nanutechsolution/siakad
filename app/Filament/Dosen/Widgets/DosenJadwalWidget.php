<?php

namespace App\Filament\Dosen\Widgets;

use App\Models\JadwalKuliah;
use App\Models\TrxDosen;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DosenJadwalWidget extends Widget
{
    protected string $view = 'filament.dosen.widgets.dosen-jadwal-widget';
    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $dosenId = Auth::user()?->person?->dosen?->id;

        if (blank($dosenId)) {
            return ['jadwal' => collect()];
        }

        // Kolom `hari` disimpan dalam bahasa Indonesia (Senin, Selasa, ...)
        // sedangkan now()->format('l') menghasilkan bahasa Inggris (Monday).
        // Memakai translate supaya filter "hari ini" benar-benar bekerja.
        $hariIni = now()->translatedFormat('l');

        $jadwal = JadwalKuliah::query()
            ->join('jadwal_kuliah_dosen', 'jadwal_kuliah.id', '=', 'jadwal_kuliah_dosen.jadwal_kuliah_id')
            ->where('jadwal_kuliah_dosen.dosen_id', $dosenId)
            ->whereNull('jadwal_kuliah.deleted_at')
            ->where('jadwal_kuliah.hari', $hariIni)
            ->orderBy('jadwal_kuliah.jam_mulai')
            ->with(['mataKuliah', 'ruang', 'kelas'])
            ->get();

        return [
            'jadwal' => $jadwal,
            'hari' => $hariIni,
        ];
    }
}
