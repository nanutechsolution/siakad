<?php

namespace App\Filament\Resources\Pegawais\RelationManagers;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RiwayatRoleRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatRole';
    protected static ?string $title = 'Role Institusi / Bisnis';
    protected static ?string $pluralLabel = 'Role Institusi / Bisnis';
    protected static string|BackedEnum|null $icon = 'heroicon-o-identification';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role_id')
                    ->label('Role Institusi')
                    ->relationship('roleBisnis', 'nama_role')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->native(false),
                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->native(false)
                    ->afterOrEqual('tanggal_mulai')
                    ->placeholder('Masih Aktif'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('roleBisnis.nama_role')
            ->columns([
                TextColumn::make('roleBisnis.kode_role')
                    ->label('Kode Role')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('roleBisnis.nama_role')
                    ->weight('bold'),
                TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d M Y'),
                TextColumn::make('tanggal_selesai')
                    ->label('Tgl Selesai')
                    ->date('d M Y')
                    ->placeholder('Aktif'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label("Kaitkan Role Institusi")->createAnother(false),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
