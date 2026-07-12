<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Enums\StatusKehadiranEnum;
use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class KelolaPresensiSesi extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithRecord;
    protected static string $resource = JadwalMengajarResource::class;
    protected static ?string $title = 'Kelola Presensi Mahasiswa';
    protected string $view = 'filament.dosen.resources.jadwal-mengajars.pages.kelola-presensi-sesi';

    public PerkuliahanSesi $sesi;

    public function mount(int|string $record, string $sesiId): void
    {
        // 1. Resolve parent record (JadwalKuliah) dan simpan ke dalam $this->record bawaan trait
        $this->record = $this->resolveRecord($record);

        // 2. Resolve child record (PerkuliahanSesi)
        $this->sesi = PerkuliahanSesi::with('jadwalKuliah.mataKuliah')->findOrFail($sesiId);

        static::$title = "Presensi Sesi Ke-" . $this->sesi->pertemuan_ke . " - " . $this->sesi->jadwalKuliah->mataKuliah->nama_mk;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PerkuliahanAbsensi::query()
                    ->with(['krsDetail.krs.mahasiswa.person'])
                    ->where('perkuliahan_sesi_id', $this->sesi->id)
            )
            ->columns([
                TextColumn::make('krsDetail.krs.mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('krsDetail.krs.mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),
                SelectColumn::make('status_kehadiran')
                    ->label('Status')
                    ->options(StatusKehadiranEnum::class)
                    ->afterStateUpdated(function (PerkuliahanAbsensi $record, $state) {
                        // Audit trail: Catat bahwa ini diubah manual oleh Dosen
                        $record->update([
                            'is_manual_update' => true,
                            'modified_by_user_id' => auth()->id(),
                            'waktu_check_in' => $state === 'H' ? now() : null, // Set jam absen jika diset Hadir manual
                        ]);
                    })
                    ->searchable(),
                TextColumn::make('waktu_check_in')
                    ->label('Waktu Absen')
                    ->dateTime('d M Y H:i:s')
                    ->placeholder('Belum Absen'),
            ])
            ->defaultSort('krsDetail.krs.mahasiswa.nim', 'asc')
            ->paginated([50, 100, 'all']); // Kapasitas optimal 1 kelas
    }
}
