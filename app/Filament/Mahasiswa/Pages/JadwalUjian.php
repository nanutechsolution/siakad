<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Filament\Mahasiswa\Concerns\ResolvesMahasiswa;
use App\Models\JadwalUjianPeserta;
use App\Models\RefTahunAkademik;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class JadwalUjian extends Page implements HasTable
{

    use InteractsWithTable;
    use ResolvesMahasiswa;


    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::PERKULIAHAN->value;
    protected static ?string $navigationLabel = 'Jadwal Ujian';
    protected static ?string $title = 'Jadwal Ujian';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'jadwal-ujian';
    protected string $view = 'filament.mahasiswa.pages.jadwal-ujian';

    public function mount(): void
    {
        $this->currentMahasiswa();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading('Jadwal Ujian Saya')
            ->emptyStateHeading('Belum ada jadwal ujian')
            ->emptyStateDescription(
                'Jadwal ujian akan muncul di sini setelah bagian akademik menetapkan '
                    . 'jadwal UTS/UAS dan Anda terdaftar sebagai peserta.'
            )
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->columns([
                TextColumn::make('jadwalUjian.tanggal_ujian')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->description(
                        fn(?JadwalUjianPeserta $record): string =>
                        $record?->jadwalUjian
                            ? $record->jadwalUjian->jam_mulai->format('H:i') .
                            ' - ' .
                            $record->jadwalUjian->jam_selesai->format('H:i')
                            : ''
                    ),
                TextColumn::make('jadwalUjian.jenis_ujian')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'UTS' => 'info',
                        'UAS' => 'warning',
                        'SUSULAN' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('jadwalUjian.jadwalKuliah.mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->description(
                        fn(JadwalUjianPeserta $record): ?string =>
                        $record->jadwalUjian?->jadwalKuliah?->mataKuliah?->kode_mk
                    )
                    ->wrap(),

                TextColumn::make('jadwalUjian.ruang.nama_ruang')
                    ->label('Ruang')
                    ->default('Belum ditentukan')
                    ->description(
                        fn(JadwalUjianPeserta $record): ?string => $record->jadwalUjian?->metode_ujian
                    ),

                TextColumn::make('nomor_kursi')
                    ->label('No. Kursi')
                    ->default('-')
                    ->alignCenter(),

                TextColumn::make('status_kehadiran')
                    ->label('Kehadiran')
                    ->badge()
                    ->formatStateUsing(fn(JadwalUjianPeserta $record): string => $record->status_label)
                    ->color(fn(?string $state): string => match ($state) {
                        'H' => 'success',
                        'I', 'S' => 'warning',
                        default => 'gray',
                    })
                    ->visible(
                        fn(?JadwalUjianPeserta $record): bool =>
                        $record?->jadwalUjian?->tanggal_ujian?->isPast() ?? false
                    ),
            ])
            ->filters([
                SelectFilter::make('tahun_akademik')
                    ->label('Semester')
                    ->options(fn(): array => $this->opsiTahunAkademik())
                    ->default(fn() => $this->tahunAkademikAktif()?->id)
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'jadwalUjian.jadwalKuliah',
                            fn(Builder $q) => $q->where('tahun_akademik_id', $data['value'])
                        );
                    }),

                SelectFilter::make('jenis_ujian')
                    ->label('Jenis Ujian')
                    ->options([
                        'UTS' => 'UTS',
                        'UAS' => 'UAS',
                        'SUSULAN' => 'Susulan',
                        'LAINNYA' => 'Lainnya',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'jadwalUjian',
                            fn(Builder $q) => $q->where('jenis_ujian', $data['value'])
                        );
                    }),
            ])
            ->defaultSort('jadwalUjian.tanggal_ujian')
            ->paginated([10, 25, 50]);
    }

    protected function getTableQuery(): Builder
    {
        $krsDetailIds = $this->currentMahasiswa()
            ->krs()
            ->berlaku()
            ->with('details:id,krs_id')
            ->get()
            ->pluck('details')
            ->flatten()
            ->pluck('id');

        return JadwalUjianPeserta::query()
            ->with([
                'jadwalUjian.ruang',
                'jadwalUjian.jadwalKuliah.mataKuliah',
            ])
            ->whereIn('krs_detail_id', $krsDetailIds)
            ->whereHas('jadwalUjian');
    }

    /** @return array<int, string> */
    protected function opsiTahunAkademik(): array
    {
        return RefTahunAkademik::query()
            ->whereIn('id', $this->currentMahasiswa()->krs()->pluck('tahun_akademik_id'))
            ->orderByDesc('tanggal_mulai')
            ->pluck('nama_tahun', 'id')
            ->all();
    }
}
