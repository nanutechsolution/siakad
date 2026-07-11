<?php

namespace App\Filament\Resources\MasterBeasiswas\RelationManagers;

use App\Enums\Keuangan\TipeDiskonBeasiswa;
use Closure;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $title = 'Komponen Biaya yang Didiskon';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('komponen_biaya_id')
                    ->label('Komponen Biaya')
                    ->relationship('komponenBiaya', 'nama_komponen')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule, RelationManager $livewire) =>
                        $rule->where('beasiswa_id', $livewire->getOwnerRecord()->id)
                    )
                    ->validationMessages([
                        'unique' => 'Komponen biaya ini sudah didaftarkan pada beasiswa ini.',
                    ])
                    ->columnSpanFull(),

                Select::make('tipe_diskon')
                    ->label('Tipe Diskon')
                    ->options(TipeDiskonBeasiswa::class)
                    ->required()
                    ->live()
                    // Proteksi RBAC tingkat form
                    ->disabled(fn(): bool => ! auth()->user()->can('UpdateNilaiDiskon')),

                TextInput::make('nilai_diskon')
                    ->label('Nilai Diskon')
                    ->numeric()
                    ->required()
                    // Proteksi RBAC tingkat form
                    ->disabled(fn(): bool => ! auth()->user()->can('UpdateNilaiDiskon'))
                    ->prefix(fn(Get $get) => $get('tipe_diskon') === TipeDiskonBeasiswa::NOMINAL->value ? 'Rp' : null)
                    ->suffix(fn(Get $get) => $get('tipe_diskon') === TipeDiskonBeasiswa::PERSENTASE->value ? '%' : null)
                    ->rules([
                        fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                            if ((float) $value <= 0) {
                                $fail('Nilai diskon harus lebih dari 0.');
                            }
                            if ($get('tipe_diskon') === TipeDiskonBeasiswa::PERSENTASE->value && (float) $value > 100) {
                                $fail('Nilai persentase tidak boleh lebih dari 100%.');
                            }
                        },
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('komponenBiaya.nama_komponen')
            ->columns([
                TextColumn::make('komponenBiaya.nama_komponen')
                    ->label('Komponen Biaya')
                    ->weight('bold'),
                TextColumn::make('tipe_diskon')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('nilai_diskon')
                    ->label('Nilai Diskon')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->tipe_diskon === TipeDiskonBeasiswa::PERSENTASE) {
                            return rtrim(rtrim((string)$state, '0'), '.') . ' %';
                        }
                        return 'Rp ' . number_format((float)$state, 2, ',', '.');
                    })
                    ->color('success')
                    ->weight('bold'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false)
                    ->label("Tambah Item Diskon")
                    ->visible(fn(): bool => auth()->user()->can('UpdateNilaiDiskon')),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn(): bool => auth()->user()->can('UpdateNilaiDiskon')),
                DeleteAction::make()
                    ->visible(fn(): bool => auth()->user()->can('UpdateNilaiDiskon')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
