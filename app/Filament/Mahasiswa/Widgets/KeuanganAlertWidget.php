<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Models\Mahasiswa;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KeuanganAlertWidget extends Widget
{
    protected string $view = 'filament.mahasiswa.widgets.keuangan-alert-widget';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    // public float $totalTunggakan = 0;
    // public bool $hasTunggakan = false;
    public float $totalTunggakan = 1500000; // Angka bohongan
    public bool $hasTunggakan = true; // Paksa true

    public function mount(): void
    {
        $user = Auth::user();

        $mahasiswa = Mahasiswa::where('person_id', $user->person_id)->first();

        if ($mahasiswa) {
            // Cek total tunggakan dari semua tagihan yang belum lunas (mengabaikan deleted_at)
            $tunggakan = DB::table('tagihan_mahasiswas')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('status_bayar', '!=', 'LUNAS')
                ->whereNull('deleted_at')
                ->sum(DB::raw('total_tagihan - total_bayar'));

            if ($tunggakan > 0) {
                $this->hasTunggakan = true;
                $this->totalTunggakan = (float) $tunggakan;
            }
        }
    }
}
