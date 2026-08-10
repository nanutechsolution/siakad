<?php

namespace App\Filament\Clusters\ManajemenKelas\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Filament\Clusters\ManajemenKelas\ManajemenKelasCluster;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

use App\Models\Mahasiswa;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\RefProgram;
use App\Services\Kelas\ManajemenKelasService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class GenerateKelasPage extends Page implements HasForms
{
    use InteractsWithForms;

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

    public int $jumlahMahasiswaTanpaKelas = 0;

    public function mount(): void
    {
        $this->form->fill([
            'pola_nama' => 'Kelas %s',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->live()
                    ->required(),
                Select::make('program_id')
                    ->label('Program')
                    ->options(fn() => RefProgram::query()->where('is_active', true)->orderBy('nama_program')->pluck('nama_program', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                    ->searchable()
                    ->live()
                    ->required(),

                Placeholder::make('info_jumlah_mahasiswa')
                    ->label('')
                    ->visible(fn($get) => $get('prodi_id') && $get('angkatan_id'))
                    ->content(function ($get) {
                        $jumlah = Mahasiswa::query()
                            ->where('prodi_id', $get('prodi_id'))
                            ->where('angkatan_id', $get('angkatan_id'))
                            ->whereNull('deleted_at')
                            ->whereDoesntHave('mahasiswaKelas', fn($q) => $q->whereNull('tanggal_keluar'))
                            ->count();

                        return new HtmlString(
                            '<div class="rounded-lg bg-primary-50 dark:bg-primary-500/10 p-3 text-sm text-primary-700 dark:text-primary-400">
                                💡 Ada <strong>' . $jumlah . '</strong> mahasiswa pada kombinasi ini yang belum punya kelas aktif — merekalah yang akan dibagi ke kelas-kelas baru.
                            </div>'
                        );
                    }),

                TextInput::make('jumlah_kelas')
                    ->label('Jumlah Kelas yang Dibuat')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(26)
                    ->required()
                    ->helperText('Maksimal 26 kelas sekali generate (penamaan otomatis A-Z).'),
                TextInput::make('kapasitas_per_kelas')
                    ->label('Kapasitas per Kelas')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Kosongkan kalau tidak ingin membatasi kapasitas.'),
                TextInput::make('pola_nama')
                    ->label('Pola Nama Kelas')
                    ->required()
                    ->helperText('Gunakan %s sebagai placeholder huruf kelas — contoh "Kelas %s" jadi "Kelas A", "Kelas B", dst.'),
            ])
            ->statePath('data');
    }

    public function hitungPreview(): void
    {
        $data = $this->form->getState();

        $this->jumlahMahasiswaTanpaKelas = Mahasiswa::query()
            ->where('prodi_id', $data['prodi_id'])
            ->where('angkatan_id', $data['angkatan_id'])
            ->whereNull('deleted_at')
            ->whereDoesntHave('mahasiswaKelas', fn($q) => $q->whereNull('tanggal_keluar'))
            ->count();

        $this->previewGenerated = true;

        if ($this->jumlahMahasiswaTanpaKelas === 0) {
            Notification::make()
                ->title('Tidak ada mahasiswa yang perlu ditempatkan')
                ->body('Semua mahasiswa pada kombinasi ini sudah punya kelas aktif.')
                ->info()
                ->send();
        }
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $hasil = app(ManajemenKelasService::class)->generateKelasOtomatis(
            prodiId: (int) $data['prodi_id'],
            programId: (int) $data['program_id'],
            angkatanId: $data['angkatan_id'],
            jumlahKelas: (int) $data['jumlah_kelas'],
            kapasitasPerKelas: $data['kapasitas_per_kelas'] ? (int) $data['kapasitas_per_kelas'] : null,
            polaNama: $data['pola_nama'],
        );

        Notification::make()
            ->title('Generate kelas selesai')
            ->body("{$hasil['kelas']->count()} kelas baru dibuat, {$hasil['ditempatkan']} mahasiswa berhasil ditempatkan.")
            ->success()
            ->persistent()
            ->send();

        $this->form->fill([
            'prodi_id' => $data['prodi_id'],
            'program_id' => $data['program_id'],
            'pola_nama' => 'Kelas %s',
        ]);
        $this->previewGenerated = false;
        $this->jumlahMahasiswaTanpaKelas = 0;
    }
}
