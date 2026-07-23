<?php

namespace App\Filament\Widgets\Clusters\Migration\Widgets;

use App\Models\MigrationBatch as ModelsMigrationBatch;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use MigrationBatch;

class RecentMigrationBatchesWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ModelsMigrationBatch::query()->latest('id')->limit(10))
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('source')->label('Sumber')->badge()
                    ->formatStateUsing(fn($state) => $state->label()),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn($state) => $state->color())
                    ->formatStateUsing(fn($state) => $state->label()),
                TextColumn::make('total_rows')->label('Total Baris'),
                TextColumn::make('total_berhasil')->label('Berhasil')->color('success'),
                TextColumn::make('total_gagal')->label('Gagal')->color('danger'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
            ])
            ->paginated(false);
    }
}
