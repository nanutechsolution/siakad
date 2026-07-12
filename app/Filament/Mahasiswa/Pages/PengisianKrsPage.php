<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Akademik\KrsValidationService;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use UnitEnum;
use Illuminate\Support\Str;

class PengisianKrsPage extends Page implements HasForms
{
    protected string $view = 'filament.mahasiswa.pages.pengisian-krs-page';
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::AKADEMIK->value;
    protected static ?string $navigationLabel = 'Isi KRS';
    protected static ?string $title = 'Pengisian Kartu Rencana Studi (KRS)';
    protected static ?int $navigationSort = 1;
    public ?array $data = [];
    public bool $isEligible = true;
    public string $eligibilityMessage = '';

    public ?Mahasiswa $mahasiswa = null;
    public ?RefTahunAkademik $activeTa = null;
    public bool $hasExistingKrs = false;

    public function mount(): void
    {
        $this->mahasiswa = Mahasiswa::where('person_id', Auth::user()->person_id)->first();
        $this->activeTa = RefTahunAkademik::where('is_active', 1)->first();

        if (!$this->mahasiswa || !$this->activeTa) {
            $this->setIneligible('Data Mahasiswa atau Tahun Akademik aktif tidak ditemukan.');
            return;
        }

        // Cek apakah sudah pernah buat KRS di semester ini
        $this->hasExistingKrs = Krs::where('mahasiswa_id', $this->mahasiswa->id)
            ->where('tahun_akademik_id', $this->activeTa->id)
            ->exists();

        if ($this->hasExistingKrs) {
            $this->setIneligible('Anda sudah memiliki draft/pengajuan KRS untuk semester ini. Silakan cek menu Riwayat KRS.');
            return;
        }

        // Cek Periode Pengisian KRS
        $now = now();
        if ($now->lt($this->activeTa->tgl_mulai_krs) || $now->gt($this->activeTa->tgl_selesai_krs)) {
            $this->setIneligible('Saat ini BUKAN masa pengisian KRS. Jadwal KRS: ' .
                $this->activeTa->tgl_mulai_krs->format('d M Y') . ' s/d ' . $this->activeTa->tgl_selesai_krs->format('d M Y'));
            return;
        }

        // Jalankan GATE 1 (Status Akademik) & GATE 2 (Keuangan)
        $service = app(KrsValidationService::class);

        $valStatus = $service->checkStatusMahasiswa($this->mahasiswa, $this->activeTa);
        if (!$valStatus->passed) {
            $this->setIneligible($valStatus->message);
            return;
        }

        $valKeuangan = $service->checkKeuangan($this->mahasiswa, $this->activeTa, false);
        if (!$valKeuangan->passed) {
            $this->setIneligible($valKeuangan->message);
            return;
        }

        $this->form->fill();
    }

