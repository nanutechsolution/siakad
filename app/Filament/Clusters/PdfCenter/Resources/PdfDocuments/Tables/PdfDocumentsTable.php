<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Tables;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PdfDocument;
use App\Services\Pdf\PdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PdfDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => PdfDocumentType::from($state)->label())
                    ->searchable(),

                TextColumn::make('nomor_dokumen')
                    ->label('Nomor')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('classification')
                    ->label('Klasifikasi')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'archived' => 'success',
                        'semi_permanent' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'final' => 'success',
                        'draft' => 'gray',
                        'revoked' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('version')
                    ->label('Versi')
                    ->alignCenter(),

                IconColumn::make('is_current')
                    ->label('Terkini')
                    ->boolean(),

                TextColumn::make('documentable_type')
                    ->label('Terkait')
                    ->formatStateUsing(fn(?string $state) => $state ? class_basename($state) : '—'),

                TextColumn::make('generated_at')
                    ->label('Diterbitkan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options(collect(PdfDocumentType::cases())
                        ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                        ->all()),

                SelectFilter::make('classification')
                    ->label('Klasifikasi')
                    ->options([
                        'dynamic' => 'Dynamic',
                        'semi_permanent' => 'Semi-Permanent',
                        'archived' => 'Archived',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'final' => 'Final',
                        'revoked' => 'Revoked',
                    ]),

                TernaryFilter::make('is_current')
                    ->label('Hanya Versi Terkini'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('unduh')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn(PdfDocument $record) => app(PdfService::class)->downloadArchived($record)),
            ])
            ->defaultSort('generated_at', 'desc');
    }
}
