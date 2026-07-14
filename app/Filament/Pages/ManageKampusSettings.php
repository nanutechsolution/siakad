<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Settings\KampusSettings as SettingsKampusSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageKampusSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static ?string $navigationLabel = 'Identitas Kampus';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SISTEM->value;
    protected static string $settings = SettingsKampusSettings::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pengaturan Kampus')
                    ->tabs([
                        Tabs\Tab::make('Identitas')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                TextInput::make('nama')->label('Nama Kampus')->required(),
                                TextInput::make('nama_singkat')->label('Singkatan')->required(),
                                TextInput::make('alamat')->label('Alamat')->required(),
                                TextInput::make('telepon')->label('Telepon')->required(),
                                TextInput::make('email')->label('Email')->email()->required(),
                                TextInput::make('website')->label('Website')->required(),
                                TextInput::make('akreditasi')->label('Akreditasi')->nullable(),
                                FileUpload::make('logo_path')
                                    ->label('Logo Kampus')
                                    ->image()
                                    ->directory('kampus')
                                    ->disk('public'),
                            ]),

                        Tabs\Tab::make('Sistem & Akademik')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Aturan NIM')
                                    ->schema([
                                        Toggle::make('reset_nim_tahunan')
                                            ->label('Reset Nomor Urut NIM Setiap Tahun')
                                            ->helperText('Jika aktif, nomor urut NIM akan kembali ke 0001 setiap ganti tahun angkatan.')
                                            ->default(true),
                                    ]),
                                Section::make('Integrasi Neo Feeder')
                                    ->schema([
                                        TextInput::make('neo_feeder_url')->label('URL Neo Feeder')->url()->default('http://localhost:8100'),
                                        TextInput::make('neo_feeder_username')->label('Username Neo Feeder'),
                                        TextInput::make('neo_feeder_password')->label('Password Neo Feeder')->password()->revealable(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Operasional')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Toggle::make('maintenance_mode')->label('Aktifkan Mode Perawatan'),
                                TextInput::make('semester_aktif')->label('Semester Aktif')->placeholder('2026/2027 Ganjil'),
                                TextInput::make('batas_maksimal_sks')->label('Batas SKS Maksimal')->numeric()->default(24),
                                Toggle::make('enable_sso_login')->label('Aktifkan SSO Login'),
                                TextInput::make('smtp_host')->label('SMTP Host')->placeholder('mail.unmaris.ac.id'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
