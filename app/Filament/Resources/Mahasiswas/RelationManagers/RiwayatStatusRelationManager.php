<?php

namespace App\Filament\Resources\Mahasiswas\RelationManagers;

use App\Enums\StatusKuliah;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class RiwayatStatusRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatStatus';
    protected static ?string $title = 'Riwayat Status & Akademik';
    protected static ?string $modelLabel = 'Riwayat Semester';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tahun_akademik_id')
                    ->label('Tahun Akademik')
                    ->relationship('tahunAkademik', 'nama_tahun')
                    ->required()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule, RelationManager $livewire) {
                            return $rule->where('mahasiswa_id', $livewire->getOwnerRecord()->id);
                        },
                        ignoreRecord: true
                    )
                    ->validationMessages([
                        'unique' => 'Riwayat untuk Tahun Akademik ini sudah ada.',
                    ]),

                Select::make('status_kuliah')
                    ->label('Status Kuliah')
                    ->options(collect(StatusKuliah::cases())->mapWithKeys(fn($enum) => [$enum->value => $enum->label()]))
                    ->required(),

                Grid::make(2)->schema([
                    TextInput::make('ips')
                        ->label('IPS (Indeks Prestasi Semester)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(4)
                        ->step(0.01)
                        ->default(0)
                        ->required(),

                    TextInput::make('ipk')
                        ->label('IPK (Indeks Prestasi Kumulatif)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(4)
                        ->step(0.01)
                        ->default(0)
                        ->required(),

                    TextInput::make('sks_semester')
                        ->label('SKS Semester')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    TextInput::make('sks_total')
                        ->label('SKS Total (Kumulatif)')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ]),

                TextInput::make('nomor_sk')
                    ->label('Nomor SK (Opsional)')
                    ->helperText('Diisi jika statusnya Cuti, Drop Out, Lulus, atau Mutasi.')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahun_akademik_id')
            ->defaultSort('tahun_akademik_id', 'desc')

            ->columns([
                TextColumn::make('tahunAkademik.nama_tahun')
                    ->label('Periode')
                    ->sortable(),

                TextColumn::make('status_kuliah')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => StatusKuliah::tryFrom($state)?->label() ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        StatusKuliah::AKTIF->value => 'success',
                        StatusKuliah::CUTI->value => 'warning',
                        StatusKuliah::LULUS->value => 'info',
                        default => 'danger',
                    }),

                TextColumn::make('ips')
                    ->label('IPS')
                    ->numeric(2),

                TextColumn::make('ipk')
                    ->label('IPK')
                    ->numeric(2),

                TextColumn::make('sks_semester')
                    ->label('SKS Sem.')
                    ->numeric(),

                TextColumn::make('sks_total')
                    ->label('Total SKS')
                    ->numeric(),

                TextColumn::make('nomor_sk')
                    ->label('No. SK')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Riwayat (Manual)'),
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
