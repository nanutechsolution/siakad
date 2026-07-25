<?php

namespace App\Filament\Resources\LpmAkreditasiElemens\RelationManagers;

use App\Models\LpmIndikator;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IndikatorsRelationManager extends RelationManager
{
    protected static string $relationship = 'indikators';

    protected static ?string $title = 'Indikator Penilaian';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('deskripsi')
                    ->label('Deskripsi Indikator')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                TextInput::make('bobot')
                    ->label('Bobot')
                    ->numeric()
                    ->step(0.01),
                Select::make('indikator_siakad_id')
                    ->label('Sumber Data Otomatis (opsional)')
                    ->options(fn() => LpmIndikator::query()->pluck('nama_indikator', 'id'))
                    ->searchable()
                    ->helperText('Tautkan ke indikator SIAKAD kalau butir ini bisa dipenuhi otomatis dari data yang sudah ada.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('deskripsi')
            ->columns([
                TextColumn::make('deskripsi')->label('Deskripsi')->wrap(),
                TextColumn::make('bobot')->label('Bobot')->numeric(2),
                TextColumn::make('indikatorSiakad.nama_indikator')->label('Sumber Data Otomatis')->toggleable(),
                TextColumn::make('evidences_count')->label('Jumlah Evidence')->counts('evidences'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
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
