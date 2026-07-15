<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\EdomProgress;
use App\Models\KrsDetail;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DaftarEdom extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::EVALUASI->value;
    protected static ?string $title = 'Daftar Evaluasi Dosen';
    protected  string $view = 'filament.mahasiswa.pages.daftar-edom';

    public array $kelasEvaluasi = [];

    public function mount()
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) {
            return;
        }
        // 1. Dapatkan Jadwal Kuliah dari KRS Detail mahasiswa pada KRS berstatus aktif/disetujui
        $krsDetails = KrsDetail::query()
            ->whereHas('krs', function ($query) use ($mahasiswa) {
                $query->where('mahasiswa_id', $mahasiswa->id)
                    ->where('status_krs', 'DISETUJUI');
            })
            ->with(['jadwalKuliah.mataKuliah', 'jadwalKuliah.dosenPengampu'])
            ->get();
        // 2. Periksa progres pengisian EDOM mahasiswa
        $completedProgress = EdomProgress::where('mahasiswa_id', $mahasiswa->id)
            ->get()
            ->groupBy(fn($item) => $item->jadwal_kuliah_id . '-' . $item->dosen_id);

        $list = [];
        foreach ($krsDetails as $detail) {
            $jadwal = $detail->jadwalKuliah;
            if (!$jadwal) continue;
            foreach ($jadwal->dosenPengampu as $dosen) {
                $key = $jadwal->id . '-' . $dosen->id;
                $isCompleted = isset($completedProgress[$key]);
                $list[] = [
                    'jadwal_kuliah_id' => $jadwal->id,
                    'mata_kuliah_nama' => $jadwal->mataKuliah->nama_mk ?? 'Tanpa Nama',
                    'mata_kuliah_kode' => $detail->kode_mk_snapshot,
                    'dosen_id'         => $dosen->id,
                    'dosen_nama'       => $dosen->person?->nama_dengan_gelar ?? '(Tanpa Nama)',
                    'is_completed'     => $isCompleted,
                    'is_koordinator'  => $dosen->pivot->is_koordinator ?? false,
                    'is_penilai'      => $dosen->pivot->is_penilai ?? false,
                ];
            }
        }

        $this->kelasEvaluasi = $list;
    }
}
