<?php

namespace App\Filament\Resources\LpmDokumens\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';
    protected static ?string $title = 'Alur Persetujuan (Penyusun / Pemeriksa / Pengesah)';
    public function form(Schema $schema): Schema
    {
        return $schema
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
                        'PENYUSUN' => 'Penyusun',
                        'PEMERIKSA' => 'Pemeriksa',
                        'PENGESAH' => 'Pengesah',
                    ])
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'PENDING' => 'Menunggu',
                        'APPROVED' => 'Disetujui',
                        'REJECTED' => 'Ditolak',
                    ])
                    ->default('PENDING')
                    ->live()
                    ->required(),
                Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(2),
                DateTimePicker::make('approved_at')
                    ->label('Tanggal Persetujuan')
                    ->visible(fn(Get $get) => $get('status') === 'APPROVED'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('peran')
            ->columns([
                TextColumn::make('person.nama_lengkap')->label('Nama')->searchable(),
                TextColumn::make('peran')->label('Peran')->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('catatan')->label('Catatan')->limit(40),
                TextColumn::make('approved_at')->label('Disetujui Pada')->dateTime('d/m/Y H:i'),
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
