<?php

namespace App\Filament\Resources\LpmAmiFindings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LpmAmiFindingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periode.nama_periode')->label('Periode')->searchable(),
                TextColumn::make('prodi.nama_prodi')->label('Prodi')->searchable(),
                TextColumn::make('standar.nama_standar')->label('Standar')->wrap(),
                TextColumn::make('klasifikasi')
                    ->label('Klasifikasi')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'KTS_MAYOR' => 'danger',
                        'KTS_MINOR' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('auditor_name')->label('Auditor'),
                TextColumn::make('status_workflow')->label('Status')->badge(),
                TextColumn::make('deadline_perbaikan')->label('Deadline')->date('d/m/Y'),
                IconColumn::make('is_closed')->label('Closed')->boolean(),
            ])
            ->filters([
                SelectFilter::make('periode_id')
                    ->label('Periode')
                    ->relationship('periode', 'nama_periode'),
                SelectFilter::make('klasifikasi')
                    ->options([
                        'OB' => 'Observasi',
                        'KTS_MINOR' => 'KTS Minor',
                        'KTS_MAYOR' => 'KTS Mayor',
                    ]),
                SelectFilter::make('status_workflow')
                    ->options([
                        'OPEN' => 'Open',
                        'ACTION_PLAN' => 'Action Plan',
                        'VERIFICATION' => 'Verification',
                        'CLOSED' => 'Closed',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
