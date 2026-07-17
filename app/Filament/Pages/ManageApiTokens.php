<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class ManageApiTokens extends Page
{
    use HasPageShield;
    protected string $view = 'filament.pages.manage-api-tokens';
    protected static ?string $navigationLabel = 'Integrasi API';
    protected static ?string $modelLabel = 'Integrasi API';
    protected static ?string $pluralModelLabel = 'Integrasi API';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTEGRASI->value;

    public ?string $newToken = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateToken')
                ->label('Generate Token Baru')
                ->icon('heroicon-o-plus')
                ->action(function () {
                    // Cari user khusus sistem yang tadi dibuat
                    $user = User::where('username', 'superadmin')->first();

                    if (!$user) {
                        Notification::make()->title('User Integrasi PMB belum dibuat!')->danger()->send();
                        return;
                    }

                    // Hapus token lama jika perlu, atau langsung buat baru
                    $user->tokens()->delete();
                    $token = $user->createToken('PMB-System-Token')->plainTextToken;

                    $this->newToken = $token;

                    Notification::make()->title('Token berhasil dibuat!')->success()->send();
                })
        ];
    }
}
