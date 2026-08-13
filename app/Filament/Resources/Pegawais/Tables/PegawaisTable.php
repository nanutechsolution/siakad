<?php

namespace App\Filament\Resources\Pegawais\Tables;

use App\Enums\HR\JenisPegawai;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PegawaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | FOTO
                |--------------------------------------------------------------------------
                */

                ImageColumn::make('person.photo_path')
                    ->label('')
                    ->imageSize(44)
                    ->circular()
                    ->defaultImageUrl(
                        url('/images/default-avatar.png')
                    )
                    ->extraImgAttributes([
                        'alt' => 'Foto pegawai',
                        'loading' => 'lazy',
                    ])
                    ->width('1%'),

                /*
                |--------------------------------------------------------------------------
                | PEGAWAI
                |--------------------------------------------------------------------------
                |
                | Nama menjadi informasi utama.
                | NIP ditampilkan sebagai description agar tabel tidak terlalu lebar.
                |
                */

                TextColumn::make('person.nama_lengkap')
                    ->label('Pegawai')
                    ->formatStateUsing(
                        fn($record): string =>
                        $record->person?->nama_dengan_gelar
                            ?? $record->person?->nama_lengkap
                            ?? '-'
                    )
                    ->description(
                        fn($record): string =>
                        'NIP: ' . ($record->nip ?: '-')
                    )
                    ->weight('bold')
                    ->grow()
                    ->searchable(
                        query: function (
                            Builder $query,
                            string $search
                        ): Builder {
                            return $query->where(function (
                                Builder $query
                            ) use ($search) {

                                $query
                                    ->where(
                                        'nip',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhereHas(
                                        'person',
                                        function (
                                            Builder $personQuery
                                        ) use ($search) {
                                            $personQuery->where(
                                                'nama_lengkap',
                                                'like',
                                                "%{$search}%"
                                            );
                                        }
                                    );
                            });
                        }
                    )
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | JENIS KELAMIN
                |--------------------------------------------------------------------------
                */

                TextColumn::make('person.jenis_kelamin')
                    ->label('L/P')
                    ->badge()
                    ->formatStateUsing(
                        fn(?string $state): string => match ($state) {
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                            default => '—',
                        }
                    )
                    ->color(
                        fn(?string $state): string => match ($state) {
                            'L' => 'info',
                            'P' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable()
                    ->alignCenter(),

                /*
                |--------------------------------------------------------------------------
                | STATUS PEGAWAI
                |--------------------------------------------------------------------------
                */

                TextColumn::make('jenis_pegawai')
                    ->label('Status Pegawai')
                    ->badge()
                    ->sortable()
                    ->alignCenter(),

                /*
                |--------------------------------------------------------------------------
                | STATUS AKTIF
                |--------------------------------------------------------------------------
                */

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(
                        fn(bool $state): string =>
                        $state
                            ? 'Pegawai aktif'
                            : 'Pegawai tidak aktif'
                    )
                    ->sortable()
                    ->alignCenter(),

                /*
                |--------------------------------------------------------------------------
                | EMAIL
                |--------------------------------------------------------------------------
                |
                | Tidak tampil di awal agar tabel tetap bersih.
                | SDM dapat mengaktifkannya melalui column manager.
                |
                */

                TextColumn::make('person.email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->copyMessage('Email berhasil disalin')
                    ->copyMessageDuration(1500)
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                /*
                |--------------------------------------------------------------------------
                | NO HP
                |--------------------------------------------------------------------------
                */

                TextColumn::make('person.no_hp')
                    ->label('No. HP')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Nomor HP berhasil disalin')
                    ->copyMessageDuration(1500)
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                /*
                |--------------------------------------------------------------------------
                | NIK
                |--------------------------------------------------------------------------
                */

                TextColumn::make('person.nik')
                    ->label('NIK')
                    ->copyable()
                    ->copyMessage('NIK berhasil disalin')
                    ->copyMessageDuration(1500)
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                /*
                |--------------------------------------------------------------------------
                | TEMPAT LAHIR
                |--------------------------------------------------------------------------
                */

                TextColumn::make('person.tempat_lahir')
                    ->label('Tempat Lahir')
                    ->placeholder('—')
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                /*
                |--------------------------------------------------------------------------
                | TANGGAL LAHIR
                |--------------------------------------------------------------------------
                */

                TextColumn::make('person.tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                /*
                |--------------------------------------------------------------------------
                | TANGGAL REGISTRASI
                |--------------------------------------------------------------------------
                */

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | FILTER
            |--------------------------------------------------------------------------
            */

            ->filters([

                SelectFilter::make('jenis_pegawai')
                    ->label('Status Pegawai')
                    ->options(JenisPegawai::class)
                    ->native(false),

                SelectFilter::make('person.jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Keaktifan')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->placeholder('Semua'),

            ])

            /*
            |--------------------------------------------------------------------------
            | AKSI PER BARIS
            |--------------------------------------------------------------------------
            */

            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->slideOver()
                    ->icon('heroicon-o-eye'),

                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil'),

            ])

            /*
            |--------------------------------------------------------------------------
            | BULK ACTION
            |--------------------------------------------------------------------------
            */

            ->toolbarActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),

            ])

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            ->searchPlaceholder(
                'Cari nama, NIP, NIK, email, atau nomor HP...'
            )

            ->searchDebounce(400)

            /*
            |--------------------------------------------------------------------------
            | SORT DEFAULT
            |--------------------------------------------------------------------------
            */

            ->defaultSort(
                'created_at',
                'desc'
            )

            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */

            ->paginationPageOptions([
                10,
                25,
                50,
                100,
            ])

            ->defaultPaginationPageOption(25)

            /*
            |--------------------------------------------------------------------------
            | RESPONSIVE
            |--------------------------------------------------------------------------
            |
            | Pada layar kecil, informasi sekunder disembunyikan.
            |
            */

            ->deferLoading();
    }
}
