<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\KeuanganKomponenBiaya;
use App\Models\Mahasiswa;
use App\Services\TagihanNonRegulerService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class GeneratorTagihanNonReguler extends Page implements HasSchemas
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;


    protected string $view = 'filament.pages.generator-tagihan-non-reguler';


    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;


    protected static ?string $navigationLabel = 'Generator Tagihan Non Reguler';


    protected static ?string $title = 'Generator Tagihan Non Reguler';


    protected static ?int $navigationSort = 4;


    /**
     * State form
     */
    public ?array $data = [];


    /**
     * Preview sebelum generate
     */
    public ?array $preview = null;



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
                        Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {

                                return Mahasiswa::query()
                                    ->join(
                                        'ref_person',
                                        'mahasiswas.person_id',
                                        '=',
                                        'ref_person.id'
                                    )
                                    ->where('mahasiswas.nim', 'like', "%{$search}%")
                                    ->orWhere(
                                        'ref_person.nama_lengkap',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->select(
                                        'mahasiswas.id',
                                        'mahasiswas.nim',
                                        'ref_person.nama_lengkap'
                                    )
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($mhs) => [
                                        $mhs->id =>
                                        "{$mhs->nim} - {$mhs->nama_lengkap}"
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {

                                $mhs = Mahasiswa::query()
                                    ->join(
                                        'ref_person',
                                        'mahasiswas.person_id',
                                        '=',
                                        'ref_person.id'
                                    )
                                    ->where('mahasiswas.id', $value)
                                    ->select(
                                        'mahasiswas.id',
                                        'mahasiswas.nim',
                                        'ref_person.nama_lengkap'
                                    )
                                    ->first();


                                return $mhs
                                    ? "{$mhs->nim} - {$mhs->nama_lengkap}"
                                    : null;
                            })
                            ->required(),



                        Textarea::make('deskripsi')
                            ->label('Deskripsi Tagihan')
                            ->placeholder(
                                'Contoh: Tagihan Sidang Skripsi Juli 2026'
                            )
                            ->required()
                            ->rows(3),



                        DatePicker::make('tenggat_waktu')
                            ->label('Tenggat Pembayaran')
                            ->native(false)
                            ->minDate(now()),



                        Repeater::make('items')
                            ->label('Komponen Biaya')

                            ->schema([

                                Select::make('komponen_biaya_id')
                                    ->label('Komponen')

                                    ->options(
                                        KeuanganKomponenBiaya::query()
                                            ->where('is_active', true)
                                            ->whereIn(
                                                'tipe_biaya',
                                                [
                                                    'SEKALI',
                                                    'INSIDENTAL'
                                                ]
                                            )
                                            ->orderBy('nama_komponen')
                                            ->pluck(
                                                'nama_komponen',
                                                'id'
                                            )
                                    )

                                    ->searchable()
                                    ->required()
                                    ->distinct(),



                                TextInput::make('nominal_dasar')
                                    ->label('Nominal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->minValue(0),



                                TextInput::make('nominal_diskon')
                                    ->label('Diskon')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->minValue(0),

                            ])

                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel(
                                'Tambah Komponen'
                            )
                            ->required(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }




    /**
     * Preview
     */
    public function hitungPreview(): void
    {

        $state = $this->form->getState();


        $items = collect(
            $state['items'] ?? []
        );


        $this->preview = [

            'deskripsi' =>
            $state['deskripsi'] ?? null,


            'tenggat_waktu' =>
            $state['tenggat_waktu'] ?? null,


            'items' =>
            $items->map(function ($item) {


                $komponen =
                    KeuanganKomponenBiaya::find(
                        $item['komponen_biaya_id']
                    );


                $nominalDasar =
                    (float)
                    ($item['nominal_dasar'] ?? 0);


                $nominalDiskon =
                    (float)
                    ($item['nominal_diskon'] ?? 0);



                return [

                    'nama_komponen' =>
                    $komponen?->nama_komponen
                        ?? '-',


                    'nominal_dasar' =>
                    $nominalDasar,


                    'nominal_diskon' =>
                    $nominalDiskon,


                    'nominal_tagihan' =>
                    $nominalDasar - $nominalDiskon,

                ];
            })->toArray(),


            'total_tagihan' =>
            $items->sum(
                fn($item) => ($item['nominal_dasar'] ?? 0)
                    -
                    ($item['nominal_diskon'] ?? 0)
            ),

        ];


        Notification::make()
            ->success()
            ->title('Preview berhasil dibuat')
            ->send();
    }




    /**
     * Action Filament
     */
    protected function getFormActions(): array
    {
        return [

            Action::make('preview')

                ->label('Preview Tagihan')

                ->color('gray')

                ->action(
                    fn() =>
                    $this->hitungPreview()
                ),



            Action::make('generate')

                ->label('Generate Tagihan')

                ->color('primary')

                ->requiresConfirmation()

                ->modalHeading(
                    'Generate Tagihan Non Reguler'
                )

                ->modalDescription(
                    'Tagihan akan dibuat dan diberikan kepada mahasiswa.'
                )

                ->visible(
                    fn() =>
                    filled($this->preview)
                )

                ->action(
                    fn(TagihanNonRegulerService $service) =>
                    $this->generate($service)
                ),

        ];
    }





    /**
     * Simpan tagihan
     */
    public function generate(
        TagihanNonRegulerService $service
    ): void {


        $state =
            $this->form->getState();



        $tagihan =
            $service->generate([

                'mahasiswa_id' =>
                $state['mahasiswa_id'],


                'deskripsi' =>
                $state['deskripsi'],


                'tenggat_waktu' =>
                $state['tenggat_waktu'] ?? null,


                'created_by' =>
                Auth::id(),


                'items' =>
                $state['items'],

            ]);



        Notification::make()

            ->success()

            ->title(
                'Tagihan berhasil dibuat'
            )

            ->body(
                "Kode transaksi: {$tagihan->kode_transaksi}"
            )

            ->send();



        $this->form->fill();


        $this->preview = null;
    }
}
