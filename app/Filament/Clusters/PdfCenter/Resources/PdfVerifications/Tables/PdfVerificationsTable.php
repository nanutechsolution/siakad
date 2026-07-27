<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Tables;

use App\Models\PdfVerification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PdfVerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_dokumen_diminta')
                    ->label('Kode/ID Diminta')
                    ->searchable(),
                IconColumn::make('ditemukan')
                    ->label('Ditemukan')
                    ->boolean(),
                TextColumn::make('ip_address')->label('IP'),
                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(40)
                    ->tooltip(fn(PdfVerification $record) => $record->user_agent),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('ditemukan')
                    ->label('Status'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
