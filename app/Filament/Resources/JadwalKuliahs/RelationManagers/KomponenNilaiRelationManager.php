<?php

namespace App\Filament\Resources\JadwalKuliahs\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KomponenNilaiRelationManager extends RelationManager
{
    protected static string $relationship = 'komponenNilai';
    protected static ?string $title = 'Bobot Komponen Penilaian';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('nama_komponen')
                            ->label('Nama Komponen Nilai')
                            ->placeholder('Contoh: UTS, UAS, Tugas 1, Presensi')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('bobot_persentase')
                            ->label('Bobot Penilaian (%)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->hint('Masukkan angka 1 - 100'),

                        Toggle::make('is_active')
                            ->label('Aktifkan Komponen')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_komponen')
            ->columns([
                TextColumn::make('masterKomponen.nama_komponen')
                    ->label('Komponen Nilai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('bobot_persentase')
                    ->label('Bobot')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->suffix('%'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Komponen')
                    ->icon('heroicon-m-plus')
                    ->visible(fn() => auth()->user()->can('ManageKomponenNilai'))
                    ->before(function (RelationManager $livewire, $state) {
                        $currentTotal = $livewire->getRelationship()->sum('bobot_persen');
                        $newTotal = $currentTotal + $state['bobot_persen'];

                        if ($newTotal > 100) {
                            \Filament\Notifications\Notification::make()
                                ->title('Total Bobot Melebihi 100%')
                                ->body("Total bobot saat ini {$currentTotal}%. Anda memasukkan {$state['bobot_persen']}%, total menjadi {$newTotal}%.")
                                ->danger()
                                ->send();

                            $livewire->halt();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->color('warning')
                    ->before(function (RelationManager $livewire, $record, $state) {
                        // Hitung total dengan mengecualikan data yang sedang di-edit saat ini
                        $currentTotal = $livewire->getRelationship()
                            ->where('id', '!=', $record->id)
                            ->sum('bobot_persen');

                        $newTotal = $currentTotal + $state['bobot_persen'];

                        if ($newTotal > 100) {
                            \Filament\Notifications\Notification::make()
                                ->title('Total Bobot Melebihi 100%')
                                ->body("Total bobot komponen lain {$currentTotal}%. Anda mengubah menjadi {$state['bobot_persen']}%, total menjadi {$newTotal}%.")
                                ->danger()
                                ->send();

                            $livewire->halt();
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
