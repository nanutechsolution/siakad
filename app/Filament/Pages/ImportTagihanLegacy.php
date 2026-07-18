<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\KeuanganKomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString; // Tambahkan ini untuk merender HTML
use UnitEnum;
use Illuminate\Support\Str;

class ImportTagihanLegacy extends Page implements HasForms
{
    use InteractsWithForms, HasPageShield;

    protected string $view = 'filament.pages.import-tagihan-legacy';
    protected static string|UnitEnum|NULL $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected static ?string $navigationLabel = 'Import Tagihan Lama';
    protected static ?string $title = 'Import Tagihan (Pra-SIAKAD)';
    protected static ?int $navigationSort = 9;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload File CSV Tunggakan')
                    ->description('Pastikan format file sesuai dengan template. File harus berformat .csv.')
                    ->schema([
                        FileUpload::make('file_csv')
                            ->label('File CSV')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                            ->disk('local')
                            ->directory('imports')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_template')
                ->label('Download Template CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $headers = [
                        'Content-type'        => 'text/csv',
                        'Content-Disposition' => 'attachment; filename=template_import_tagihan.csv',
                        'Pragma'              => 'no-cache',
                        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                        'Expires'             => '0'
                    ];

                    $columns = ['nim', 'kode_tahun_akademik', 'kode_komponen_biaya', 'nominal_tagihan', 'nominal_terbayar', 'deskripsi'];

                    $callback = function () use ($columns) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $columns);
                        fputcsv($file, ['20230001', '20231', 'SPP', '2500000', '1000000', 'Tunggakan SPP Ganjil 2023']);
                        fclose($file);
                    };

