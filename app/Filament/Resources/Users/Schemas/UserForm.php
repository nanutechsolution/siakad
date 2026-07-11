<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Kredensial Akun')
                            ->description('Informasi utama untuk login ke dalam sistem.')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Tampilan (Display Name)')
                                    ->required()
                                    ->maxLength(255),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('username')
                                            ->label('Username')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                    ]),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                    ->dehydrated(fn(?string $state): bool => filled($state))
                                    ->required(fn(Page $livewire): bool => $livewire instanceof CreateUser)
                                    ->maxLength(255)
                                    ->helperText('Biarkan kosong jika tidak ingin mengubah password saat mengedit data.')
                                    ->revealable(),
                            ]),

                        Section::make('Tautan Profil (SSOT)')
                            ->description('Kaitkan akun ini dengan entitas fisik/person yang ada di sistem.')
                            ->schema([
                                Select::make('person_id')
                                    ->label('Person (Entitas Utama)')
                                    ->relationship('person', 'nama_lengkap')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Jika dikosongkan, user ini dianggap sebagai Admin/Sistem murni.'),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Akses & Keamanan')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Akun non-aktif tidak akan bisa login.'),

                                Select::make('roles')
                                    ->label('Roles (Hak Akses)')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),

                        Section::make('Riwayat Login')
                            ->hidden(fn(Page $livewire): bool => $livewire instanceof CreateUser)
                            ->schema([
                                TextEntry::make('last_login_at')
                                    ->label('Login Terakhir')
                                    ->state(fn(?User $record): string => $record?->last_login_at ? $record->last_login_at->format('d M Y, H:i') : 'Belum pernah login'),
                                TextEntry::make('last_login_ip')
                                    ->label('IP Address Terakhir')
                                    ->state(fn(?User $record): string => $record?->last_login_ip ?? '-'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
