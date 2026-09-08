<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Pages;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use App\Models\DosenPengampu;
use App\Models\Kelas;
use App\Models\TrxDosen;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class PlottingDosenPage extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = JadwalGeneratorBatchResource::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Plotting Dosen';
    protected static ?string $title = 'Plotting Dosen Pengampu - Ganjil 2026/2027';
    protected string $view = 'filament.resources.jadwal-generator-batches.pages.plotting-dosen-page';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Ambil data KELAS beserta relasi Mata Kuliah dan Dosen Pengampunya
                Kelas::query()->with(['mataKuliah', 'dosenPengampus.dosen'])
            )
            ->groups([
                // FITUR UX 1: Grouping berdasarkan Mata Kuliah (Tampilan jadi sangat rapi!)
                Group::make('mataKuliah.nama_mata_kuliah')
                    ->label('Mata Kuliah')
                    ->collapsible()
            ])
            ->defaultGroup('mataKuliah.nama_mata_kuliah')
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('mataKuliah.sks_total')
                    ->label('SKS')
                    ->numeric(),

                // Menampilkan daftar nama dosen sebagai Badge (Bagus untuk Team Teaching)
                TextColumn::make('dosenPengampus.dosen.nama_dosen')
                    ->label('Dosen Pengampu')
                    ->badge()
                    ->color('success')
                    ->default('— Kosong —'),

                // Indikator Status Lengkap / Belum
                TextColumn::make('status_plot')
                    ->label('Status')
                    ->badge()
                    ->state(function (Kelas $record): string {
                        return $record->dosenPengampus->count() > 0 ? '🟢 Lengkap' : '🔴 Belum';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '🟢 Lengkap' => 'success',
                        '🔴 Belum' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Filter Prodi')
                    ->relationship('prodi', 'nama_prodi'),

                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        '1' => 'Semester 1',
                        '3' => 'Semester 3',
                        '5' => 'Semester 5',
                        '7' => 'Semester 7',
                    ])
            ])
            ->recordActions([
                // FITUR UX 2: Tombol Atur Dosen dengan Real-Time SKS Indicator
                Action::make('atur_dosen')
                    ->label('Set Dosen')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->modalHeading(fn(Kelas $record) => "Atur Dosen: Kelas {$record->nama_kelas}")
                    ->modalDescription('Anda bisa memilih lebih dari 1 dosen untuk Team Teaching.')
                    ->modalWidth('md')
                    // Isi data default ke dalam dropdown saat modal dibuka
                    ->fillForm(fn(Kelas $record): array => [
                        'dosen_ids' => $record->dosenPengampus->pluck('dosen_id')->toArray(),
                    ])
                    ->schema([
                        Select::make('dosen_ids')
                            ->label('Pilih Dosen')
                            ->multiple() // Mendukung lebih dari 1 dosen (Team Teaching)
                            ->searchable()
                            ->required()
                            ->options(function () {
                                // ⚡ REAL-TIME SKS CALCULATION ⚡
                                // Skrip ini dieksekusi SETIAP KALI tombol diklik, sehingga SKS selalu update!
                                $dosens = TrxDosen::with(['dosenPengampus.mataKuliah'])->get();

                                return $dosens->mapWithKeys(function ($dosen) {
                                    // Hitung total SKS yang sudah diemban dosen ini
                                    $totalSks = $dosen->dosenPengampus->sum(function ($pengampu) {
                                        return $pengampu->mataKuliah->sks_total ?? 0;
                                    });

                                    // Beri peringatan merah jika beban melebihi batas (misal: 12 SKS)
                                    $warning = $totalSks >= 12 ? ' ⚠️ (Overload)' : '';

                                    // Hasil di dropdown: "Yohanes - Beban: 9 SKS"
                                    return [$dosen->id => "{$dosen->nama_dosen} - Beban: {$totalSks} SKS{$warning}"];
                                });
                            })
                    ])
                    ->action(function (array $data, Kelas $record): void {
                        // 1. Hapus plotting lama untuk kelas ini
                        DosenPengampu::where('kelas_id', $record->id)->delete();

                        // 2. Simpan plotting dosen yang baru (Bisa lebih dari 1)
                        foreach ($data['dosen_ids'] as $dosenId) {
                            DosenPengampu::create([
                                'kelas_id' => $record->id,
                                'mata_kuliah_id' => $record->mata_kuliah_id, // Asumsi kelas punya foreign key mata_kuliah_id
                                'dosen_id' => $dosenId,
                                'tahun_akademik_id' => 1, // Sesuaikan dengan Tahun Akademik aktif Anda
                            ]);
                        }
                    }),
            ]);
    }
}
