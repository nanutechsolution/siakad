<?php

namespace App\Filament\Resources\LpmUnitKerjas\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PicsRelationManager extends RelationManager
{
    protected static string $relationship = 'pics';
    protected static ?string $title = 'Penanggung Jawab (PIC)';


    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Select::make('person_id')
                    ->label('Nama')
                    ->relationship('person', 'nama_lengkap')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('peran')
                    ->label('Peran')
                    ->options([
                        'KETUA' => 'Ketua',
                        'SEKRETARIS' => 'Sekretaris',
                        'GKM' => 'Gugus Kendali Mutu',
                        'AUDITOR' => 'Auditor',
                        'ANGGOTA' => 'Anggota',
                    ])
                    ->required(),
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required(),
                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->helperText('Kosongkan jika masih menjabat.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('person.nama_lengkap')->label('Nama')->searchable(),
                TextColumn::make('peran')->label('Peran')->badge(),
                TextColumn::make('tanggal_mulai')->label('Mulai')->date('d/m/Y'),
                TextColumn::make('tanggal_selesai')->label('Selesai')->date('d/m/Y')->placeholder('Masih menjabat'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
