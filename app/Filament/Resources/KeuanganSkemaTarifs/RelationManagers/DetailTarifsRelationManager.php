<?php

namespace App\Filament\Resources\KeuanganSkemaTarifs\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DetailTarifsRelationManager extends RelationManager
{
    protected static string $relationship = '   ';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('komponen_biaya_id')
                    ->label('Komponen Biaya')
                    ->relationship('komponenBiaya', 'nama_komponen')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('nominal')
                    ->label('Nominal')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('berlaku_semester')
                    ->label('Berlaku Semester')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(14)
                    ->nullable()
                    ->helperText('Kosongkan jika berlaku untuk semua semester.'),

                Radio::make('penerapan')
                    ->label('Penerapan')
                    ->options([
                        'FLAT' => 'Tiap Semester',
                        'ONCE' => 'Sekali Saja',
                    ])
                    ->inline()
                    ->inlineLabel(false)
                    ->default('FLAT')
                    ->live()
                    ->required(),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query
                    ->join(
                        'keuangan_komponen_biaya',
                        'keuangan_detail_tarif.komponen_biaya_id',
                        '=',
                        'keuangan_komponen_biaya.id'
                    )
                    ->select('keuangan_detail_tarif.*')
                    ->orderBy('keuangan_komponen_biaya.urutan_prioritas');
            })
            ->columns([
                TextColumn::make('komponenBiaya.nama_komponen')
                    ->label('Komponen Biaya')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('berlaku_semester')
                    ->label('Semester')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ?: 'Semua'),

                TextColumn::make('penerapan')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'FLAT' => 'success',
                        'ONCE' => 'warning',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'FLAT' => 'Tiap Semester',
                        'ONCE' => 'Sekali',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
