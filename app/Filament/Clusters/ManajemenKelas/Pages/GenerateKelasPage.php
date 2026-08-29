<?php

namespace App\Filament\Clusters\ManajemenKelas\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Filament\Clusters\ManajemenKelas\ManajemenKelasCluster;
use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Models\RefProgram;
use App\Services\Kelas\ManajemenKelasService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;

class GenerateKelasPage extends Page implements HasForms, HasActions
{
    use HasPageShield;
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $cluster = ManajemenKelasCluster::class;
    protected static ?string $navigationLabel = 'Generate Kelas Otomatis';
    protected static ?string $title = 'Generate Kelas Otomatis per Angkatan';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.clusters.manajemen-kelas.pages.generate-kelas-page';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }

    /** @var array<string, mixed> */
    public ?array $data = [];

    public bool $previewGenerated = false;

    /** @var array<string,string>  mahasiswa_id => label kelas ('A','B',...) */
    public array $distribusi = [];

    /** @var array<int,array{id:string,nama:string,nim:string}> */
    public array $daftarMahasiswa = [];

    /** @var array<string,string>  label => nama kelas hasil pola_nama */
    public array $labelKelas = [];

    public string $cariMahasiswa = '';

    public function mount(): void
    {
        $this->form->fill(['pola_nama' => 'Kelas %s']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()->live()->required()
                    ->afterStateUpdated(fn() => $this->resetPreview()),
                Select::make('program_id')
                    ->label('Program')
                    ->options(fn() => RefProgram::query()->where('is_active', true)->orderBy('nama_program')->pluck('nama_program', 'id'))
                    ->searchable()->required(),
                Select::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                    ->searchable()->live()->required()
                    ->afterStateUpdated(fn() => $this->resetPreview()),
                TextInput::make('jumlah_kelas')
                    ->label('Jumlah Kelas yang Dibuat')
                    ->numeric()->minValue(1)->maxValue(26)->required()->live(debounce: 400)
                    ->helperText('Maksimal 26 kelas sekali generate (penamaan otomatis A-Z).')
                    ->afterStateUpdated(fn() => $this->resetPreview()),
                TextInput::make('kapasitas_per_kelas')
                    ->label('Kapasitas per Kelas')
                    ->numeric()->minValue(1)->live(debounce: 400)
                    ->helperText('Kosongkan kalau tidak ingin membatasi kapasitas.')
                    ->afterStateUpdated(fn() => $this->resetPreview()),
                TextInput::make('pola_nama')
                    ->label('Pola Nama Kelas')
                    ->required()->live(debounce: 400)
                    ->helperText(fn($state) => 'Contoh hasil: ' . collect(['A', 'B', 'C'])
                        ->map(fn($l) => sprintf($state ?: 'Kelas %s', $l))
                        ->implode(', '))
                    ->afterStateUpdated(fn() => $this->resetPreview()),
            ])
            ->statePath('data');
    }
    public function pindahkanMahasiswa(string $mahasiswaId, string $labelTujuan): void
    {
        if (! array_key_exists($labelTujuan, $this->labelKelas)) {
            return;
        }

        $labelAsal = $this->distribusi[$mahasiswaId] ?? null;

        if ($labelAsal === $labelTujuan) {
            return; // drop ke kolom yang sama, tidak perlu apa-apa
        }

        $this->distribusi[$mahasiswaId] = $labelTujuan;

        // Feedback halus, tidak mengganggu — cukup untuk konfirmasi visual
        $nama = collect($this->daftarMahasiswa)->firstWhere('id', $mahasiswaId)['nama'] ?? 'Mahasiswa';
        $this->dispatch('mahasiswa-dipindah', nama: $nama, tujuan: $this->labelKelas[$labelTujuan]);
    }

    #[Computed]
    public function mahasiswaPerKolom(): array
    {
        $tersaring = collect($this->daftarMahasiswaTersaring)->keyBy('id');

        $kolom = [];
        foreach (array_keys($this->labelKelas) as $label) {
            $kolom[$label] = [];
        }

        foreach ($this->distribusi as $mahasiswaId => $label) {
            if ($tersaring->has($mahasiswaId) && isset($kolom[$label])) {
                $kolom[$label][] = $tersaring[$mahasiswaId];
            }
        }

        return $kolom;
    }
    public function resetPreview(): void
    {
        $this->previewGenerated = false;
        $this->distribusi = [];
        $this->daftarMahasiswa = [];
        $this->labelKelas = [];
    }

    public function hitungPreview(): void
    {
        $data = $this->form->getState();

        $mahasiswa = Mahasiswa::query()
            ->select(['mahasiswas.id', 'mahasiswas.nim', 'mahasiswas.person_id'])
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->addSelect('ref_person.nama_lengkap as nama')
            ->where('mahasiswas.prodi_id', $data['prodi_id'])
            ->where('mahasiswas.angkatan_id', $data['angkatan_id'])
            ->whereNull('mahasiswas.deleted_at')
            ->whereDoesntHave('mahasiswaKelas', fn($q) => $q->whereNull('tanggal_keluar'))
            ->orderBy('ref_person.nama_lengkap')
            ->get();

        if ($mahasiswa->isEmpty()) {
            Notification::make()
                ->title('Tidak ada mahasiswa yang perlu ditempatkan')
                ->body('Semua mahasiswa pada kombinasi ini sudah punya kelas aktif.')
                ->info()->send();
            $this->resetPreview();
            return;
        }

        $abjad = range('A', 'Z');
        $jumlahKelas = (int) $data['jumlah_kelas'];
        $labels = collect(range(0, $jumlahKelas - 1))->map(fn($i) => $abjad[$i] ?? (string) ($i + 1));

        $this->labelKelas = $labels->mapWithKeys(
            fn($l) => [$l => sprintf($data['pola_nama'], $l)]
        )->all();

        $this->daftarMahasiswa = $mahasiswa->map(fn($m) => [
            'id' => $m->id,
            'nama' => $m->nama,
            'nim' => $m->nim,
        ])->all();

        $this->distribusi = [];
        foreach ($mahasiswa->values() as $i => $m) {
            $this->distribusi[$m->id] = $labels[$i % $labels->count()];
        }

        $this->previewGenerated = true;
    }

    public function seimbangkanUlang(): void
    {
        if (empty($this->labelKelas)) {
            return;
        }
        $labels = array_values(array_keys($this->labelKelas));
        foreach (array_keys($this->distribusi) as $i => $mahasiswaId) {
            $this->distribusi[$mahasiswaId] = $labels[$i % count($labels)];
        }
        Notification::make()->title('Distribusi dikembalikan ke pembagian rata')->success()->send();
    }

    #[Computed]
    public function ringkasanKelas(): array
    {
        $kapasitas = $this->form->getRawState()['kapasitas_per_kelas'] ?? null;
        $counts = array_count_values($this->distribusi);

        return collect($this->labelKelas)->map(function ($nama, $label) use ($counts, $kapasitas) {
            $jumlah = $counts[$label] ?? 0;
            $status = 'success';
            if ($kapasitas) {
                $status = $jumlah > $kapasitas ? 'danger' : ($jumlah === (int) $kapasitas ? 'warning' : 'success');
            }
            return [
                'label' => $label,
                'nama' => $nama,
                'jumlah' => $jumlah,
                'kapasitas' => $kapasitas,
                'status' => $status,
            ];
        })->values()->all();
    }

    #[Computed]
    public function adaKelasPenuh(): bool
    {
        return collect($this->ringkasanKelas)->contains(fn($k) => $k['status'] === 'danger');
    }

    #[Computed]
    public function daftarMahasiswaTersaring(): array
    {
        if ($this->cariMahasiswa === '') {
            return $this->daftarMahasiswa;
        }
        $q = mb_strtolower($this->cariMahasiswa);
        return collect($this->daftarMahasiswa)
            ->filter(fn($m) => str_contains(mb_strtolower($m['nama']), $q) || str_contains(mb_strtolower($m['nim']), $q))
            ->values()->all();
    }

    public function generateAction(): Action
    {
        return Action::make('generate')
            ->label('Generate Kelas')
            ->icon('heroicon-o-bolt')
            ->color('primary')
            ->disabled(fn() => ! $this->previewGenerated || $this->adaKelasPenuh || empty($this->daftarMahasiswa))
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Generate Kelas')
            ->modalDescription(function () {
                $total = count($this->daftarMahasiswa);
                $jmlKelas = count($this->labelKelas);
                return "{$jmlKelas} kelas baru akan dibuat dan {$total} mahasiswa akan ditempatkan sesuai distribusi pada preview. Tindakan ini tidak bisa dibatalkan otomatis.";
            })
            ->modalSubmitActionLabel('Ya, Generate Sekarang')
            ->action(fn() => $this->generate());
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        // Susun ulang distribusi jadi label => [mahasiswaId,...]
        $distribusiPerKelas = [];
        foreach ($this->distribusi as $mahasiswaId => $label) {
            $distribusiPerKelas[$label][] = $mahasiswaId;
        }
        foreach (array_keys($this->labelKelas) as $label) {
            $distribusiPerKelas[$label] ??= [];
        }

        $hasil = app(ManajemenKelasService::class)->generateKelasOtomatis(
            prodiId: (int) $data['prodi_id'],
            programId: (int) $data['program_id'],
            angkatanId: $data['angkatan_id'],
            jumlahKelas: (int) $data['jumlah_kelas'],
            kapasitasPerKelas: $data['kapasitas_per_kelas'] ? (int) $data['kapasitas_per_kelas'] : null,
            polaNama: $data['pola_nama'],
            distribusiManual: $distribusiPerKelas,
        );

        Notification::make()
            ->title('Generate kelas selesai')
            ->body("{$hasil['kelas']->count()} kelas baru dibuat, {$hasil['ditempatkan']} mahasiswa berhasil ditempatkan.")
            ->success()->persistent()
            ->actions([
                Action::make('lihat')
                    ->label('Lihat Kelas')
                    ->url(route('filament.admin.resources.kelas.index')), // sesuaikan nama route
            ])
            ->send();

        $this->form->fill([
            'prodi_id' => $data['prodi_id'],
            'program_id' => $data['program_id'],
            'pola_nama' => 'Kelas %s',
        ]);
        $this->resetPreview();
    }
}
