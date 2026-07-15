<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Enums\StatusKehadiran;
use App\Enums\StatusKehadiranEnum;
use App\Enums\StatusSesiPerkuliahan;
use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class KelolaPresensiSesi extends Page implements HasTable
{
    use InteractsWithRecord,  InteractsWithTable;

    protected static string $resource = JadwalMengajarResource::class;

    protected string $view = 'filament.dosen.resources.jadwal-mengajars.pages.kelola-presensi-sesi';

    public PerkuliahanSesi $sesi;

    public function mount(int|string $record, string $sesiId): void
    {
        $this->record = $this->resolveRecord($record);
        $this->sesi = PerkuliahanSesi::with('jadwalKuliah.mataKuliah')
            ->where('jadwal_kuliah_id', $this->record->id)
            ->findOrFail($sesiId);
    }

    public function getHeading(): string
    {
        return 'Presensi Sesi Ke-' . $this->sesi->pertemuan_ke . ' — ' . $this->sesi->jadwalKuliah->mataKuliah->nama_mk;
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(
                PerkuliahanAbsensi::query()
                    ->join('krs_detail', 'krs_detail.id', '=', 'perkuliahan_absensi.krs_detail_id')
                    ->join('krs', 'krs.id', '=', 'krs_detail.krs_id')
                    ->join('mahasiswas', 'mahasiswas.id', '=', 'krs.mahasiswa_id')
                    ->where('perkuliahan_absensi.perkuliahan_sesi_id', $this->sesi->id)
                    ->select('perkuliahan_absensi.*')
                    ->with(['krsDetail.krs.mahasiswa.person'])
                    ->orderBy('mahasiswas.nim')
            )
            ->columns([
                TextColumn::make('krsDetail.krs.mahasiswa.nim')
                    ->label('NIM')
                    ->searchable(),
                TextColumn::make('krsDetail.krs.mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                SelectColumn::make('status_kehadiran')
                    ->label('Status')
                    ->options(collect(StatusKehadiranEnum::cases())->mapWithKeys(
                        fn($c) => [$c->value => $c->getLabel()]
                    ))
                    ->afterStateUpdated(function (PerkuliahanAbsensi $record, $state): void {
                        $record->update([
                            'is_manual_update' => true,
                            'modified_by_user_id' => auth()->id(),
                            'waktu_check_in' => $state === 'H' ? now() : null,
                        ]);
                    }),
                TextColumn::make('waktu_check_in')
                    ->label('Waktu Absen')
                    ->dateTime('d M Y H:i:s')
                    ->placeholder('Belum Absen'),
                IconColumn::make('is_flagged_duplikat')
                    ->label('Terindikasi Ganda')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('gray'),
            ])
            ->paginated([50, 100, 'all']);
    }
}
