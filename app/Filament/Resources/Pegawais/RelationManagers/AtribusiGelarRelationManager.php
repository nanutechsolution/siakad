<?php

namespace App\Filament\Resources\Pegawais\RelationManagers;

use App\Models\RefGelar;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AtribusiGelarRelationManager extends RelationManager
{
    protected static string $relationship = 'atribusiGelar';
    protected static ?string $title = '🎓 Gelar Akademik';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('preview')
                    ->label('Preview Nama')
                    ->state(fn() => $this->ownerRecord->person?->nama_dengan_gelar),
                Select::make('gelar_id')
                    ->label('Gelar')
                    ->options(
                        RefGelar::query()
                            ->orderBy('jenjang')
                            ->orderBy('kode')
                            ->get()
                            ->mapWithKeys(fn($gelar) => [
                                $gelar->id => "{$gelar->kode} — {$gelar->nama} ({$gelar->jenjang->value})"
                            ])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->helperText('1 = paling depan sesuai posisi gelar'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->defaultSort('urutan')

            ->reorderable('urutan')

            ->columns([

                TextColumn::make('urutan')
                    ->label('#')
                    ->badge()
                    ->sortable(),

                TextColumn::make('gelar.kode')
                    ->label('Kode')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('gelar.nama')
                    ->label('Nama Gelar')
                    ->wrap(),

                TextColumn::make('gelar.posisi')
                    ->badge()
                    ->color(fn($state) => match ($state->value ?? $state) {
                        'DEPAN' => 'success',
                        'BELAKANG' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('gelar.jenjang')
                    ->badge()
                    ->color(fn($state) => match ($state->value ?? $state) {
                        'D3' => 'gray',
                        'D4' => 'warning',
                        'S1' => 'primary',
                        'S2' => 'success',
                        'S3' => 'danger',
                        'PROFESI' => 'info',
                        default => 'gray',
                    }),

            ])

            ->headerActions([

                CreateAction::make()
                    ->label('Tambah Gelar')
                    ->icon('heroicon-o-academic-cap')
                    ->createAnother(false)
                    ->modalWidth('lg'),

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
