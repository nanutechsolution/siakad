<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Services\TagihanService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use UnitEnum;

class GeneratorTagihan extends Page implements HasSchemas
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;
    use RestrictsFileUploadsToSchemaComponents;

    protected static ?string $navigationLabel = 'Generator Tagihan Reguler';
    protected static ?string $title = 'Generator Tagihan Mahasiswa';
    protected static ?int $navigationSort = 5;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected string $view = 'filament.pages.generator-tagihan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Parameter Generate Tagihan')
                    ->description('Pilih kriteria mahasiswa yang akan dibuatkan tagihan.')
                    ->schema([
                        Radio::make('tipe_target')
                            ->label('Target Generate')
                            ->options([
                                'kolektif' => 'Kolektif (Berdasarkan Prodi/Angkatan)',
                                'spesifik' => 'Mahasiswa Spesifik',
                            ])
                            ->default('kolektif')
                            ->live(),

                        Select::make('tahun_akademik_id')
                            ->label('Tahun Akademik')
                            ->options(RefTahunAkademik::query()->pluck('nama_tahun', 'id'))
                            ->default(fn() => RefTahunAkademik::where('is_active', true)->value('id'))
                            ->searchable()
                            ->live()
                            ->required(),

                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->options(RefProdi::query()->pluck('nama_prodi', 'id'))
                            ->searchable()
                            ->visible(fn(Get $get) => $get('tipe_target') === 'kolektif')
                            ->live()
                            ->hint(function (Get $get) {
                                $prodiId = $get('prodi_id');
                                if (!$prodiId) return null;

                                $query = Mahasiswa::query()->where('prodi_id', $prodiId);
                                if ($angkatanId = $get('angkatan_id')) {
                                    $query->where('angkatan_id', $angkatanId);
                                }

                                return "Total: {$query->count()} Mahasiswa";
                            })
                            ->hintIcon('heroicon-m-users')
                            ->hintColor('info'),

                        Select::make('angkatan_id')
                            ->label('Angkatan')
                            ->options(Mahasiswa::select('angkatan_id')->distinct()->pluck('angkatan_id', 'angkatan_id'))
                            ->searchable()
                            ->visible(fn(Get $get) => $get('tipe_target') === 'kolektif')
                            ->live()
                            ->hint(function (Get $get) {
                                $angkatanId = $get('angkatan_id');
                                if (!$angkatanId) return null;

                                $query = Mahasiswa::query()->where('angkatan_id', $angkatanId);
                                if ($prodiId = $get('prodi_id')) {
                                    $query->where('prodi_id', $prodiId);
                                }

                                return "Total: {$query->count()} Mahasiswa";
                            })
                            ->hintIcon('heroicon-m-users')
                            ->hintColor('info'),

                        Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->options(
                                Mahasiswa::query()
                                    ->join('ref_person', 'mahasiswas.person_id', '=', 'ref_person.id')
                                    ->select('mahasiswas.id', 'mahasiswas.nim', 'ref_person.nama_lengkap')
                                    ->get()
                                    ->mapWithKeys(fn($mhs) => [$mhs->id => "{$mhs->nim} - {$mhs->nama_lengkap}"])
                            )
                            ->searchable()
                            ->visible(fn(Get $get) => $get('tipe_target') === 'spesifik')
                            ->required(fn(Get $get) => $get('tipe_target') === 'spesifik'),

                        TextEntry::make('info_kalkulasi')
                            ->label('Informasi Target')
                            ->columnSpanFull()
                            ->state(function (Get $get) {
                                $tipeTarget = $get('tipe_target');
                                $tahunAkademikId = $get('tahun_akademik_id');
                                $prodiId = $get('prodi_id');
                                $angkatanId = $get('angkatan_id');
                                $mahasiswaId = $get('mahasiswa_id');

                                if (!$tahunAkademikId) {
                                    return new HtmlString('<span class="text-gray-500 italic">Pilih Tahun Akademik terlebih dahulu.</span>');
                                }

                                $query = Mahasiswa::query();

                                if ($tipeTarget === 'kolektif') {
                                    if ($prodiId) $query->where('prodi_id', $prodiId);
                                    if ($angkatanId) $query->where('angkatan_id', $angkatanId);
                                } else {
                                    if (!$mahasiswaId) {
                                        return new HtmlString('<span class="text-gray-500 italic">Pilih Mahasiswa terlebih dahulu.</span>');
                                    }
                                    $query->where('id', $mahasiswaId);
                                }

                                $totalMahasiswa = $query->count();

                                if ($totalMahasiswa === 0) {
                                    return new HtmlString('<div style="color: red; font-weight: bold;">Tidak ada mahasiswa yang cocok dengan kriteria filter.</div>');
                                }

                                $mahasiswaIds = $query->pluck('id');

                                $sudahDitagih = DB::table('tagihan_mahasiswas')
                                    ->whereIn('mahasiswa_id', $mahasiswaIds)
                                    ->where('tahun_akademik_id', $tahunAkademikId)
                                    ->count();

                                $siapDitagih = $totalMahasiswa - $sudahDitagih;

                                if ($siapDitagih === 0) {
                                    return new HtmlString("
                                        <div style='padding: 10px; background-color: rgba(234, 179, 8, 0.1); border: 1px solid rgb(234, 179, 8); border-radius: 8px; color: rgb(161, 98, 7);'>
                                            <strong>Peringatan:</strong> Semua mahasiswa (Total: {$totalMahasiswa}) <strong>sudah memiliki tagihan</strong> di semester ini. Tidak ada tagihan baru yang akan dibuat.
                                        </div>
                                    ");
                                }

                                return new HtmlString("
                                    <div style='padding: 10px; background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgb(59, 130, 246); border-radius: 8px; color: rgb(29, 78, 216);'>
                                        Ditemukan <strong>{$totalMahasiswa}</strong> mahasiswa.
                                        <strong>{$siapDitagih} mahasiswa siap di-generate tagihannya.</strong>
                                        <br><span style='font-size: 0.9em;'>({$sudahDitagih} mahasiswa dilewati karena sudah memiliki tagihan).</span>
                                    </div>
                                ");
                            }),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate_tagihan')
                ->formId('form-generator-tagihan')
                ->disabled(fn() => ! auth()->user()->can('GenerateTagihan'))
                ->label('Generate Tagihan Sekarang🚀')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Proses Generate Tagihan')
                ->modalDescription('Proses ini akan menghasilkan tagihan mahasiswa sesuai skema tarif yang berlaku. Pastikan data tahun akademik, angkatan, program studi, dan komponen biaya telah dikonfigurasi dengan benar. Setelah proses dijalankan, tagihan akan diterbitkan kepada mahasiswa yang memenuhi kriteria.')
                ->modalSubmitActionLabel('Lanjutkan')
                ->modalCancelActionLabel('Batal')
                ->modalSubmitAction(fn($action) => $action->color('success'))
                ->action(function (TagihanService $service) {
                    abort_unless(auth()->user()->can('GenerateTagihan'), 403);
                    $this->prosesGenerateTagihan($service);
                })
        ];
    }

    public function prosesGenerateTagihan(TagihanService $service): void
    {
        $data = $this->form->getState();
        $result = $service->generate($data);

        if ($result['status'] === 'success') {
            Notification::make()
                ->title('Proses Antrean Dimulai')
                ->body($result['message'])
                ->info()
                ->send();

            $this->form->fill();
        } else {
            Notification::make()
                ->title('Gagal Generate Tagihan')
                ->body('Sistem error: ' . $result['message'])
                ->danger()
                ->send();
        }
    }
}
