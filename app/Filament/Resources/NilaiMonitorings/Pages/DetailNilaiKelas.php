<?php

namespace App\Filament\Resources\NilaiMonitorings\Pages;

use App\Filament\Resources\NilaiMonitorings\NilaiMonitoringResource;
use App\Models\KrsDetail;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class DetailNilaiKelas extends Page implements HasTable
{
    use InteractsWithRecord, InteractsWithTable;
    protected static string $resource = NilaiMonitoringResource::class;

    protected string $view = 'filament.resources.nilai-monitorings.pages.detail-nilai-kelas';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    public function table(Table $table): Table
    {
        return $table
            // Query mengarah ke KrsDetail milik Kelas (JadwalKuliah) ini
            ->query(KrsDetail::query()->where('jadwal_kuliah_id', $this->record->id)->with('krs.mahasiswa'))
            ->columns([
                TextColumn::make('krs.mahasiswa.nim')->label('NIM')->searchable(),
                // Sesuaikan 'nama' dengan field nama mahasiswa di database Anda (misal: 'person.nama_lengkap')
                TextColumn::make('krs.mahasiswa.person.nama_lengkap')->label('Nama Mahasiswa')->searchable(),
                TextColumn::make('nilai_angka')->label('Nilai Angka'),
                TextColumn::make('nilai_huruf')->label('Huruf'),
                TextColumn::make('nilai_indeks')->label('Bobot'),
                IconColumn::make('is_published')
                    ->label('Publish')
                    ->boolean(),
                IconColumn::make('is_locked')
                    ->label('Terkunci')
                    ->boolean(),
            ])
            ->recordActions([
                // ACTION: KOREKSI NILAI BARA
                Action::make('koreksi')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->label('Koreksi')
                    ->schema([
                        TextInput::make('nilai_angka_baru')
                            ->label('Nilai Angka Baru')
                            ->numeric()
                            ->required(),
                        TextInput::make('nilai_huruf_baru')
                            ->label('Nilai Huruf Baru')
                            ->required(),
                        Textarea::make('alasan_perbaikan')
                            ->label('Alasan Perbaikan')
                            ->required()
                            ->helperText('Wajib diisi sebagai audit trail.'),
                    ])
                    ->action(function (KrsDetail $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            // 1. Catat ke tabel akademik_grade_revision_logs
                            DB::table('akademik_grade_revision_logs')->insert([
                                'krs_detail_id' => $record->id,
                                'executed_by' => auth()->id(),
                                'old_nilai_angka' => $record->nilai_angka,
                                'new_nilai_angka' => $data['nilai_angka_baru'],
                                'old_nilai_huruf' => $record->nilai_huruf,
                                'new_nilai_huruf' => $data['nilai_huruf_baru'],
                                'alasan_perbaikan' => $data['alasan_perbaikan'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            // 2. Update Nilai Utama
                            $record->update([
                                'nilai_angka' => $data['nilai_angka_baru'],
                                'nilai_huruf' => $data['nilai_huruf_baru'],
                            ]);
                        });
                    }),

                // ACTION: LIHAT HISTORI REVISI
                Action::make('histori')
                    ->label('Histori')
                    ->icon('heroicon-o-clock')
                    ->modalContent(fn(KrsDetail $record) => view('filament.modals.histori-nilai', [
                        'histori' => DB::table('akademik_grade_revision_logs')
                            ->where('krs_detail_id', $record->id)
                            ->orderByDesc('created_at')
                            ->get()
                    ]))
                    ->modalSubmitAction(false)
            ]);
    }
}
