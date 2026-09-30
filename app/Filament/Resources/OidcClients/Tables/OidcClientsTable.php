<?php

declare(strict_types=1);

namespace App\Filament\Resources\OidcClients\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OidcClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Aplikasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('id')
                    ->label('Client ID')
                    ->copyable()
                    ->fontFamily('mono')
                    ->limit(12),

                TextColumn::make('scopes')
                    ->label('Scope')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => implode(' ', is_array($state) ? $state : []))
                    ->placeholder('-'),

                TextColumn::make('redirect_uris')
                    ->label('Redirect URI')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),

                IconColumn::make('revoked')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('revoked')->label('Dinonaktifkan'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('revoke_tokens')
                    ->label('Cabut Semua Token')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Semua access & refresh token milik client ini langsung tidak berlaku.')
                    ->action(function ($record): void {
                        $count = 0;

                        $record->tokens()->with('refreshToken')->get()->each(function ($token) use (&$count): void {
                            $token->refreshToken?->revoke();
                            $token->revoke();
                            $count++;
                        });

                        Notification::make()
                            ->title('Token dicabut')
                            ->body($count.' token untuk "'.($record->name ?? '').'" telah dicabut.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
