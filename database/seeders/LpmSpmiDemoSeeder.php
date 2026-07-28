<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\LpmAkreditasi;
use App\Models\LpmAkreditasiElemen;
use App\Models\LpmAkreditasiEvidence;
use App\Models\LpmAkreditasiIndikator;
use App\Models\LpmAkreditasiKriteria;
use App\Models\LpmAkreditasiLembaga;
use App\Models\LpmAmiChecklist;
use App\Models\LpmAmiChecklistItem;
use App\Models\LpmAmiChecklistJawaban;
use App\Models\LpmAmiDiscussion;
use App\Models\LpmAmiEvidence;
use App\Models\LpmAmiFinding;
use App\Models\LpmAmiPeriode;
use App\Models\LpmAmiProgram;
use App\Models\LpmAmiProgramAuditor;
use App\Models\LpmAuditor;
use App\Models\LpmBenchmark;
use App\Models\LpmBenchmarkInstitusi;
use App\Models\LpmBuktiPelaksanaan;
use App\Models\LpmDokumen;
use App\Models\LpmDokumenApproval;
use App\Models\LpmDokumenRiwayat;
use App\Models\LpmIkuTarget;
use App\Models\LpmIndikator;
use App\Models\LpmKategoriStandar;
use App\Models\LpmKuisionerKelompok;
use App\Models\LpmKuisionerPertanyaan;
use App\Models\LpmRiwayatPeningkatan;
use App\Models\LpmStandar;
use App\Models\LpmSurveyAnalisis;
use App\Models\LpmSurveyJawabanPihak;
use App\Models\LpmUnitKerja;
use App\Models\LpmUnitPic;
use App\Models\RefFakultas;
use App\Models\RefPerson;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Demo seeder untuk modul LPM/SPMI, dirancang untuk dijalankan di database
 * SIAKAD yang SUDAH punya data akademik existing (Fakultas, Prodi, Person,
 * Dosen, Tahun Akademik) — seeder ini TIDAK membuat data akademik baru,
 * hanya memakai baris pertama yang sudah ada lewat ::first()/::limit().
 *
 * Jalankan dengan: php artisan db:seed --class=Database\\Seeders\\LpmSpmiDemoSeeder
 *
 * Urutan pemanggilan method di run() SENGAJA mengikuti urutan dependency FK
 * (unit kerja lebih dulu dari standar, standar lebih dulu dari indikator,
 * dst) — jangan diacak urutannya kalau menambah seed baru.
 */
class LpmSpmiDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $units = $this->seedOrganisasiMutu();
            $kategoriStandars = $this->seedKategoriStandar();
            $standars = $this->seedStandarDanIndikator($kategoriStandars);
            $this->seedIkuTargets($standars, $units);
            $this->seedDokumenMutu($units, $standars);
            $kelompoks = $this->seedKuisioner();
            $this->seedSurveyJawabanPihak($kelompoks);
            $this->seedSurveyAnalisis($kelompoks);
            $auditors = $this->seedAuditors();
            $checklists = $this->seedAmiChecklists($standars);
            $programs = $this->seedAmiPrograms($units, $auditors);
            $this->seedAmiChecklistJawabanDanTemuan($programs, $checklists);
            $lembagas = $this->seedAkreditasiLembagas();
            $this->seedAkreditasi($lembagas, $units);
            $institusis = $this->seedBenchmarkInstitusis();
            $this->seedBenchmarks($institusis, $standars);
            $this->seedPpeppEvidence($standars, $units);
        });

        $this->command?->info('LPM/SPMI demo data berhasil di-seed.');
    }

    /**
     * @return array{universitas: LpmUnitKerja, lembaga_lpm: LpmUnitKerja, fakultas: ?LpmUnitKerja, prodi: ?LpmUnitKerja}
     */
    private function seedOrganisasiMutu(): array
    {
        $fakultas = RefFakultas::query()->first();
        $prodi = RefProdi::query()->first();

        $universitas = LpmUnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'UNIV-01'],
            ['jenis_unit' => 'UNIVERSITAS', 'nama_unit' => 'Universitas (Pusat)', 'is_active' => true]
        );

        $lembagaLpm = LpmUnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'LPM-01'],
            [
                'jenis_unit' => 'LEMBAGA',
                'nama_unit' => 'Lembaga Penjaminan Mutu',
                'parent_id' => $universitas->id,
                'is_active' => true,
            ]
        );

        $unitFakultas = null;
        if ($fakultas) {
            $unitFakultas = LpmUnitKerja::query()->firstOrCreate(
                ['kode_unit' => 'FAK-' . $fakultas->id],
                [
                    'jenis_unit' => 'FAKULTAS',
                    'nama_unit' => $fakultas->nama_fakultas,
                    'fakultas_id' => $fakultas->id,
                    'parent_id' => $universitas->id,
                    'is_active' => true,
                ]
            );
        }

        $unitProdi = null;
        if ($prodi) {
            $unitProdi = LpmUnitKerja::query()->firstOrCreate(
                ['kode_unit' => 'PRODI-' . $prodi->id],
                [
                    'jenis_unit' => 'PRODI',
                    'nama_unit' => $prodi->nama_prodi,
                    'prodi_id' => $prodi->id,
                    'parent_id' => $unitFakultas?->id ?? $universitas->id,
                    'is_active' => true,
                ]
            );
        }

        // PIC: pakai person yang sudah ada di sistem, kalau ada.
        $people = RefPerson::query()->inRandomOrder()->limit(2)->get();
        foreach ($people as $index => $person) {
            LpmUnitPic::query()->firstOrCreate(
                [
                    'unit_kerja_id' => $lembagaLpm->id,
                    'person_id' => $person->id,
                    'peran' => $index === 0 ? 'KETUA' : 'GKM',
                ],
                ['tanggal_mulai' => now()->subYear()->startOfYear()]
            );
        }

        return [
            'universitas' => $universitas,
            'lembaga_lpm' => $lembagaLpm,
            'fakultas' => $unitFakultas,
            'prodi' => $unitProdi,
        ];
    }

    /** @return array<string, LpmKategoriStandar> */
    private function seedKategoriStandar(): array
    {
        $data = [
            'PENDIDIKAN' => ['nama' => 'Standar Pendidikan', 'urutan' => 1],
            'PENELITIAN' => ['nama' => 'Standar Penelitian', 'urutan' => 2],
            'PENGABDIAN' => ['nama' => 'Standar Pengabdian kepada Masyarakat', 'urutan' => 3],
            'TAMBAHAN' => ['nama' => 'Standar Tambahan Universitas', 'urutan' => 4],
        ];

        $result = [];
        foreach ($data as $kode => $attrs) {
            $result[$kode] = LpmKategoriStandar::query()->firstOrCreate(
                ['kode' => $kode],
                ['nama' => $attrs['nama'], 'urutan' => $attrs['urutan'], 'is_active' => true]
            );
        }

        return $result;
    }

    /**
     * @param  array<string, LpmKategoriStandar>  $kategoriStandars
     * @return array<int, LpmStandar>
     */
    private function seedStandarDanIndikator(array $kategoriStandars): array
    {
        $definisi = [
            [
                'kategori_kode' => 'PENDIDIKAN',
                'kategori_enum' => 'AKADEMIK',
                'kode_standar' => 'STD-DIK-01',
                'nama_standar' => 'Standar Kompetensi Lulusan',
                'pernyataan' => 'Lulusan wajib memiliki kompetensi sesuai capaian pembelajaran lulusan (CPL) program studi.',
                'indikators' => [
                    ['kode' => 'IND-DIK-01-01', 'nama' => 'Rata-rata IPK Lulusan', 'satuan' => 'IPK'],
                    ['kode' => 'IND-DIK-01-02', 'nama' => 'Persentase Lulusan Tepat Waktu', 'satuan' => '%'],
                ],
            ],
            [
                'kategori_kode' => 'PENDIDIKAN',
                'kategori_enum' => 'AKADEMIK',
                'kode_standar' => 'STD-DIK-02',
                'nama_standar' => 'Standar Proses Pembelajaran',
                'pernyataan' => 'Setiap mata kuliah wajib memiliki RPS yang mutakhir dan dilaksanakan sesuai rencana.',
                'indikators' => [
                    ['kode' => 'IND-DIK-02-01', 'nama' => 'Persentase Dosen Memiliki RPS', 'satuan' => '%'],
                ],
            ],
            [
                'kategori_kode' => 'PENELITIAN',
                'kategori_enum' => 'AKADEMIK',
                'kode_standar' => 'STD-LIT-01',
                'nama_standar' => 'Standar Hasil Penelitian',
                'pernyataan' => 'Penelitian dosen wajib menghasilkan luaran yang terpublikasi setiap tahun.',
                'indikators' => [
                    ['kode' => 'IND-LIT-01-01', 'nama' => 'Jumlah Publikasi Dosen per Tahun', 'satuan' => 'Dokumen'],
                ],
            ],
            [
                'kategori_kode' => 'PENGABDIAN',
                'kategori_enum' => 'AKADEMIK',
                'kode_standar' => 'STD-PKM-01',
                'nama_standar' => 'Standar Hasil Pengabdian kepada Masyarakat',
                'pernyataan' => 'Kegiatan PkM wajib memberi manfaat terukur bagi mitra/masyarakat sasaran.',
                'indikators' => [
                    ['kode' => 'IND-PKM-01-01', 'nama' => 'Jumlah Kegiatan PkM per Tahun', 'satuan' => 'Kegiatan'],
                ],
            ],
            [
                'kategori_kode' => 'TAMBAHAN',
                'kategori_enum' => 'NON-AKADEMIK',
                'kode_standar' => 'STD-TAM-01',
                'nama_standar' => 'Standar Kepuasan Layanan',
                'pernyataan' => 'Layanan akademik dan non-akademik wajib memenuhi tingkat kepuasan minimum civitas akademika.',
                'indikators' => [
                    ['kode' => 'IND-TAM-01-01', 'nama' => 'Indeks Kepuasan Layanan', 'satuan' => 'Skala 1-4'],
                ],
            ],
        ];

        $standars = [];
        foreach ($definisi as $item) {
            $standar = LpmStandar::query()->firstOrCreate(
                ['kode_standar' => $item['kode_standar']],
                [
                    'nama_standar' => $item['nama_standar'],
                    'kategori' => $item['kategori_enum'],
                    'kategori_standar_id' => $kategoriStandars[$item['kategori_kode']]->id,
                    'pernyataan_standar' => $item['pernyataan'],
                    'target_pencapaian' => 100,
                    'satuan' => '%',
                    'versi' => 1,
                    'is_active' => true,
                ]
            );

            foreach ($item['indikators'] as $indDef) {
                LpmIndikator::query()->firstOrCreate(
                    ['kode_indikator' => $indDef['kode']],
                    [
                        'standar_id' => $standar->id,
                        'nama_indikator' => $indDef['nama'],
                        'satuan' => $indDef['satuan'],
                        'slug' => Str::slug($indDef['kode'] . '-' . $indDef['nama']),
                        'bobot' => 1,
                        'is_iku' => true,
                        'is_active' => true,
                    ]
                );
            }

            $standars[] = $standar->fresh(['indikators']);
        }

        return $standars;
    }

    /**
     * @param  array<int, LpmStandar>  $standars
     * @param  array<string, mixed>  $units
     */
    private function seedIkuTargets(array $standars, array $units): void
    {
        $tahunIni = (int) now()->year;
        $prodiUnit = $units['prodi'];

        foreach ($standars as $standar) {
            foreach ($standar->indikators as $index => $indikator) {
                // Sengaja dibuat variatif: sebagian tercapai, sebagian belum,
                // supaya widget Dashboard (ketercapaian/standar kritis) langsung
                // terlihat isinya begitu di-seed.
                $target = 100.0;
                $capaian = $index % 2 === 0 ? 95.0 : 68.0;

                LpmIkuTarget::query()->firstOrCreate(
                    [
                        'indikator_id' => $indikator->id,
                        'prodi_id' => $prodiUnit?->prodi_id,
                        'tahun' => $tahunIni,
                    ],
                    [
                        'unit_kerja_id' => $prodiUnit?->id,
                        'target_nilai' => $target,
                        'capaian_nilai' => $capaian,
                        'status' => 'VALIDATED',
                        'analisis_kendala' => $capaian < $target ? 'Belum optimal, perlu tindak lanjut unit terkait.' : null,
                        'tindakan_koreksi' => $capaian < $target ? 'Sosialisasi ulang & monitoring berkala tiap semester.' : null,
                    ]
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $units
     * @param  array<int, LpmStandar>  $standars
     */
    private function seedDokumenMutu(array $units, array $standars): void
    {
        $dokumens = [
            [
                'kode' => 'DOK-KM-001',
                'nama' => 'Kebijakan Mutu Universitas',
                'jenis' => 'KEBIJAKAN',
                'versi' => '1.0',
                'status' => 'PUBLISHED',
            ],
            [
                'kode' => 'DOK-MM-001',
                'nama' => 'Manual Mutu SPMI',
                'jenis' => 'MANUAL',
                'versi' => '1.0',
                'status' => 'PUBLISHED',
            ],
            [
                'kode' => 'DOK-SOP-001',
                'nama' => 'SOP Audit Mutu Internal',
                'jenis' => 'SOP',
                'versi' => '1.0',
                'status' => 'REVIEW',
            ],
        ];

        $people = RefPerson::query()->inRandomOrder()->limit(3)->get();

        foreach ($dokumens as $index => $def) {
            $dokumen = LpmDokumen::query()->firstOrCreate(
                ['kode_dokumen' => $def['kode']],
                [
                    'nama_dokumen' => $def['nama'],
                    'jenis' => $def['jenis'],
                    'unit_kerja_id' => $units['lembaga_lpm']->id,
                    'standar_id' => $standars[0]?->id,
                    'file_path' => 'lpm/dokumen/demo-' . Str::slug($def['nama']) . '.pdf',
                    'deskripsi' => 'Dokumen contoh hasil seeding demo LPM/SPMI.',
                    'versi' => $def['versi'],
                    'status' => $def['status'],
                    'is_active' => true,
                    'tgl_berlaku' => now()->subMonths(2),
                ]
            );

            $peran = ['PENYUSUN', 'PEMERIKSA', 'PENGESAH'];
            foreach ($peran as $peranIndex => $peranNama) {
                if (! isset($people[$peranIndex])) {
                    continue;
                }

                LpmDokumenApproval::query()->firstOrCreate(
                    ['dokumen_id' => $dokumen->id, 'peran' => $peranNama, 'person_id' => $people[$peranIndex]->id],
                    [
                        'status' => $def['status'] === 'PUBLISHED' ? 'APPROVED' : 'PENDING',
                        'approved_at' => $def['status'] === 'PUBLISHED' ? now()->subMonths(2) : null,
                    ]
                );
            }

            if ($index === 0) {
                LpmDokumenRiwayat::query()->firstOrCreate(
                    ['dokumen_id' => $dokumen->id, 'versi_baru' => '1.0'],
                    [
                        'versi_lama' => '0.1',
                        'file_path' => $dokumen->file_path,
                        'changelog' => 'Penerbitan pertama dokumen (seed demo).',
                        'diubah_oleh_person_id' => $people->first()?->id,
                        'tanggal' => now()->subMonths(2),
                    ]
                );
            }
        }
    }

    /** @return array{edom: LpmKuisionerKelompok, kepuasan_mahasiswa: LpmKuisionerKelompok, kepuasan_dosen: LpmKuisionerKelompok} */
    private function seedKuisioner(): array
    {
        $tahunAkademik = RefTahunAkademik::query()->latest('id')->first();

        $edom = LpmKuisionerKelompok::query()->firstOrCreate(
            ['nama_kelompok' => 'EDOM Semester Berjalan', 'kategori' => 'EDOM'],
            ['tahun_akademik_id' => $tahunAkademik?->id, 'urutan' => 1, 'is_active' => true]
        );

        $kepuasanMhs = LpmKuisionerKelompok::query()->firstOrCreate(
            ['nama_kelompok' => 'Kepuasan Mahasiswa Terhadap Layanan', 'kategori' => 'KEPUASAN_MAHASISWA'],
            ['tahun_akademik_id' => $tahunAkademik?->id, 'urutan' => 2, 'is_active' => true]
        );

        $kepuasanDosen = LpmKuisionerKelompok::query()->firstOrCreate(
            ['nama_kelompok' => 'Kepuasan Dosen Terhadap Manajemen Kampus', 'kategori' => 'KEPUASAN_DOSEN'],
            ['tahun_akademik_id' => $tahunAkademik?->id, 'urutan' => 3, 'is_active' => true]
        );

        $pertanyaanPerKelompok = [
            $kepuasanMhs->id => [
                'Layanan administrasi akademik cepat dan responsif.',
                'Fasilitas kampus mendukung proses belajar.',
            ],
            $kepuasanDosen->id => [
                'Dukungan manajemen terhadap kegiatan Tridharma memadai.',
                'Sistem informasi akademik memudahkan pekerjaan saya.',
            ],
        ];

        foreach ($pertanyaanPerKelompok as $kelompokId => $pertanyaans) {
            foreach ($pertanyaans as $urutan => $bunyi) {
                LpmKuisionerPertanyaan::query()->firstOrCreate(
                    ['kelompok_id' => $kelompokId, 'bunyi_pertanyaan' => $bunyi],
                    ['jenis_input' => 'RATING_4', 'is_required' => true, 'urutan' => $urutan + 1]
                );
            }
        }

        return ['edom' => $edom, 'kepuasan_mahasiswa' => $kepuasanMhs, 'kepuasan_dosen' => $kepuasanDosen];
    }

    /** @param array{kepuasan_dosen: LpmKuisionerKelompok} $kelompoks */
    private function seedSurveyJawabanPihak(array $kelompoks): void
    {
        $tahunAkademik = RefTahunAkademik::query()->latest('id')->first();
        $pertanyaans = $kelompoks['kepuasan_dosen']->pertanyaans()->get();
        $dosens = TrxDosen::query()->with('person')->inRandomOrder()->limit(2)->get();

        if ($pertanyaans->isEmpty() || $dosens->isEmpty() || ! $tahunAkademik) {
            return;
        }

        foreach ($dosens as $dosen) {
            if (! $dosen->person_id) {
                continue;
            }

            foreach ($pertanyaans as $pertanyaan) {
                LpmSurveyJawabanPihak::query()->firstOrCreate(
                    [
                        'person_id' => $dosen->person_id,
                        'pertanyaan_id' => $pertanyaan->id,
                        'tahun_akademik_id' => $tahunAkademik->id,
                    ],
                    [
                        'jenis_responden' => 'DOSEN',
                        'jawaban_nilai' => (string) random_int(3, 4),
                    ]
                );
            }
        }

        // Contoh 1 responden eksternal (alumni) tanpa person_id.
        $kelompokAlumni = LpmKuisionerKelompok::query()->firstOrCreate(
            ['nama_kelompok' => 'Kepuasan Alumni', 'kategori' => 'KEPUASAN_ALUMNI'],
            ['tahun_akademik_id' => $tahunAkademik->id, 'urutan' => 4, 'is_active' => true]
        );
        $pertanyaanAlumni = LpmKuisionerPertanyaan::query()->firstOrCreate(
            ['kelompok_id' => $kelompokAlumni->id, 'bunyi_pertanyaan' => 'Kurikulum program studi relevan dengan kebutuhan dunia kerja.'],
            ['jenis_input' => 'RATING_4', 'is_required' => true, 'urutan' => 1]
        );

        LpmSurveyJawabanPihak::query()->firstOrCreate(
            ['nama_eksternal' => 'Alumni Demo 2024', 'pertanyaan_id' => $pertanyaanAlumni->id, 'tahun_akademik_id' => $tahunAkademik->id],
            ['jenis_responden' => 'ALUMNI', 'instansi_eksternal' => 'PT Contoh Sejahtera', 'jawaban_nilai' => '4']
        );
    }

    /** @param array{kepuasan_dosen: LpmKuisionerKelompok} $kelompoks */
    private function seedSurveyAnalisis(array $kelompoks): void
    {
        $tahunAkademik = RefTahunAkademik::query()->latest('id')->first();
        $penyusun = RefPerson::query()->inRandomOrder()->first();

        LpmSurveyAnalisis::query()->firstOrCreate(
            ['kelompok_id' => $kelompoks['kepuasan_dosen']->id, 'tahun_akademik_id' => $tahunAkademik?->id],
            [
                'rata_rata_skor' => 3.5,
                'kesimpulan' => 'Secara umum dosen puas terhadap dukungan manajemen, namun sistem informasi masih perlu penyempurnaan.',
                'rencana_perbaikan' => 'Perbaikan modul SIAKAD terkait pelaporan beban kerja dosen pada semester depan.',
                'disusun_oleh_person_id' => $penyusun?->id,
                'tanggal' => now(),
            ]
        );
    }

    /** @return array<int, LpmAuditor> */
    private function seedAuditors(): array
    {
        $dosens = TrxDosen::query()->with('person')->whereNotNull('person_id')->inRandomOrder()->limit(2)->get();

        $auditors = [];
        foreach ($dosens as $index => $dosen) {
            $auditors[] = LpmAuditor::query()->firstOrCreate(
                ['person_id' => $dosen->person_id],
                [
                    'no_sertifikat_auditor' => 'AMI-2024-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'kompetensi' => 'Pelatihan Auditor Mutu Internal Angkatan ' . ($index + 1) . ' (data demo).',
                    'is_active' => true,
                ]
            );
        }

        return $auditors;
    }

    /**
     * @param  array<int, LpmStandar>  $standars
     * @return array<int, LpmAmiChecklist>
     */
    private function seedAmiChecklists(array $standars): array
    {
        if (empty($standars)) {
            return [];
        }

        $checklist = LpmAmiChecklist::query()->firstOrCreate(
            ['standar_id' => $standars[0]->id, 'kriteria' => 'Ketersediaan RPS dan Kelengkapan Dokumen Pembelajaran'],
            ['urutan' => 1]
        );

        $items = [
            'Apakah seluruh mata kuliah memiliki RPS yang mutakhir?',
            'Apakah RPS ditinjau ulang minimal setiap tahun ajaran?',
        ];

        foreach ($items as $urutan => $pertanyaan) {
            LpmAmiChecklistItem::query()->firstOrCreate(
                ['checklist_id' => $checklist->id, 'pertanyaan' => $pertanyaan],
                ['urutan' => $urutan + 1]
            );
        }

        return [$checklist->fresh('items')];
    }

    /**
     * @param  array<string, mixed>  $units
     * @param  array<int, LpmAuditor>  $auditors
     * @return array<int, LpmAmiProgram>
     */
    private function seedAmiPrograms(array $units, array $auditors): array
    {
        $unitDiaudit = $units['prodi'] ?? $units['fakultas'] ?? $units['lembaga_lpm'];

        $periode = LpmAmiPeriode::query()->firstOrCreate(
            ['nama_periode' => 'AMI Tahun ' . now()->year],
            [
                'tahun' => now()->year,
                'tgl_mulai' => now()->startOfYear(),
                'tgl_selesai' => now()->endOfYear(),
                'tanggal_mulai' => now()->startOfYear(),
                'tanggal_selesai' => now()->endOfYear(),
                'status' => 'ON-GOING',
                'is_active' => true,
            ]
        );

        $program = LpmAmiProgram::query()->firstOrCreate(
            ['periode_id' => $periode->id, 'unit_kerja_id' => $unitDiaudit->id],
            ['tanggal_pelaksanaan' => now()->subWeek(), 'status' => 'BERLANGSUNG']
        );

        foreach ($auditors as $index => $auditor) {
            LpmAmiProgramAuditor::query()->firstOrCreate(
                ['program_id' => $program->id, 'auditor_id' => $auditor->id],
                ['peran' => $index === 0 ? 'KETUA_TIM' : 'ANGGOTA']
            );
        }

        return [$program];
    }

    /**
     * @param  array<int, LpmAmiProgram>  $programs
     * @param  array<int, LpmAmiChecklist>  $checklists
     */
    private function seedAmiChecklistJawabanDanTemuan(array $programs, array $checklists): void
    {
        if (empty($programs) || empty($checklists)) {
            return;
        }

        $program = $programs[0];
        $items = $checklists[0]->items;

        if ($items->isEmpty()) {
            return;
        }

        // Item pertama: Sesuai (tidak memicu temuan).
        LpmAmiChecklistJawaban::query()->firstOrCreate(
            ['program_id' => $program->id, 'checklist_item_id' => $items[0]->id],
            ['jawaban' => 'SESUAI', 'catatan' => 'Sudah sesuai, dokumen lengkap dan mutakhir.']
        );

        if (! isset($items[1])) {
            return;
        }

        // Item kedua: Tidak Sesuai -> otomatis memicu Temuan (mereplikasi
        // logic tombol "Buat Temuan" di ChecklistJawabansRelationManager).
        $jawabanTidakSesuai = LpmAmiChecklistJawaban::query()->firstOrCreate(
            ['program_id' => $program->id, 'checklist_item_id' => $items[1]->id],
            ['jawaban' => 'TIDAK_SESUAI', 'catatan' => 'Sebagian RPS belum ditinjau ulang tahun ini.']
        );

        if ($jawabanTidakSesuai->finding_id) {
            return;
        }

        $checklistItem = $jawabanTidakSesuai->checklistItem()->with('checklist')->first();
        $prodi = RefProdi::query()->first();

        if (! $prodi) {
            return;
        }

        $finding = LpmAmiFinding::query()->create([
            'periode_id' => $program->periode_id,
            'program_id' => $program->id,
            'prodi_id' => $prodi->id,
            'standar_id' => $checklistItem->checklist->standar_id,
            'jenis_temuan' => 'KTS',
            'auditor_name' => 'Tim Auditor Demo',
            'klasifikasi' => 'KTS_MINOR',
            'deskripsi_temuan' => 'Ditemukan sebagian RPS mata kuliah belum ditinjau ulang sesuai periode yang ditetapkan.',
            'rekomendasi' => 'Lakukan peninjauan RPS secara berkala setiap awal tahun ajaran.',
            'akar_masalah' => 'Belum ada mekanisme pengingat otomatis peninjauan RPS.',
            'rencana_tindak_lanjut' => 'Prodi menjadwalkan review RPS bersama seluruh dosen pengampu.',
            'preventive_action' => 'Menambahkan reminder otomatis di sistem akademik menjelang semester baru.',
            'deadline_perbaikan' => now()->addMonths(2),
            'status_workflow' => 'OPEN',
            'is_closed' => false,
        ]);

        $jawabanTidakSesuai->update(['finding_id' => $finding->id]);

        LpmAmiEvidence::query()->create([
            'checklist_jawaban_id' => $jawabanTidakSesuai->id,
            'finding_id' => $finding->id,
            'file_path' => 'lpm/ami/evidences/demo-daftar-rps.pdf',
            'keterangan' => 'Daftar RPS yang belum ditinjau ulang (demo).',
        ]);

        LpmAmiDiscussion::query()->create([
            'finding_id' => $finding->id,
            'user_id' => DB::table('users')->value('id'),
            'message' => 'Prodi sudah menerima temuan ini dan akan menjadwalkan review RPS bulan depan.',
        ]);
    }

    /** @return array<int, LpmAkreditasiLembaga> */
    private function seedAkreditasiLembagas(): array
    {
        $banpt = LpmAkreditasiLembaga::query()->firstOrCreate(
            ['kode' => 'BANPT'],
            ['nama' => 'Badan Akreditasi Nasional Perguruan Tinggi', 'jenis' => 'PRODI']
        );

        return [$banpt];
    }

    /**
     * @param  array<int, LpmAkreditasiLembaga>  $lembagas
     * @param  array<string, mixed>  $units
     */
    private function seedAkreditasi(array $lembagas, array $units): void
    {
        if (empty($lembagas)) {
            return;
        }

        $prodi = RefProdi::query()->first();
        $person = RefPerson::query()->inRandomOrder()->first();

        $akreditasi = LpmAkreditasi::query()->firstOrCreate(
            ['lembaga_id' => $lembagas[0]->id, 'prodi_id' => $prodi?->id, 'jenis_akreditasi' => 'PRODI'],
            [
                'instrumen' => 'IAPS 4.0',
                'status' => 'PENGISIAN',
                'peringkat_target' => 'Unggul',
            ]
        );

        $kriteria = LpmAkreditasiKriteria::query()->firstOrCreate(
            ['akreditasi_id' => $akreditasi->id, 'kode_kriteria' => 'C4'],
            ['nama_kriteria' => 'Sumber Daya Manusia', 'urutan' => 4]
        );

        $elemen = LpmAkreditasiElemen::query()->firstOrCreate(
            ['kriteria_id' => $kriteria->id, 'kode_elemen' => 'C4.1'],
            [
                'deskripsi' => 'Kecukupan dan kualifikasi dosen tetap program studi.',
                'urutan' => 1,
                'status_kelengkapan' => 'PROSES',
            ]
        );

        $indikator = LpmAkreditasiIndikator::query()->firstOrCreate(
            ['elemen_id' => $elemen->id, 'deskripsi' => 'Rasio dosen tetap terhadap mahasiswa'],
            ['bobot' => 5]
        );

        LpmAkreditasiEvidence::query()->firstOrCreate(
            ['elemen_id' => $elemen->id, 'indikator_id' => $indikator->id],
            [
                'file_path' => 'lpm/akreditasi/evidences/demo-rasio-dosen-mahasiswa.pdf',
                'keterangan' => 'Rekap rasio dosen:mahasiswa 3 tahun terakhir (demo).',
                'uploaded_by_person_id' => $person?->id,
            ]
        );
    }

    /** @return array<int, LpmBenchmarkInstitusi> */
    private function seedBenchmarkInstitusis(): array
    {
        $institusiA = LpmBenchmarkInstitusi::query()->firstOrCreate(
            ['nama_institusi' => 'Universitas Pembanding A'],
            ['jenis' => 'PTN', 'catatan' => 'Institusi pembanding sekawasan (data demo).']
        );

        return [$institusiA];
    }

    /**
     * @param  array<int, LpmBenchmarkInstitusi>  $institusis
     * @param  array<int, LpmStandar>  $standars
     */
    private function seedBenchmarks(array $institusis, array $standars): void
    {
        if (empty($institusis) || empty($standars)) {
            return;
        }

        $indikator = $standars[0]->indikators->first();

        if (! $indikator) {
            return;
        }

        LpmBenchmark::query()->firstOrCreate(
            [
                'indikator_id' => $indikator->id,
                'institusi_pembanding_id' => $institusis[0]->id,
                'tahun' => now()->year,
            ],
            [
                'nilai_internal' => 3.45,
                'nilai_eksternal' => 3.60,
                'analisis_gap' => 'Nilai internal sedikit di bawah institusi pembanding, perlu peningkatan mutu bimbingan akademik.',
                'sumber_data' => 'Laporan PDDIKTI (data demo).',
            ]
        );
    }

    /**
     * Melengkapi siklus PPEPP: bukti pelaksanaan (untuk target IKU & tindak
     * lanjut temuan) dan riwayat peningkatan standar. Dipanggil paling akhir
     * karena butuh IKU Target, Finding, dan Standar yang sudah ter-seed.
     *
     * @param  array<int, LpmStandar>  $standars
     * @param  array<string, mixed>  $units
     */
    private function seedPpeppEvidence(array $standars, array $units): void
    {
        $unit = $units['prodi'] ?? $units['fakultas'] ?? $units['lembaga_lpm'];
        $person = RefPerson::query()->inRandomOrder()->first();

        // Bukti pelaksanaan atas realisasi target IKU.
        $ikuTarget = LpmIkuTarget::query()->first();
        if ($ikuTarget) {
            LpmBuktiPelaksanaan::query()->firstOrCreate(
                ['iku_target_id' => $ikuTarget->id, 'judul' => 'Laporan Realisasi Capaian Indikator (Demo)'],
                [
                    'unit_kerja_id' => $unit->id,
                    'file_path' => 'lpm/bukti-pelaksanaan/demo-realisasi-indikator.pdf',
                    'keterangan' => 'Dokumentasi pendukung capaian indikator tahun berjalan.',
                    'uploaded_by_person_id' => $person?->id,
                    'tanggal' => now()->subMonth(),
                ]
            );
        }

        // Bukti pelaksanaan tindak lanjut atas temuan AMI.
        $finding = LpmAmiFinding::query()->first();
        if ($finding) {
            LpmBuktiPelaksanaan::query()->firstOrCreate(
                ['finding_id' => $finding->id, 'judul' => 'Dokumentasi Pelaksanaan Corrective Action (Demo)'],
                [
                    'unit_kerja_id' => $unit->id,
                    'file_path' => 'lpm/bukti-pelaksanaan/demo-corrective-action.pdf',
                    'keterangan' => 'Bukti bahwa rencana tindak lanjut temuan sudah mulai dijalankan.',
                    'uploaded_by_person_id' => $person?->id,
                    'tanggal' => now()->subWeeks(2),
                ]
            );
        }

        // Riwayat peningkatan: naikkan versi standar pertama dari 1 -> 2.
        if (! empty($standars)) {
            $standar = $standars[0];

            $sudahAdaRiwayat = LpmRiwayatPeningkatan::query()
                ->where('standar_id', $standar->id)
                ->exists();

            if (! $sudahAdaRiwayat) {
                $versiLama = (int) $standar->versi;
                $versiBaru = $versiLama + 1;

                LpmRiwayatPeningkatan::create([
                    'standar_id' => $standar->id,
                    'versi_lama' => $versiLama,
                    'versi_baru' => $versiBaru,
                    'ringkasan_perubahan' => 'Penyesuaian target pencapaian berdasarkan hasil evaluasi tahun berjalan (demo).',
                    'dasar_peningkatan' => 'HASIL_MONEV',
                    'disetujui_oleh_person_id' => $person?->id,
                    'tanggal' => now(),
                ]);

                $standar->update(['versi' => $versiBaru]);
            }
        }
    }
}
