<?php

namespace App\Filament\Resources\RefPeople\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Grid;

class RefPersonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('photo_path')
                    ->hiddenLabel()
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=P&color=7F9CF5&background=EBF4FF'))
                    ->imageSize(150),
                TextEntry::make('nama_lengkap')
                    ->size(TextSize::Large)
                    ->weight('bold')
                    ->columnSpanFull(),

                TextEntry::make('nik')
                    ->label('NIK')
                    ->copyable(),

                TextEntry::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => $state === 'L' ? 'Laki-laki' : 'Perempuan')
                    ->color(fn(string $state): string => match ($state) {
                        'L' => 'info',
                        'P' => 'success',
                        default => 'gray',
                    }),

                TextEntry::make('tempat_lahir')
                    ->label('Tempat, Tanggal Lahir')
                    ->formatStateUsing(function ($record) {
                        $tempat = $record->tempat_lahir ?? '-';
                        $tgl = $record->tanggal_lahir ? $record->tanggal_lahir->format('d F Y') : '-';
                        return "{$tempat}, {$tgl}";
                    }),

                TextEntry::make('email')
                    ->icon('heroicon-m-envelope'),

                TextEntry::make('no_hp')
                    ->label('No. HP / WhatsApp')
                    ->icon('heroicon-m-phone'),
            ]);
    }
}
