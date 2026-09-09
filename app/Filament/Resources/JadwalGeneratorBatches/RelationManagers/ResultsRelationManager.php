<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\RelationManagers;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\HtmlString;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';
    protected static ?string $relatedResource = JadwalGeneratorBatchResource::class;
    protected static ?string $title = 'Preview Jadwal (Sandbox)';

    public function table(Table $table): Table
    {
        return $table
            ->description('Draf jadwal hasil komputasi mesin. Periksa jika ada baris berstatus Gagal, perbaiki data masternya, lalu tekan "Generate Ulang".')
            ->defaultSort('is_success', 'asc') // Selalu tampilkan yang GAGAL di urutan teratas
            ->columns([
                IconColumn::make('is_success')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                // 1. INFO MATA KULIAH, SKS, & PRODI
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah & Prodi')
                    ->weight('bold')
                    ->description(fn($record) => new HtmlString(
                        ($record->mataKuliah->kode_mk ?? '-') . ' &bull; <b>' . ($record->sks_real ?? 0) . ' SKS</b><br>' .
                            '<span style="color: gray; font-size: 0.8rem;">Prodi: ' . ($record->kelas->prodi->nama_prodi ?? 'Umum') . '</span>'
                    ))
                    ->searchable(['nama_mk'])
                    ->wrap(),

                // 2. INFO KELAS, ANGKATAN & KAPASITAS BEBAN
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas & Beban')
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn($record) => new HtmlString(
                        'Angkatan: ' . ($record->kelas->angkatan->id_tahun ?? '-') . '<br>' .
                            'Butuh: <b style="color: #ea580c;">' . ($record->estimasi_kapasitas_dibutuhkan ?? 0) . ' Kursi</b>'
                    ))
                    ->searchable(['nama_kelas']),

                // 3. INFO DOSEN PENGAMPU (Team Teaching Decoder)
                TextColumn::make('dosen_pengampu_ids')
                    ->label('Tim Dosen Pengajar')
                    ->formatStateUsing(function ($record) {
                        $ids = is_string($record->dosen_pengampu_ids)
                            ? json_decode($record->dosen_pengampu_ids, true)
                            : $record->dosen_pengampu_ids;

                        if (empty($ids)) return '-';

                        $dosens = \App\Models\DosenPengampu::with('dosen.person')->whereIn('id', $ids)->get();

                        $list = $dosens->map(function ($dp) {
                            $nama = $dp->dosen->person->nama_lengkap ?? 'Tanpa Nama';
                            $badge = $dp->is_koordinator ? ' <span style="color: #eab308; font-weight: bold; font-size: 10px;">(Koord)</span>' : '';
                            return "<div style='margin-bottom: 2px;'>&bull; {$nama}{$badge}</div>";
                        })->implode('');

                        return new HtmlString("<div style='font-size: 0.85rem; line-height: 1.4;'>{$list}</div>");
                    }),

                // 4. JADWAL, RUANGAN, & TIPE RUANG
                TextColumn::make('hari')
                    ->label('Alokasi Jadwal & Ruang')
                    ->formatStateUsing(function ($record) {
                        if (!$record->is_success) {
                            return new HtmlString('<span style="color: #ef4444; font-weight: bold;">❌ Belum Dapat Slot</span>');
                        }

                        $mulai = $record->jam_mulai ? Carbon::parse($record->jam_mulai)->format('H:i') : '--:--';
                        $selesai = $record->jam_selesai ? Carbon::parse($record->jam_selesai)->format('H:i') : '--:--';
                        $ruang = $record->ruang->nama_ruang ?? '?';
                        $jenisRuang = $record->ruang->jenis_ruang ?? 'TEORI';

                        // Beri label kecil berwarna ungu jika ruangannya adalah LAB
                        $jenisBadge = $jenisRuang === 'LABORATORIUM'
                            ? '<span style="color: #a855f7; font-size: 10px; font-weight: bold;">[LAB]</span>'
                            : '';

                        return new HtmlString(
                            "<span style='color: #16a34a; font-weight: bold;'>{$record->hari}</span>, {$mulai}-{$selesai}<br>" .
                                "R. {$ruang} {$jenisBadge}"
                        );
                    }),

                // 5. KETERANGAN / ANALISIS
                TextColumn::make('failure_reason')
                    ->label('Analisis Mesin')
                    ->color(fn($record) => $record->is_success ? 'success' : 'danger')
                    ->wrap()
                    ->formatStateUsing(fn($record) => $record->is_success ? '✅ Sempurna' : $record->failure_reason)
                    ->copyable(fn($record) => !$record->is_success)
                    ->copyMessage('Analisis disalin!'),

                // 6. SKOR KUALITAS (BARU -- dari CandidateScorer + LocalSearchOptimizer,
                // sebelumnya dihitung dan disimpan ke optimization_score tapi tidak
                // pernah ditampilkan di UI mana pun)
                TextColumn::make('optimization_score')
                    ->label('Skor')
                    ->badge()
                    ->alignCenter()
                    ->visible(fn($record) => $record?->is_success)
                    ->color(fn(?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn(?int $state) => $state !== null ? $state : '-')
                    ->tooltip('Seberapa baik slot ini dibanding kandidat lain yang tersedia (0-100, makin tinggi makin merata bebannya).'),
            ])
            ->filters([
                TernaryFilter::make('is_success')
                    ->label('Status Plotting')
                    ->placeholder('Semua Jadwal')
                    ->trueLabel('Hanya yang Berhasil')
                    ->falseLabel('Hanya yang Gagal (Butuh Perbaikan)'),
            ])
            ->headerActions([])

            // --- PENGUNCI KEAMANAN TABEL DRAF ---
            ->recordUrl(null) // Mematikan link klik pada baris
            ->recordActions([
                EditAction::make()
                    ->label('Edit Manual')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->button() // Ubah jadi tombol terlihat jelas (bukan icon kecil)
                    ->modalHeading('Intervensi Manual Jadwal')
                    ->modalDescription(fn($record) => new HtmlString('Ubah hari, jam, atau ruangan secara manual untuk MK <b>' . ($record->mataKuliah->nama_mk ?? '') . '</b>.<br><span style="color:red;">⚠️ Peringatan: Mesin tidak mengecek bentrok untuk perubahan manual. Pastikan Anda yakin ruangan ini kosong!</span>'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('hari')
                                ->label('Hari')
                                ->options([
                                    'Senin' => 'Senin',
                                    'Selasa' => 'Selasa',
                                    'Rabu' => 'Rabu',
                                    'Kamis' => 'Kamis',
                                    'Jumat' => 'Jumat',
                                    'Sabtu' => 'Sabtu'
                                ])
                                ->required(),

                            Select::make('ruang_id')
                                ->label('Pilih Ruangan')
                                ->relationship('ruang', 'nama_ruang')
                                ->preload()
                                ->searchable()
                                ->required(),

                            TimePicker::make('jam_mulai')
                                ->label('Jam Mulai')
                                ->seconds(false)
                                ->required(),

                            TimePicker::make('jam_selesai')
                                ->label('Jam Selesai')
                                ->seconds(false)
                                ->required(),
                        ])
                    ])
                    ->using(function (\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model {
                        // Timpa data dengan inputan manual
                        $record->update([
                            'hari' => $data['hari'],
                            'jam_mulai' => $data['jam_mulai'],
                            'jam_selesai' => $data['jam_selesai'],
                            'ruang_id' => $data['ruang_id'],
                            // PENTING: Jika sebelumnya gagal, ubah statusnya jadi SUKSES!
                            'is_success' => true,
                            'failure_reason' => '🛠️ Diintervensi Manual oleh Operator',
                            // Skor dikosongkan karena ini bukan hasil mesin lagi
                            'optimization_score' => null,
                        ]);

                        return $record;
                    })
                    ->successNotificationTitle('Jadwal berhasil diubah manual!'),
            ])     // Menghilangkan tombol Lihat/Edit/Delete bawaan
            ->toolbarActions([]); // Mematikan fitur hapus massal (checkbox)
    }
}
