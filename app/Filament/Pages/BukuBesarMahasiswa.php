<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\KeuanganGeneralLedger;
use App\Services\BukuBesarService;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class BukuBesarMahasiswa extends Page implements HasSchemas, HasTable
{
    use HasPageShield;
    use InteractsWithSchemas;
    use InteractsWithFormActions;
    use InteractsWithTable;

    protected string $view = 'filament.pages.buku-besar-mahasiswa';
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?string $title = 'Buku Besar Mahasiswa';
    protected static ?int $navigationSort = 101;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;

    public ?array $filterData = [];
    public ?object $mahasiswaInfo = null;

    public function mount(): void
    {
        $this->filterData = [
            'mahasiswa_id' => null,
        ];

        $this->form->fill($this->filterData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pilih Mahasiswa')
                    ->description('Cari berdasarkan NIM atau Nama Lengkap')
                    ->schema([
                        Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return DB::table('mahasiswas as m')
                                    ->join('ref_person as rp', 'm.person_id', '=', 'rp.id')
                                    ->where('m.nim', 'like', "%{$search}%")
                                    ->orWhere('rp.nama_lengkap', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->select('m.id', 'm.nim', 'rp.nama_lengkap')
                                    ->get()
                                    ->mapWithKeys(fn($row) => [$row->id => "{$row->nim} - {$row->nama_lengkap}"])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $row = DB::table('mahasiswas as m')
                                    ->join('ref_person as rp', 'm.person_id', '=', 'rp.id')
                                    ->where('m.id', $value)
                                    ->select('m.nim', 'rp.nama_lengkap')
                                    ->first();
                                return $row ? "{$row->nim} - {$row->nama_lengkap}" : null;
                            })
                            ->required()
                            ->live() // Memicu update realtime
                            ->afterStateUpdated(function () {
                                $this->applyFilter();
                            }),
                    ]),
            ])
            ->statePath('filterData');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('filter')
                ->label('Tampilkan Buku Besar')
                ->color('primary')
                ->icon('heroicon-m-magnifying-glass')
                ->action('applyFilter'),
        ];
    }

    public function applyFilter(): void
    {
        $this->filterData = $this->form->getState();
        $this->mahasiswaInfo = app(BukuBesarService::class)->getMahasiswaInfo($this->filterData['mahasiswa_id'] ?? null);
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if (empty($this->filterData['mahasiswa_id'])) {
                    return KeuanganGeneralLedger::query()->where('id', 'TIDAK_ADA');
                }

                return KeuanganGeneralLedger::query()
                    ->where('mahasiswa_id', $this->filterData['mahasiswa_id'])
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
            })
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('referensi_dokumen')
                    ->label('No. Referensi')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('tipe_transaksi')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'TAGIHAN' => 'danger',
                        'PEMBAYARAN' => 'success',
                        'ADJUSTMENT' => 'warning',
                        'REFUND' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->wrap(),

                TextColumn::make('debit')
                    ->label('Debit (Tagihan)')
                    ->money('IDR', locale: 'id')
                    ->color('danger')
                    ->alignEnd(),

                TextColumn::make('kredit')
                    ->label('Kredit (Bayar)')
                    ->money('IDR', locale: 'id')
                    ->color('success')
                    ->alignEnd(),

                TextColumn::make('saldo_berjalan')
                    ->label('Saldo Akhir')
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->alignEnd(),
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-m-printer')
                    ->color('danger')
                    ->action('exportPdf')
                    ->visible(fn() => !empty($this->filterData['mahasiswa_id'])),
            ])
            ->emptyStateHeading('Pilih Mahasiswa')
            ->emptyStateDescription('Silakan cari dan pilih mahasiswa untuk melihat histori buku besarnya.')
            ->paginated([20, 50, 100]);
    }

    public function exportPdf()
    {
        $mahasiswaId = $this->filterData['mahasiswa_id'] ?? null;
        if (!$mahasiswaId) return;

        $service = app(BukuBesarService::class);
        $data = $service->getLedger($mahasiswaId);
        $info = $service->getMahasiswaInfo($mahasiswaId);

        $pdf = Pdf::loadView('pdf.buku-besar', [
            'data' => $data,
            'info' => $info,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            "Buku_Besar_{$info->nim}_" . now()->format('Ymd_His') . ".pdf"
        );
    }
}
