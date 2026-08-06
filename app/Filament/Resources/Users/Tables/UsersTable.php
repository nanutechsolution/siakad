<?php

namespace App\Filament\Resources\Users\Tables;

use App\Helpers\SecurityHelper;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Tampilan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable()
                    ->action(function ($record, $column) {
                        $name = $column->getName();
                        $record->update([
                            $name => !$record->$name
                        ]);
                    })
                    ->tooltip('Klik untuk mengubah status aktif'),

                TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TrashedFilter::make(),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Hanya yang Aktif')
                    ->falseLabel('Hanya yang Non-aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('reset_password')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password Pengguna')
                    ->modalDescription('Password baru yang kuat akan di-generate. User akan dipaksa mengganti password saat login berikutnya.')
                    ->action(function (User $record) {
                        $newPassword = SecurityHelper::generateStrongPassword();
                        $record->update([
                            'password' => bcrypt($newPassword),
                            'must_change_password' => true,
                            'failed_login_attempts' => 0, // Reset percobaan login
                            'locked_at' => null // Unlock account
                        ]);

                        // Menggunakan Persistent Notification agar Admin bisa mencopy password
                        Notification::make()
                            ->title('Password Berhasil Direset')
                            ->body("Password baru untuk **{$record->username}** adalah: \n\n **`{$newPassword}`** \n\n Harap segera berikan kepada user.")
                            ->success()
                            ->persistent()
                            ->send();

                        activity()->performedOn($record)->log('Password reset by Admin');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
