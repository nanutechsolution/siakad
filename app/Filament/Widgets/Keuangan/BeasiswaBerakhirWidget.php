<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Keuangan;

use App\Models\KeuanganMahasiswaBeasiswa;
use App\Models\RefTahunAkademik;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BeasiswaBerakhirWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Early Warning: Beasiswa Berakhir di Semester Ini';

    public function table(Table $table): Table
    {
        $tahunAktifId = RefTahunAkademik::where('is_active', true)->value('id');

        return $table
            ->query(
                KeuanganMahasiswaBeasiswa::query()
                    ->with(['mahasiswa.person', 'beasiswa'])
                    ->where('is_active', true)
                    ->when($tahunAktifId, function (Builder $query) use ($tahunAktifId) {
                        $query->where('tahun_akademik_akhir_id', $tahunAktifId);
                    }, function (Builder $query) {
                        // Fallback jika tidak ada TA aktif
                        $query->whereNull('id'); 
                    })
            )
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('beasiswa.nama_beasiswa')
                    ->label('Program Beasiswa')
                    ->color('warning'),

                TextColumn::make('nomor_sk')
                    ->label('Nomor SK')
                    ->placeholder('-'),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn () => 'Berakhir Semester Ini')
                    ->badge()
                    ->color('danger'),
            ])
            ->paginated(false);
    }
}