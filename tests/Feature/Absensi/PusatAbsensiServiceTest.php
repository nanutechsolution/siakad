<?php

declare(strict_types=1);

namespace Tests\Feature\Absensi;

use App\Exports\Absensi\AbsensiDocumentExport;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MahasiswaKelas;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use App\Services\Absensi\AbsensiDocumentService;
use App\Services\Pdf\PdfTemplateEngine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Test Pusat Absensi.
 *
 * WAJIB tanpa RefreshDatabase (phpunit.xml mengarah ke DB produksi).
 * Semua record dibuat oleh test ini sendiri dan dibersihkan di afterEach().
 */
class PusatAbsensiServiceTest extends TestCase
{
    private string $suffix;

    private int $fakultasId = 0;

    private int $prodiId = 0;

    private int $programId = 0;

    private int $angkatanId = 2099;

    private bool $angkatanBaruDibuat = false;

    private int $kampusId = 0;

    private int $kelasId = 0;

    private int $mataKuliahId = 0;

    private int $tahunAkademikId = 0;

    private string $mahasiswaId1 = '';

    private string $mahasiswaId2 = '';

    private string $nim1 = '';

    private string $nim2 = '';

    private string $nama1 = '';

    private string $nama2 = '';

    private string $nim3 = '';

    private string $nama3 = '';

    private string $jadwalId = '';

    private string $sesiId = '';

    private int $krsDetailId1 = 0;

    private int $krsDetailId2 = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffix = 'TST'.strtoupper(Str::random(6));
        $this->angkatanId = (int) now()->format('Y');
        $this->nim1 = 'T'.$this->suffix.'1';
        $this->nim2 = 'T'.$this->suffix.'2';
        $this->nama1 = 'Mahasiswa Satu '.$this->suffix;
        $this->nama2 = 'Mahasiswa Dua '.$this->suffix;
        $this->nim3 = 'T'.$this->suffix.'3';
        $this->nama3 = 'Mahasiswa Tiga '.$this->suffix;
        $this->seedFixtures();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();

