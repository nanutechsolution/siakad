<?php

namespace App\Filament\Resources\LpmAkreditasis\RelationManagers;

use App\Filament\Resources\LpmAkreditasiKriterias\LpmAkreditasiKriteriaResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KriteriasRelationManager extends RelationManager
{
    protected static string $relationship = 'kriterias';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_kriteria')
                    ->label('Kode Kriteria')
                    ->required()
                    ->maxLength(20),
                TextInput::make('nama_kriteria')
                    ->label('Nama Kriteria')
                    ->required()
                    ->maxLength(255),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_kriteria')
            ->columns([
                TextColumn::make('kode_kriteria')->label('Kode'),
                TextColumn::make('nama_kriteria')->label('Nama Kriteria')->wrap(),
                TextColumn::make('elemens_count')->label('Jumlah Elemen')->counts('elemens'),
            ])
            ->defaultSort('urutan')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('kelolaElemen')
                    ->label('Kelola Elemen')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($record) => LpmAkreditasiKriteriaResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])->reorderable('urutan');
    }
}
