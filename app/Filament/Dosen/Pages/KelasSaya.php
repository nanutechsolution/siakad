<?php

declare(strict_types=1);

namespace App\Filament\Dosen\Pages;

use App\Models\JadwalKuliah;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class KelasSaya extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Kelas Saya';
    protected static ?string $navigationLabel = 'Kelas Saya';
    protected string $view = 'filament.dosen.pages.kelas-saya';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $slug = 'kelas-saya';

    public function table(Table $table): Table
    {
        $dosenId = Auth::user()?->person?->trxDosen?->id;

        return $table
            ->query(
                JadwalKuliah::query()
                    // Pastikan merelasikan 'ruang' jika ada tabelnya, atau sesuaikan kodenya
                    ->with(['mataKuliah', 'kelas', 'tahunAkademik'])
                    ->whereHas('tahunAkademik', fn(Builder $q) => $q->where('is_active', true))
                    ->whereHas('dosenPengajar', function (Builder $q) use ($dosenId) {
                        $q->where('dosen_id', $dosenId)->where('is_penilai', true);
                    })
            )
            ->columns([
                TextColumn::make('mataKuliah.kode_mk')
                    ->label('Kode')
                    ->sortable()
                    ->copyable() // Dosen bisa copy kode MK sekali klik
                    ->description(fn (JadwalKuliah $record) => "Kelas: {$record->kelas?->nama_kelas}"),
                
                TextColumn::make('mataKuliah.nama_mk')
                    ->label('Mata Kuliah')
                    ->searchable()
                    ->sortable()
                    ->wrap(), // Membungkus teks panjang agar tidak kepotong kiri-kanan
                
                // Menggabungkan Hari, Jam, dan Ruang Kuliah agar ringkas tapi kaya info
                TextColumn::make('jadwal_info')
                    ->label('Jadwal & Ruang')
                    ->getStateUsing(function (JadwalKuliah $record) {
                        // Sesuaikan properti 'hari', 'jam_mulai', 'jam_selesai', 'ruang' dengan model Anda
                        $hari = $record->hari ?? '-';
                        $jam = $record->jam_mulai ? "{$record->jam_mulai} - {$record->jam_selesai}" : '';
                        $ruang = $record->ruang?->nama_ruang ?? $record->ruang_id ?? '';
                        
                        return "{$hari} ({$jam})" . ($ruang ? " | R. {$ruang}" : '');
                    })
                    ->color('gray'),

                TextColumn::make('krs_details_count')
                    ->label('Peserta')
                    ->counts('krsDetails') 
                    ->badge()
                    ->color('info'),

                // Menunjukkan progress pengisian: Nilai terisi vs Total mahasiswa
                TextColumn::make('progress_nilai')
                    ->label('Progress Nilai')
                    ->getStateUsing(function (JadwalKuliah $record) {
                        $total = $record->krsDetails()->count();
                        $published = $record->krsDetails()->where('is_published', true)->count();
                        
                        return "{$published} / {$total} Mhs";
                    })
                    ->badge()
                    ->color(function (JadwalKuliah $record) {
                        $total = $record->krsDetails()->count();
                        $published = $record->krsDetails()->where('is_published', true)->count();
                        
                        if ($total === 0) return 'gray';
                        return $published === $total ? 'success' : 'warning';
                    }),
                
                TextColumn::make('status_periode')
                    ->badge()
                    ->label('Status Input')
                    ->getStateUsing(fn(JadwalKuliah $record) => $record->tahunAkademik?->inputNilaiStatusLabel() ?? 'Tidak diketahui')
                    ->colors([
                        'success' => 'Terbuka',
                        'danger' => 'Terkunci (manual)',
                        'warning' => fn($state) => in_array($state, ['Belum dibuka', 'Sudah ditutup']),
                    ]),
            ])
            ->recordActions([
                Action::make('input_nilai')
                    ->label('Input Nilai')
                    ->icon('heroicon-o-pencil-square')
                    // Tombol berubah warna hijau jika sudah diisi semua, atau kuning jika belum lengkap
                    ->color(function (JadwalKuliah $record) {
                        $total = $record->krsDetails()->count();
                        $published = $record->krsDetails()->where('is_published', true)->count();
                        return ($total > 0 && $published === $total) ? 'gray' : 'primary';
                    })
                    ->url(fn(JadwalKuliah $record) => InputNilaiKelas::getUrl(['record' => $record->getKey()])),
            ])
            ->emptyStateHeading('Belum ada kelas yang perlu dinilai')
            ->emptyStateDescription('Anda belum terdaftar sebagai penilai di kelas manapun pada semester aktif.');
    }
}