    private function setIneligible(string $message): void
    {
        $this->isEligible = false;
        $this->eligibilityMessage = $message;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pilih Mata Kuliah')
                    ->description('Centang mata kuliah yang ingin Anda ambil pada semester ini. Perhatikan batas maksimal SKS Anda.')
                    ->schema([
                        CheckboxList::make('jadwal_kuliah_ids')
                            ->label('')
                            ->options(function () {
                                if (!$this->mahasiswa || !$this->activeTa) return [];

                                return JadwalKuliah::with(['mataKuliah', 'dosenPengajars'])
                                    ->where('tahun_akademik_id', $this->activeTa->id)
                                    // Buka jadwal untuk prodi mahasiswa tersebut atau jadwal lintas prodi
                                    ->whereHas('kelas', function ($query) {
                                        $query->where('prodi_id', $this->mahasiswa->prodi_id);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($jadwal) {
                                        $sisaKuota = $jadwal->kuota_kelas - $jadwal->isi_kelas;

                                        // Ambil nama dari relasi, pastikan tidak error jika null
                                        $namaRuang = $jadwal->ruang->nama_ruang ?? 'Belum ditentukan';
                                        $namaDosen = $jadwal->dosenPengajars
                                            ->map(fn($dp) => $dp->person?->nama_dengan_gelar)
                                            ->filter()
                                            ->implode(', ') ?: '-';

                                        // Desain UI yang lebih rapi menggunakan struktur div dan Tailwind CSS
                                        $label = "
        <div class='flex flex-col py-1'>
            <div class='flex items-center gap-2'>
                <span class='font-bold text-gray-900 dark:text-white'>{$jadwal->mataKuliah->nama_mk}</span>
                <span class='px-2 py-0.5 text-xs font-semibold bg-primary-100 text-primary-700 rounded-md dark:bg-primary-900/50 dark:text-primary-400'>
                    {$jadwal->mataKuliah->sks_default} SKS
                </span>
            </div>
            <div class='text-sm text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1'>
                <span>📅 {$jadwal->hari}, {$jadwal->jam_mulai}-{$jadwal->jam_selesai}</span>
                <span>🚪 Ruang: {$namaRuang}</span>
                <span>👨‍🏫 Dosen: {$namaDosen}</span>
                <span class='" . ($sisaKuota <= 0 ? 'text-danger-500' : 'text-success-600 dark:text-success-400') . "'>
                    👥 Sisa Kuota: <strong>{$sisaKuota}</strong>
                </span>
            </div>
        </div>
    ";

                                        // Pastikan pakai HtmlString agar tag HTML-nya dirender oleh Filament
                                        return [$jadwal->id => new \Illuminate\Support\HtmlString($label)];
                                    });
                            })
                            ->columns(1)
                            ->required()
                            ->validationMessages([
                                'required' => 'Anda harus memilih minimal satu mata kuliah.',
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
    public function simpanKrs(): void
    {
        if (!$this->isEligible) return;

        $data = $this->form->getState();
        $jadwalIds = $data['jadwal_kuliah_ids'] ?? [];

        if (empty($jadwalIds)) {
            Notification::make()->warning()->title('Peringatan')->body('Pilih minimal satu kelas.')->send();
            return;
        }

        $service = app(KrsValidationService::class);

        // Kalkulasi Total SKS
        $totalSksDiambil = (int) DB::table('jadwal_kuliah')
            ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
            ->whereIn('jadwal_kuliah.id', $jadwalIds)
            ->sum('master_mata_kuliahs.sks_default');

        // GATE 3: SKS Maksimal
        $hasDispensasiSks = DB::table('dispensasi_akademiks')
            ->where('mahasiswa_id', $this->mahasiswa->id)
            ->where('jenis', 'KRS')
            ->where('status', 'AKTIF')
            ->where('berlaku_mulai', '<=', $this->activeTa->tgl_selesai_krs)
            ->where('berlaku_sampai', '>=', $this->activeTa->tgl_mulai_krs)
            ->exists();

        $valSks = $service->checkSksMaksimal($this->mahasiswa, $totalSksDiambil, $hasDispensasiSks);
        if (!$valSks->passed) {
            Notification::make()->danger()->title('Batas SKS Terlampaui')->body($valSks->message)->send();
            return;
        }

        // GATE 4: Bentrok Jadwal
        $valJadwal = $service->checkDuplikasiDanBentrok($jadwalIds);
        if (!$valJadwal->passed) {
            Notification::make()->danger()->title('Jadwal Bentrok')->body($valJadwal->message)->send();
            return;
        }

        // GATE 5: Kuota
        $valKuota = $service->checkKuotaKelas($jadwalIds);
        if (!$valKuota->passed) {
            Notification::make()->danger()->title('Kapasitas Kelas Penuh')->body($valKuota->message)->send();
            return;
        }

        // EKSEKUSI PENYIMPANAN
        DB::beginTransaction();
        try {
            $krsId = Str::uuid()->toString();

            // 1. Insert Header KRS (Tabel KRS menggunakan UUID di kolom id)
            DB::table('krs')->insert([
                'id'                => $krsId,
                'mahasiswa_id'      => $this->mahasiswa->id,
                'tahun_akademik_id' => $this->activeTa->id,
                'diajukan_at'       => now(),
                'status_krs'        => 'DIAJUKAN',
                'total_sks_diambil' => $totalSksDiambil,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // 2. Insert Details KRS (Tabel ini menggunakan AUTO INCREMENT, maka id TIDAK dikirimkan)
            $detailInserts = [];
            foreach ($jadwalIds as $jId) {
                $jadwal = JadwalKuliah::with('mataKuliah')->find($jId);

                $detailInserts[] = [
                    'krs_id'           => $krsId,
                    'jadwal_kuliah_id' => $jId,
                    'mata_kuliah_id'   => $jadwal->mata_kuliah_id,
                    'sks_snapshot'     => $jadwal->mataKuliah->sks_default,
                    'status_ambil'     => 'B',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            DB::table('krs_detail')->insert($detailInserts);
            // 3. Insert Log Status
            // NOTE: Bagian log saya 'comment' / amankan dulu agar tidak memblokir simpan KRS Anda.
            // Silakan buka '//' di bawah ini JIKA Anda sudah yakin mengetahui NAMA KOLOM yang benar di database.
            DB::table('krs_status_logs')->insert([
                'krs_id'         => $krsId,
                'aksi'           => 'DIAJUKAN',
                'dilakukan_oleh' => Auth::id(),
                'catatan'        => 'KRS diajukan secara mandiri oleh mahasiswa.',
                'created_at'     => now(),
            ]);

            DB::commit();

            Notification::make()->success()->title('Berhasil')->body('KRS Anda berhasil diajukan dan menunggu persetujuan Dosen Wali.')->send();

            // Trigger UI lock agar form menghilang
            $this->hasExistingKrs = true;
            $this->setIneligible('KRS Anda berhasil diajukan. Silakan pantau status persetujuan di menu Riwayat KRS.');
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->danger()->title('Gagal Menyimpan')->body('Terjadi kesalahan sistem: ' . $e->getMessage())->send();
        }
    }
}