        parent::tearDown();
    }

    public function test_manual_document_uses_active_class_roster(): void
    {
        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_MANUAL,
            $this->tahunAkademikId,
            $this->jadwalId,
        );

        expect($document->mode)->toBe(AbsensiDocumentService::MODE_MANUAL)
            ->and($document->rows)->toHaveCount(2)
            ->and($document->rows[0])->toMatchArray(['nim' => $this->nim1, 'nama' => $this->nama1])
            ->and($document->rows[1]['nim'])->toBe($this->nim2)
            ->and($document->rows[0]['status'])->toBe('')
            ->and($document->akademik['mata_kuliah'])->toBe('Mata Kuliah '.$this->suffix)
            ->and($document->akademik['kelas'])->toBe('Kelas '.$this->suffix)
            ->and($document->akademik['dosen'])->toBe('Dosen '.$this->suffix)
            ->and($document->akademik['tahun_akademik'])->toBe('2099/2100');
    }

    public function test_template_document_lists_meeting_columns_up_to_readable_limit(): void
    {
        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_TEMPLATE,
            $this->tahunAkademikId,
            $this->jadwalId,
        );

        expect($document->rows)->toHaveCount(2)
            ->and($document->pertemuan)->toBe(range(1, 16));

        foreach ($document->rows as $row) {
            expect($row['status'])->toBe('');
            expect($row['waktu'])->toBe('');
        }
    }

    public function test_template_meeting_count_follows_rencana_tatap_muka(): void
    {
        DB::table('jadwal_kuliah_dosen')
            ->where('jadwal_kuliah_id', $this->jadwalId)
            ->update(['rencana_tatap_muka' => 14]);

        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_TEMPLATE,
            $this->tahunAkademikId,
            $this->jadwalId,
        );

        expect($document->pertemuan)->toBe(range(1, 14));
    }

    public function test_online_document_reads_persisted_attendance_without_inventing_rows(): void
    {
        // Sesi 1: mahasiswa 1 hadir (check-in tercatat), mahasiswa 2 masih baris seed Alpa tanpa check-in.
        $sesi = PerkuliahanSesi::query()->find($this->sesiId);

        PerkuliahanAbsensi::create([
            'perkuliahan_sesi_id' => $sesi->id,
            'krs_detail_id' => $this->krsDetailId1,
            'status_kehadiran' => 'H',
            'waktu_check_in' => Carbon::parse('2099-01-15 08:05:00'),
        ]);
        PerkuliahanAbsensi::create([
            'perkuliahan_sesi_id' => $sesi->id,
            'krs_detail_id' => $this->krsDetailId2,
            'status_kehadiran' => 'A',
            'waktu_check_in' => null,
        ]);

        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_ONLINE,
            $this->tahunAkademikId,
            $this->jadwalId,
            $sesi->id,
        );

        expect($document->rows)->toHaveCount(2)
            ->and($document->rows[0])->toMatchArray([
                'nim' => $this->nim1,
                'status' => 'Hadir',
                'waktu' => '08:05',
                'belum_presensi' => false,
            ])
            ->and($document->rows[1])->toMatchArray([
                'nim' => $this->nim2,
                'status' => 'Alpa',
                'belum_presensi' => true,
            ])
            ->and($document->summary['hadir'])->toBe(1)
            ->and($document->summary['alpa'])->toBe(1)
            ->and($document->summary['belum_presensi'])->toBe(1)
            ->and($document->akademik['pertemuan'])->toBe(3)
            ->and($document->akademik['tanggal'])->toBe('15/01/2100');
    }

    public function test_online_document_marks_student_without_attendance_row(): void
    {
        $sesi = PerkuliahanSesi::query()->find($this->sesiId);

        // Hanya mahasiswa 1 yang punya baris absensi; mahasiswa 2 tanpa baris sama sekali.
        PerkuliahanAbsensi::create([
            'perkuliahan_sesi_id' => $sesi->id,
            'krs_detail_id' => $this->krsDetailId1,
            'status_kehadiran' => 'S',
            'waktu_check_in' => Carbon::parse('2099-01-15 08:10:00'),
        ]);

        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_ONLINE,
            $this->tahunAkademikId,
            $this->jadwalId,
            $sesi->id,
        );

        expect($document->rows[0]['status'])->toBe('Sakit')
            ->and($document->rows[1]['status'])->toBe('Belum Tercatat')
            ->and($document->rows[1]['belum_presensi'])->toBeTrue()
            ->and($document->summary['belum_presensi'])->toBe(1);
    }

    public function test_online_document_reports_empty_session_without_students(): void
    {
        // Kelas tanpa mahasiswa aktif, tetap harus menghasilkan dokumen kosong.
        MahasiswaKelas::query()
            ->where('kelas_id', $this->kelasId)
            ->update(['tanggal_keluar' => '2098-12-31']);

        $sesi = PerkuliahanSesi::query()->find($this->sesiId);
        $jadwal = JadwalKuliah::query()->find($this->jadwalId);
        $jadwal->krsDetail()->delete();

        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_ONLINE,
            $this->tahunAkademikId,
            $this->jadwalId,
            $sesi->id,
        );

        expect($document->rows)->toBeEmpty()
            ->and($document->summary['jumlah_mahasiswa'])->toBe(0);

        $manual = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_MANUAL,
            $this->tahunAkademikId,
            $this->jadwalId,
        );

        expect($manual->rows)->toBeEmpty();
    }

    public function test_resolve_rejects_schedule_from_another_academic_year(): void
    {
        $otherYearId = DB::table('ref_tahun_akademik')->insertGetId([
            'kode_tahun' => substr($this->suffix.'Y', 0, 5),
            'nama_tahun' => '2100/2101 '.$this->suffix,
            'semester' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->expectException(ValidationException::class);

            app(AbsensiDocumentService::class)->resolve(
                AbsensiDocumentService::MODE_MANUAL,
                $otherYearId,
                $this->jadwalId,
            );
        } finally {
            DB::table('ref_tahun_akademik')->where('id', $otherYearId)->delete();
        }
    }

    public function test_resolve_rejects_session_belonging_to_another_schedule(): void
    {
        $otherSesiId = (string) Str::uuid();
        $otherJadwalId = (string) Str::uuid();

        DB::table('jadwal_kuliah')->insert([
            'id' => $otherJadwalId,
            'tahun_akademik_id' => $this->tahunAkademikId,
            'mata_kuliah_id' => $this->mataKuliahId,
            'kelas_id' => $this->kelasId,
            'hari' => 'Jumat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('perkuliahan_sesi')->insert([
            'id' => $otherSesiId,
            'jadwal_kuliah_id' => $otherJadwalId,
            'pertemuan_ke' => 1,
            'waktu_mulai_rencana' => now(),
            'status_sesi' => 'terjadwal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->expectException(ValidationException::class);

            app(AbsensiDocumentService::class)->resolve(
                AbsensiDocumentService::MODE_ONLINE,
                $this->tahunAkademikId,
                $this->jadwalId,
                $otherSesiId,
            );
        } finally {
            DB::table('perkuliahan_absensi')->where('perkuliahan_sesi_id', $otherSesiId)->delete();
            DB::table('perkuliahan_sesi')->where('id', $otherSesiId)->delete();
            DB::table('jadwal_kuliah')->where('id', $otherJadwalId)->delete();
        }
    }

    public function test_manual_roster_uses_approved_krs_not_just_class_membership(): void
    {
        // Mahasiswa aktif di kelas, tapi TIDAK punya KRS disetujui → tidak boleh muncul.
        $tanpaKrsId = $this->createMahasiswa($this->nim3, $this->nama3);
        MahasiswaKelas::create([
            'mahasiswa_id' => $tanpaKrsId,
            'kelas_id' => $this->kelasId,
            'tanggal_masuk' => '2099-01-01',
            'tanggal_keluar' => null,
        ]);

        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_MANUAL,
            $this->tahunAkademikId,
            $this->jadwalId,
        );

        $nims = array_column($document->rows, 'nim');

        expect($nims)->toBe([$this->nim1, $this->nim2])
            ->and($nims)->not->toContain($this->nim3);
    }

    public function test_kelas_options_group_by_prodi_and_include_angkatan(): void
    {
        $options = app(AbsensiDocumentService::class)->kelasOptions($this->prodiId);

        expect(array_keys($options))->toContain('Prodi '.$this->suffix)
            ->and($options['Prodi '.$this->suffix][$this->kelasId])
            ->toBe('Kelas '.$this->suffix.' — Angkatan '.$this->angkatanId);
    }

    public function test_schedule_options_are_filtered_by_class_and_year(): void
    {
        $options = app(AbsensiDocumentService::class)->scheduleOptions(
            $this->tahunAkademikId,
            $this->prodiId,
            null,
            $this->kelasId,
        );

        expect($options)->toHaveCount(1)
            ->and($options->first()->id)->toBe($this->jadwalId);

        $empty = app(AbsensiDocumentService::class)->scheduleOptions(
            $this->tahunAkademikId,
            null,
            PHP_INT_MAX,
        );

        expect($empty)->toBeEmpty();
    }

    public function test_excel_export_maps_the_same_document_payload(): void
    {
        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_MANUAL,
            $this->tahunAkademikId,
            $this->jadwalId,
        );

        $export = new AbsensiDocumentExport($document);
        $array = $export->array();

        expect($array[0])->toBe(['No', 'NIM', 'Nama Mahasiswa', 'Tanda Tangan / Keterangan'])
            ->and($array[1])->toBe([1, $this->nim1, $this->nama1, ''])
            ->and($array)->toHaveCount(3) // header + 2 mahasiswa, tanpa duplikasi
            ->and($export->startCell())->toBe('A10');
    }

    public function test_excel_export_online_columns_match_document_rows(): void
    {
        $sesi = PerkuliahanSesi::query()->find($this->sesiId);
        PerkuliahanAbsensi::create([
            'perkuliahan_sesi_id' => $sesi->id,
            'krs_detail_id' => $this->krsDetailId1,
            'status_kehadiran' => 'H',
            'waktu_check_in' => Carbon::parse('2099-01-15 08:05:00'),
        ]);

        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_ONLINE,
            $this->tahunAkademikId,
            $this->jadwalId,
            $sesi->id,
        );

        $array = (new AbsensiDocumentExport($document))->array();

        expect($array[0])->toBe(['No', 'NIM', 'Nama Mahasiswa', 'Status', 'Jam', 'Keterangan'])
            ->and($array[1][3])->toBe('Hadir')
            ->and($array[1][4])->toBe('08:05')
            ->and($array)->toHaveCount(3);
    }

    public function test_pdf_template_renders_for_every_mode(): void
    {
        foreach ([
            AbsensiDocumentService::MODE_MANUAL,
            AbsensiDocumentService::MODE_TEMPLATE,
        ] as $mode) {
            $document = app(AbsensiDocumentService::class)->resolve(
                $mode,
                $this->tahunAkademikId,
                $this->jadwalId,
            );

            $pdf = app(PdfTemplateEngine::class)
                ->render('pdf.absensi.document', [
                    'mode' => $document->mode,
                    'akademik' => $document->akademik,
                    'rows' => $document->rows,
                    'summary' => $document->summary,
                    'pertemuan' => $document->pertemuan,
                ], ['paper' => 'a4', 'orientation' => 'portrait']);

            expect(substr($pdf->output(), 0, 4))->toBe('%PDF');
        }

        $sesi = PerkuliahanSesi::query()->find($this->sesiId);
        $document = app(AbsensiDocumentService::class)->resolve(
            AbsensiDocumentService::MODE_ONLINE,
            $this->tahunAkademikId,
            $this->jadwalId,
            $sesi->id,
        );

        $pdf = app(PdfTemplateEngine::class)
            ->render('pdf.absensi.document', [
                'mode' => $document->mode,
                'akademik' => $document->akademik,
                'rows' => $document->rows,
                'summary' => $document->summary,
                'pertemuan' => $document->pertemuan,
            ], ['paper' => 'a4', 'orientation' => 'portrait']);

        expect(substr($pdf->output(), 0, 4))->toBe('%PDF');
    }

    private function seedFixtures(): void
    {
        $this->fakultasId = DB::table('ref_fakultas')->insertGetId([
            'kode_fakultas' => substr('F-'.$this->suffix, 0, 10),
            'nama_fakultas' => 'Fakultas '.$this->suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->prodiId = DB::table('ref_prodi')->insertGetId([
            'fakultas_id' => $this->fakultasId,
            'kode_prodi_internal' => substr('P'.$this->suffix, 0, 10),
            'nama_prodi' => 'Prodi '.$this->suffix,
            'jenjang' => 'S1',
            'last_nim_seq' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->programId = DB::table('ref_program')->insertGetId([
            'kode_internal' => substr('PR'.$this->suffix, 0, 10),
            'nama_program' => 'Program '.$this->suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! DB::table('ref_angkatan')->where('id_tahun', $this->angkatanId)->exists()) {
            DB::table('ref_angkatan')->insert([
                'id_tahun' => $this->angkatanId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->angkatanBaruDibuat = true;
        }

        $this->kampusId = DB::table('ref_kampus')->insertGetId([
            'kode_kampus' => substr('K'.$this->suffix, 0, 20),
            'nama_kampus' => 'Kampus '.$this->suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->kelasId = DB::table('kelas')->insertGetId([
            'nama_kelas' => 'Kelas '.$this->suffix,
            'prodi_id' => $this->prodiId,
            'program_id' => $this->programId,
            'angkatan_id' => $this->angkatanId,
            'kampus_id' => $this->kampusId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->mataKuliahId = DB::table('master_mata_kuliahs')->insertGetId([
            'prodi_id' => $this->prodiId,
            'kode_mk' => substr('MK'.$this->suffix, 0, 20),
            'nama_mk' => 'Mata Kuliah '.$this->suffix,
            'sks_default' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->tahunAkademikId = DB::table('ref_tahun_akademik')->insertGetId([
            'kode_tahun' => substr($this->suffix.'T', 0, 5),
            'nama_tahun' => '2099/2100',
            'semester' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->mahasiswaId1 = $this->createMahasiswa($this->nim1, $this->nama1);
        $this->mahasiswaId2 = $this->createMahasiswa($this->nim2, $this->nama2);

        foreach ([$this->mahasiswaId1, $this->mahasiswaId2] as $mahasiswaId) {
            MahasiswaKelas::create([
                'mahasiswa_id' => $mahasiswaId,
                'kelas_id' => $this->kelasId,
                'tanggal_masuk' => '2099-01-01',
                'tanggal_keluar' => null,
            ]);
        }

        $this->jadwalId = (string) Str::uuid();
        DB::table('jadwal_kuliah')->insert([
            'id' => $this->jadwalId,
            'tahun_akademik_id' => $this->tahunAkademikId,
            'mata_kuliah_id' => $this->mataKuliahId,
            'kelas_id' => $this->kelasId,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:40:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dosenPersonId = DB::table('ref_person')->insertGetId([
            'nama_lengkap' => 'Dosen '.$this->suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $dosenId = (string) Str::uuid();
        DB::table('trx_dosen')->insert([
            'id' => $dosenId,
            'person_id' => $dosenPersonId,
            'prodi_id' => $this->prodiId,
            'jenis_dosen' => 'TETAP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('jadwal_kuliah_dosen')->insert([
            'jadwal_kuliah_id' => $this->jadwalId,
            'dosen_id' => $dosenId,
            'is_koordinator' => true,
            'is_penilai' => true,
            'rencana_tatap_muka' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // KRS + detail untuk mode online.
        foreach ([[$this->mahasiswaId1, 1], [$this->mahasiswaId2, 2]] as [$mahasiswaId, $sequence]) {
            $krsId = (string) Str::uuid();
            DB::table('krs')->insert([
                'id' => $krsId,
                'mahasiswa_id' => $mahasiswaId,
                'tahun_akademik_id' => $this->tahunAkademikId,
                'kelas_id' => $this->kelasId,
                'status_krs' => 'DISETUJUI',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $detailId = DB::table('krs_detail')->insertGetId([
                'krs_id' => $krsId,
                'jadwal_kuliah_id' => $this->jadwalId,
                'mata_kuliah_id' => $this->mataKuliahId,
                'status_ambil' => 'B',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($sequence === 1) {
                $this->krsDetailId1 = $detailId;
            } else {
                $this->krsDetailId2 = $detailId;
            }
        }

        $this->sesiId = (string) Str::uuid();
        DB::table('perkuliahan_sesi')->insert([
            'id' => $this->sesiId,
            'jadwal_kuliah_id' => $this->jadwalId,
            'pertemuan_ke' => 3,
            'waktu_mulai_rencana' => '2100-01-15 08:00:00',
            'status_sesi' => 'selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMahasiswa(string $nim, string $nama): string
    {
        $personId = DB::table('ref_person')->insertGetId([
            'nama_lengkap' => $nama,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Mahasiswa::create([
            'person_id' => $personId,
            'nim' => $nim,
            'angkatan_id' => $this->angkatanId,
            'prodi_id' => $this->prodiId,
            'program_id' => $this->programId,
        ])->id;
    }

    private function cleanupFixtures(): void
    {
        if ($this->jadwalId === '') {
            return;
        }

        $jadwalIds = DB::table('jadwal_kuliah')
            ->where('mata_kuliah_id', $this->mataKuliahId)
            ->pluck('id');

        DB::table('perkuliahan_absensi')
            ->whereIn('perkuliahan_sesi_id', DB::table('perkuliahan_sesi')->whereIn('jadwal_kuliah_id', $jadwalIds)->pluck('id'))
            ->delete();
        DB::table('perkuliahan_sesi')->whereIn('jadwal_kuliah_id', $jadwalIds)->delete();
        DB::table('krs_detail')->whereIn('jadwal_kuliah_id', $jadwalIds)->delete();
        DB::table('krs')->where('tahun_akademik_id', $this->tahunAkademikId)->delete();
        DB::table('jadwal_kuliah_dosen')->whereIn('jadwal_kuliah_id', $jadwalIds)->delete();
        DB::table('jadwal_kuliah')->whereIn('id', $jadwalIds)->delete();
        DB::table('mahasiswa_kelas')->where('kelas_id', $this->kelasId)->delete();
        DB::table('mahasiswas')->where('prodi_id', $this->prodiId)->where('angkatan_id', $this->angkatanId)->delete();
        DB::table('trx_dosen')->where('prodi_id', $this->prodiId)->delete();
        DB::table('ref_person')->where('nama_lengkap', 'like', '%'.$this->suffix)->delete();
        DB::table('kelas')->where('id', $this->kelasId)->delete();
        DB::table('master_mata_kuliahs')->where('id', $this->mataKuliahId)->delete();
        DB::table('ref_program')->where('id', $this->programId)->delete();
        if ($this->angkatanBaruDibuat) {
            DB::table('ref_angkatan')->where('id_tahun', $this->angkatanId)->delete();
        }
        DB::table('ref_kampus')->where('id', $this->kampusId)->delete();
        DB::table('ref_prodi')->where('id', $this->prodiId)->delete();
        DB::table('ref_fakultas')->where('id', $this->fakultasId)->delete();
        DB::table('ref_tahun_akademik')->where('id', $this->tahunAkademikId)->delete();
    }
}
