<?php

namespace App\Filament\Resources\Kelas\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Filament\Resources\Kelas\KelasResource;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Services\MahasiswaPlottingService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlottingMahasiswa extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string $resource = KelasResource::class;
    protected static ?string $title = 'Plotting Mahasiswa';
    protected static ?string $navigationLabel = 'Plotting Mahasiswa';
    protected string $view = 'filament.resources.kelas.pages.plotting-mahasiswa';
    public ?int $tahunAkademikId = null;
    public ?int $prodiId = null;
    public ?int $programId = null;
    public ?int $angkatanId = null;

    public string $prodiLabel = '-';
    public string $tahunAkademikLabel = '-';



    public function mount(): void
    {
        $this->tahunAkademikId = request()->integer('tahun_akademik_id') ?: null;
        $this->prodiId = request()->integer('prodi_id') ?: null;
        $this->programId = request()->integer('program_id') ?: null;
        $this->angkatanId = request()->integer('angkatan_id') ?: null;

        // Program & Angkatan wajib ada supaya query mahasiswa selalu ter-scope
        // (tidak pernah menampilkan seluruh mahasiswa se-kampus).
        abort_unless(
            $this->programId && $this->angkatanId,
            400,
            'Konteks Program dan Angkatan wajib dipilih sebelum membuka halaman plotting.'
        );

        $this->prodiLabel = $this->prodiId
            ? (RefProdi::find($this->prodiId)?->nama_prodi ?? '-')
            : 'Semua Prodi';

        // Sesuaikan nama tabel/model referensi tahun akademik dengan skema Anda.
        $this->tahunAkademikLabel = $this->tahunAkademikId
            ? (DB::table('ref_tahun_akademik')->where('id', $this->tahunAkademikId)->value('nama') ?? '-')
            : '-';
    }

    /**
     * Dipanggil dari view untuk header stat "Belum memiliki kelas: N mahasiswa".
     * Dihitung ulang setiap render (bukan disimpan di properti), supaya angka
     * selalu akurat setelah bulk action selesai memindahkan mahasiswa.
     */
    public function getJumlahBelumBerkelas(): int
    {
        return Mahasiswa::query()
            ->belumBerkelas()
            ->where('program_id', $this->programId)
            ->where('angkatan_id', $this->angkatanId)
            ->when($this->prodiId, fn($q) => $q->where('prodi_id', $this->prodiId))
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Mahasiswa::query()
                    // Scope query utama: mahasiswa tanpa kelas aktif, dalam program+angkatan
                    // yang sama. Filter Prodi/Angkatan di bawah bisa mempersempit lebih lanjut.
                    ->belumBerkelas()
                    ->where('program_id', $this->programId)
                    ->where('angkatan_id', $this->angkatanId)
                    ->with(['person', 'prodi'])
            )
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Prodi')
                    ->sortable(),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->default($this->prodiId),

                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => DB::table('ref_angkatan')->pluck('id_tahun', 'id_tahun')->toArray())
                    ->default($this->angkatanId),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    $this->masukkanKeKelasBulkAction(),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    protected function masukkanKeKelasBulkAction(): BulkAction
    {
        return BulkAction::make('assign_ke_kelas')
            ->label('Masukkan ke Kelas')
            ->icon('heroicon-o-arrow-right-circle')
            ->schema([
                Select::make('kelas_id')
                    ->label('Kelas Tujuan')
                    ->searchable()
                    ->required()
                    ->options(fn() => $this->opsiKelasTujuan()),

                DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->default(now())
                    ->required(),
            ])
            ->action(function (Collection $records, array $data, MahasiswaPlottingService $service) {
                $sukses = 0;
                $gagalDetail = [];

                foreach ($records as $mahasiswa) {
                    try {
                        // Semua validasi (kapasitas, mahasiswa sudah berkelas, dst)
                        // dilakukan di dalam service ini — bukan di sini.
                        $service->plot($mahasiswa->id, $data['kelas_id'], $data['tanggal_masuk']);
                        $sukses++;
                    } catch (\Throwable $e) {
                        Log::error('Plotting Mahasiswa Error: ' . $e->getMessage());
                        $gagalDetail[] = "{$mahasiswa->nim}: {$e->getMessage()}";
                    }
                }

                $gagal = count($gagalDetail);

                Notification::make()
                    ->title($gagal === 0 ? 'Berhasil' : 'Selesai dengan Catatan')
                    ->body(
                        "Sukses: {$sukses}, Gagal: {$gagal}."
                            . ($gagal > 0 ? '<br>' . implode('<br>', $gagalDetail) : '')
                    )
                    ->status($gagal === 0 ? 'success' : 'warning')
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Opsi kelas tujuan untuk modal bulk action. Hanya menampilkan kelas dalam
     * konteks yang sama, dengan label sisa kapasitas — jumlah kelas per konteks
     * kecil (puluhan), jadi aman dipakai options() statis (bukan mahasiswa yang
     * jumlahnya ribuan).
     */
    protected function opsiKelasTujuan(): array
    {
        return Kelas::query()
            ->when($this->prodiId, fn($q) => $q->where('prodi_id', $this->prodiId))
            ->where('program_id', $this->programId)
            ->where('angkatan_id', $this->angkatanId)
            ->withCount('mahasiswaKelasAktif')
            ->get()
            ->mapWithKeys(function (Kelas $kelas) {
                $sisa = $kelas->kapasitas - $kelas->mahasiswa_kelas_aktif_count;

                $label = $sisa > 0
                    ? "{$kelas->nama_kelas} — {$kelas->mahasiswa_kelas_aktif_count}/{$kelas->kapasitas} tersedia {$sisa}"
                    : "{$kelas->nama_kelas} — {$kelas->mahasiswa_kelas_aktif_count}/{$kelas->kapasitas} penuh";

                return [$kelas->id => $label];
            })
            ->toArray();
    }
}
