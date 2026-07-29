<?php

namespace App\Filament\Resources\LpmIkuTargets\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BuktiPelaksanaansRelationManager extends RelationManager
{
    protected static string $relationship = 'buktiPelaksanaans';

    protected static ?string $title = 'Bukti Pelaksanaan Tindak Lanjut';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('unit_kerja_id')
                    ->label('Unit Kerja')
                    ->relationship('unitKerja', 'nama_unit')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('judul')
                    ->label('Judul Bukti')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->label('File')
                    ->directory('lpm/bukti-pelaksanaan')
                    ->required(),
                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(2),
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('judul')
            ->columns([
                TextColumn::make('judul')->label('Judul')->wrap(),
                TextColumn::make('unitKerja.nama_unit')->label('Unit Kerja'),
                TextColumn::make('tanggal')->label('Tanggal')->date('d/m/Y'),

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
            ])->defaultSort('tanggal', 'desc');
    }
}
