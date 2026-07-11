<?php

declare(strict_types=1);

namespace App\Filament\Widgets\HR;

use App\Enums\HR\JenisJabatan;
use App\Models\TrxPersonJabatan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class JabatanBerakhirWidget extends BaseWidget
{
    protected  static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Early Warning: Jabatan Struktural Berakhir (< 30 Hari)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TrxPersonJabatan::query()
                    ->with(['person', 'jabatan', 'fakultas', 'prodi'])
                    ->whereHas('jabatan', function (Builder $query) {
                        $query->where('jenis', JenisJabatan::STRUKTURAL);
                    })
                    ->whereNotNull('tanggal_selesai')
                    ->whereBetween('tanggal_selesai', [Carbon::today(), Carbon::today()->addDays(30)])
            )
            ->columns([
                Tables\Columns\TextColumn::make('person.nama_lengkap')
                    ->label('Nama Pegawai')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('fakultas.nama_fakultas')
                    ->label('Fakultas')
                    ->default('-'),
                Tables\Columns\TextColumn::make('prodi.nama_prodi')
                    ->label('Prodi')
                    ->default('-'),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tgl Berakhir')
                    ->date('d M Y')
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sisa_hari')
                    ->label('Sisa Waktu')
                    ->state(function (TrxPersonJabatan $record): string {
                        $days = (int) Carbon::today()->diffInDays($record->tanggal_selesai, false);
                        return $days <= 0 ? 'Hari ini / Lewat' : "{$days} Hari";
                    })
                    ->badge()
                    ->color(fn(string $state): string => str_contains($state, 'Hari ini') ? 'danger' : 'warning'),
            ])
            ->paginated(false);
    }
}