                    return response()->stream($callback, 200, $headers);
                }),
        ];
    }

    public function processImport(): void
    {
        $data = $this->form->getState();
        $filePath = Storage::disk('local')->path($data['file_csv']);

        if (!file_exists($filePath)) {
            Notification::make()->danger()->title('Gagal')->body('File tidak ditemukan.')->send();
            return;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        // Validasi Header
        $expectedHeaders = ['nim', 'kode_tahun_akademik', 'kode_komponen_biaya', 'nominal_tagihan', 'nominal_terbayar', 'deskripsi'];
        if ($header !== $expectedHeaders) {
            Notification::make()->danger()->title('Format File Salah')->body('Header CSV tidak sesuai dengan template.')->send();
            fclose($file);
            return;
        }

        $berhasil = 0;
        $gagal = 0;
        $errors = [];
        $baris = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                $baris++;

                if (empty(array_filter($row))) continue;

                $nim = $row[0] ?? null;
                $kodeTahun = $row[1] ?? null;
                $kodeKomponen = $row[2] ?? null;
                $nominalTagihan = (float) ($row[3] ?? 0);
                $nominalTerbayar = (float) ($row[4] ?? 0);
                $deskripsi = $row[5] ?? 'Import Tagihan Lama';

                // 1. Validasi Master Data
                $mahasiswa = Mahasiswa::where('nim', $nim)->first();
                $ta = RefTahunAkademik::where('kode_tahun', $kodeTahun)->first();
                $komponen = KeuanganKomponenBiaya::where('kode_komponen', $kodeKomponen)->first();

                if (!$mahasiswa || !$ta || !$komponen) {
                    $gagal++;
                    $alasan = [];
                    if (!$mahasiswa) $alasan[] = "NIM '$nim' tidak ditemukan";
                    if (!$ta) $alasan[] = "TA '$kodeTahun' tidak ditemukan";
                    if (!$komponen) $alasan[] = "Komponen '$kodeKomponen' tidak ditemukan";

                    $errors[] = "Baris {$baris}: " . implode(', ', $alasan);
                    continue;
                }

                // 2. Cari atau Buat Header Tagihan
                $tagihan = DB::table('tagihan_mahasiswas')
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('tahun_akademik_id', $ta->id)
                    ->whereNull('deleted_at')
                    ->first();

                $tagihanId = $tagihan ? $tagihan->id : Str::uuid()->toString();

                if (!$tagihan) {
                    DB::table('tagihan_mahasiswas')->insert([
                        'id' => $tagihanId,
                        'mahasiswa_id' => $mahasiswa->id,
                        'tahun_akademik_id' => $ta->id,
                        'kode_transaksi' => 'INV-LEGACY-' . $nim . '-' . $ta->kode_tahun,
                        'deskripsi' => 'Tagihan Migrasi Pra-SIAKAD',
                        'total_tagihan' => $nominalTagihan,
                        'total_bayar' => $nominalTerbayar,
                        'status_bayar' => $this->hitungStatusBayar($nominalTagihan, $nominalTerbayar),
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $newTotalTagihan = $tagihan->total_tagihan + $nominalTagihan;
                    $newTotalBayar = $tagihan->total_bayar + $nominalTerbayar;

                    DB::table('tagihan_mahasiswas')->where('id', $tagihanId)->update([
                        'total_tagihan' => $newTotalTagihan,
                        'total_bayar' => $newTotalBayar,
                        'status_bayar' => $this->hitungStatusBayar($newTotalTagihan, $newTotalBayar),
                        'updated_at' => now(),
                    ]);
                }

                // 3. Insert Detail Tagihan
                DB::table('tagihan_mahasiswas_details')
                    ->where('tagihan_id', $tagihanId)
                    ->where('komponen_biaya_id', $komponen->id)
                    ->delete();

                DB::table('tagihan_mahasiswas_details')->insert([
                    'tagihan_id' => $tagihanId,
                    'komponen_biaya_id' => $komponen->id,
                    'nama_komponen_snapshot' => $komponen->nama_komponen,
                    'nominal_dasar' => $nominalTagihan,
                    'nominal_diskon' => 0,
                    'nominal_terbayar' => $nominalTerbayar,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Catat ke General Ledger
                DB::table('keuangan_general_ledgers')->insert([
                    'id' => Str::uuid()->toString(),
                    'mahasiswa_id' => $mahasiswa->id,
                    'referensi_dokumen' => 'INV-LEGACY-' . $nim . '-' . $ta->kode_tahun,
                    'tipe_transaksi' => 'TAGIHAN',
                    'debit' => $nominalTagihan,
                    'kredit' => $nominalTerbayar,
                    'saldo_berjalan' => $nominalTagihan - $nominalTerbayar,
                    'keterangan' => $deskripsi,
                    'created_at' => now(),
                ]);

                $berhasil++;
            }

            DB::commit();

            $pesanBody = "<strong>Berhasil:</strong> {$berhasil} baris.<br><strong>Gagal:</strong> {$gagal} baris.";

            if ($gagal > 0 && !empty($errors)) {
                $pesanBody .= "<br><br><strong>Detail Error:</strong><ul style='margin-left: 1.5rem; list-style-type: disc; margin-top: 0.5rem;'>";

                $tampilkanError = array_slice($errors, 0, 10);
                foreach ($tampilkanError as $err) {
                    $pesanBody .= "<li>{$err}</li>";
                }

                if (count($errors) > 10) {
                    $sisa = count($errors) - 10;
                    $pesanBody .= "<li><em>...dan {$sisa} baris error lainnya.</em></li>";
                }
                $pesanBody .= "</ul>";

                Notification::make()
                    ->warning()
                    ->title('Import Selesai dengan Catatan')
                    ->body(new HtmlString($pesanBody)) // Menggunakan HtmlString
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title('Import Sukses Sepenuhnya')
                    ->body(new HtmlString($pesanBody)) // Menggunakan HtmlString
                    ->send();
            }

            $this->form->fill();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Import Gagal Total')
                ->body('Terjadi kesalahan sistem: ' . $e->getMessage())
                ->persistent()
                ->send();
        } finally {
            fclose($file);
        }
    }

    private function hitungStatusBayar(float $tagihan, float $bayar): string
    {
        if ($bayar >= $tagihan) return 'LUNAS';
        if ($bayar > 0) return 'CICIL';
        return 'BELUM';
    }
}
