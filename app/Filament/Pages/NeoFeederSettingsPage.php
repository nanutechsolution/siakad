<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Services\NeoFeeder\NeoFeederAuthenticator;
use App\Services\NeoFeeder\NeoFeederConnectionTester;
use App\Services\NeoFeeder\NeoFeederSettingsService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class NeoFeederSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::INTEGRASI->value;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Neo Feeder';

    protected static ?string $title = 'Pengaturan Neo Feeder';

    protected static ?string $slug = 'neo-feeder';

    protected  string $view = 'filament.pages.neo-feeder-settings-page';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(NeoFeederSettingsService $settings): void
    {
        $this->form->fill($settings->formData());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Koneksi Neo Feeder')
                    ->description('"Ambil Token" dan "Test Koneksi" memakai pengaturan yang sudah DISIMPAN.')
                    ->schema([
                        TextInput::make('url')
                            ->label('URL Neo Feeder')
                            ->placeholder('https://neo.unmaris.my.id:3003/ws/live2.php')
                            ->url()
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->maxLength(191)
                            ->autocomplete(false),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn(): bool => ! app(NeoFeederSettingsService::class)->hasPassword())
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->helperText('Kosongkan jika tidak ingin mengubah password yang tersimpan.'),

                        Toggle::make('verify_ssl')
                            ->label('Verifikasi sertifikat SSL')
                            ->helperText('Matikan hanya jika Neo Feeder memakai sertifikat self-signed.')
                            ->default(true),

                        TextInput::make('timeout')
                            ->label('Timeout')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(600)
                            ->suffix('detik')
                            ->required(),

                        TextInput::make('connect_timeout')
                            ->label('Connect timeout')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->suffix('detik')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(NeoFeederSettingsService $settings): void
    {
        $settings->save($this->form->getState());

        // Kosongkan lagi field password di browser.
        $this->form->fill($settings->formData());

        Notification::make()
            ->title('Pengaturan Neo Feeder disimpan.')
            ->success()
            ->send();
    }

    public function getTokenStatus(): string
    {
        return app(NeoFeederSettingsService::class)->tokenStatus();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetchToken')
                ->label('Ambil Token')
                ->icon('heroicon-o-key')
                ->color('gray')
                ->action(function (NeoFeederAuthenticator $authenticator): void {
                    $result = $authenticator->refreshToken();

                    // User biasa hanya melihat pesan singkat; detail ada di log.
                    $notification = Notification::make()->title(
                        $result->isConnected()
                            ? 'Token Neo Feeder berhasil diperbarui.'
                            : $result->message(),
                    );

                    if ($result->isConnected()) {
                        $notification->success();
                    } else {
                        $notification->danger();
                    }

                    $notification->send();
                }),

            Action::make('testConnection')
                ->label('Test Koneksi Neo Feeder')
                ->icon('heroicon-o-signal')
                ->action(function (NeoFeederConnectionTester $tester): void {
                    $result = $tester->test();

                    $notification = Notification::make()->title($result->message());

                    if ($result->isConnected()) {
                        $notification->success();
                    } else {
                        $notification->danger();
                    }

                    $notification->send();
                }),
        ];
    }
}
