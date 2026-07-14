<?php

namespace App\Filament\Resources\KeuanganAdjustments\Tables;

use App\Enums\Keuangan\JenisAdjustment;
use App\Enums\Keuangan\StatusAdjustment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KeuanganAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_adjustment')
                    ->label('Nomor ADJ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('tagihan.kode_transaksi')
                    ->label('Kode Tagihan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tagihan.mahasiswa.person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('jenis_adjustment')
                    ->label('Jenis')
                    ->badge()
                    ->sortable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((float) $state, 2, ',', '.'))
                    ->color(fn($state) => (float) $state < 0 ? 'danger' : 'success')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tgl Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options(StatusAdjustment::class)
                    ->multiple(),

                SelectFilter::make('jenis_adjustment')
                    ->options(JenisAdjustment::class),

                Filter::make('nominal_direction')
                    ->label('Arah Nominal')
                    ->schema([
                        \Filament\Forms\Components\Select::make('arah')
                            ->options([
                                'pengurangan' => 'Hanya Pengurangan (-)',
                                'penambahan' => 'Hanya Penambahan (+)',
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['arah'] === 'pengurangan', fn(Builder $q) => $q->where('nominal', '<', 0))
                            ->when($data['arah'] === 'penambahan', fn(Builder $q) => $q->where('nominal', '>', 0));
                    }),

                Filter::make('tanggal')
                    ->schema([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => $record->status === StatusAdjustment::DRAFT),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
