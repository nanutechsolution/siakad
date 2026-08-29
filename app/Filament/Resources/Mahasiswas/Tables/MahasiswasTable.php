<?php

namespace App\Filament\Resources\Mahasiswas\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Mahasiswa;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) => $query->with(['person', 'prodi', 'angkatan', 'program', 'biodata'])
            )
            ->defaultSort('nim')
            ->striped()
            ->searchOnBlur() // hindari query per-keystroke, hanya cari saat selesai mengetik / blur
            ->columns([
                ImageColumn::make('person.photo_path')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->person?->nama_lengkap ?? '?') . '&color=7C3AED&background=EDE9FE')
                    ->size(40),

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(['nama_lengkap', 'nik']) // ← tanpa prefix "person."
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn(Mahasiswa $record) => $record->person?->nik ? 'NIK ' . $record->person->nik : null)
                    ->wrap(),
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('NIM disalin')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn(Mahasiswa $record) => $record->program?->nama_program),

                TextColumn::make('angkatan.id_tahun')
                    ->label('Angkatan')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                IconColumn::make('biodata_lengkap')
                    ->label('Biodata')
                    ->tooltip(fn(Mahasiswa $record) => self::biodataStatus($record)['tooltip'])
                    ->icon(fn(Mahasiswa $record) => self::biodataStatus($record)['icon'])
                    ->color(fn(Mahasiswa $record) => self::biodataStatus($record)['color'])
                    ->alignCenter(),

                IconColumn::make('sync_status')
                    ->label('PDDikti')
                    ->boolean()
                    ->state(fn(Mahasiswa $record) => filled($record->last_synced_at))
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-exclamation-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn(Mahasiswa $record) => $record->last_synced_at
                        ? 'Tersinkron ' . $record->last_synced_at->diffForHumans()
                        : 'Belum pernah sinkron')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->relationship('angkatan', 'id_tahun')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('program_id')
                    ->label('Program Kelas')
                    ->relationship('program', 'nama_program')
                    ->preload(),

                TernaryFilter::make('sync_status')
                    ->label('Status PDDikti')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Sinkron')
                    ->falseLabel('Belum Sinkron')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('last_synced_at'),
                        false: fn(Builder $query) => $query->whereNull('last_synced_at'),
                    ),

                Filter::make('biodata_belum_lengkap')
                    ->label('Biodata Belum Lengkap')
                    ->toggle()
                    ->query(fn(Builder $query) => $query->whereDoesntHave('biodata')
                        ->orWhereHas('biodata', fn($q) => $q->whereNull('nama_ayah')->orWhereNull('agama'))),

                TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada mahasiswa')
            ->emptyStateDescription('Data mahasiswa yang terdaftar akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    /**
     * @return array{icon: string, color: string, tooltip: string}
     */
    protected static function biodataStatus(Mahasiswa $record): array
    {
        if (! $record->biodata) {
            return [
                'icon' => 'heroicon-o-x-circle',
                'color' => 'danger',
                'tooltip' => 'Belum diisi sama sekali',
            ];
        }

        $fields = ['alamat_ktp', 'nama_ayah', 'nama_ibu', 'agama', 'status_pernikahan'];
        $filled = collect($fields)->filter(fn($f) => filled($record->biodata->{$f}))->count();

        return match (true) {
            $filled === count($fields) => [
                'icon' => 'heroicon-o-check-circle',
                'color' => 'success',
                'tooltip' => 'Biodata lengkap',
            ],
            $filled > 0 => [
                'icon' => 'heroicon-o-minus-circle',
                'color' => 'warning',
                'tooltip' => "Biodata sebagian ({$filled}/" . count($fields) . ' field terisi)',
            ],
            default => [
                'icon' => 'heroicon-o-x-circle',
                'color' => 'danger',
                'tooltip' => 'Belum diisi sama sekali',
            ],
        };
    }
}
