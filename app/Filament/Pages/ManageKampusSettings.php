<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Settings\KampusSettings as SettingsKampusSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
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
            ]);
    }
}
