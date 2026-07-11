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
use UnitEnum;
Use Illuminate\Support\Str;

class ImportTagihanLegacy extends Page implements HasForms
{
    use InteractsWithForms, HasPageShield;
    protected string $view = 'filament.pages.import-tagihan-legacy';
    protected static string|UnitEnum|NULL $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected static ?string $navigationLabel = 'Import Tagihan Lama';
    protected static ?string $title = 'Import Tagihan (Pra-SIAKAD)';
    protected static ?int $navigationSort = 99;
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
                        // Contoh baris
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

                // Lewati baris kosong
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
                // Asumsi tabel komponen punya field 'nama_komponen' yang unik untuk identifikasi, atau sesuaikan jika ada kode spesifik
                $komponen = KeuanganKomponenBiaya::where('nama_komponen', $kodeKomponen)->first();

                if (!$mahasiswa || !$ta || !$komponen) {
                    $gagal++;
                    $errors[] = "Baris {$baris}: NIM/Tahun/Komponen tidak valid.";
                    continue;
                }

                // 2. Cari atau Buat Header Tagihan (Berdasarkan TA)
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
                    // Update header yang sudah ada
                    $newTotalTagihan = $tagihan->total_tagihan + $nominalTagihan;
                    $newTotalBayar = $tagihan->total_bayar + $nominalTerbayar;

                    DB::table('tagihan_mahasiswas')->where('id', $tagihanId)->update([
                        'total_tagihan' => $newTotalTagihan,
                        'total_bayar' => $newTotalBayar,
                        'status_bayar' => $this->hitungStatusBayar($newTotalTagihan, $newTotalBayar),
                        'updated_at' => now(),
                    ]);
                }

                // 3. Insert Detail Tagihan (Hapus duplikasi komponen jika sudah ada agar aman di-reimport)
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

                // 4. Catat ke General Ledger untuk Audit Keuangan (Debit = Hutang bertambah)
                DB::table('keuangan_general_ledgers')->insert([
                    'id' => Str::uuid()->toString(),
                    'mahasiswa_id' => $mahasiswa->id,
                    'referensi_dokumen' => 'INV-LEGACY-' . $nim . '-' . $ta->kode_tahun,
                    'tipe_transaksi' => 'TAGIHAN',
                    'debit' => $nominalTagihan,
                    'kredit' => $nominalTerbayar, // Langsung potong jika ada pembayaran
                    'saldo_berjalan' => $nominalTagihan - $nominalTerbayar,
                    'keterangan' => $deskripsi,
                    'created_at' => now(),
                ]);

                $berhasil++;
            }

            DB::commit();

            Notification::make()
                ->success()
                ->title('Import Selesai')
                ->body("Berhasil: {$berhasil} baris. Gagal: {$gagal} baris.")
                ->send();

            // Bersihkan form
            $this->form->fill();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->danger()->title('Import Gagal')->body('Terjadi kesalahan sistem: ' . $e->getMessage())->send();
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
