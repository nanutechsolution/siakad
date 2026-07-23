<?php

declare(strict_types=1);

namespace App\Application\Migration\Services;

use App\Domain\Akademik\Enums\StatusAmbil;
use App\Domain\Migration\DTOs\GradeMigrationRowData;
use App\Domain\Migration\DTOs\GradeMigrationRowResult;
use App\Domain\Migration\Enums\MigrationRowStatus;
use App\Domain\Migration\Exceptions\GradeMigrationResolutionException;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\MigrationBatch;
use App\Models\MigrationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Orkestrator utama migrasi nilai — TIDAK PERNAH tahu asal data (Excel/CSV/Neo).
 * Menerima Collection<GradeMigrationRowData> yang sudah dinormalisasi oleh
 * MigrationSourceInterface::fetch().
 */
final class ImportGradeService
{
    public function __construct(
        private readonly GradeMigrationResolverService $resolver,
        private readonly StatusAmbilResolverService $statusAmbilResolver,
        private readonly AkademikTranskripSyncService $transkripSync,
    ) {
    }

    /**
     * @param Collection<int, GradeMigrationRowData> $rows
     */
    public function run(Collection $rows, MigrationBatch $batch, ?\Closure $shouldCancel = null): void
    {
        $sortedRows = $this->sortRows($rows);

        foreach ($sortedRows as $row) {
            if ($shouldCancel !== null && $shouldCancel()) {
                break;
            }

            $result = $this->processRow($row);
            $this->recordResult($batch, $result);
        }
    }

    /**
     * @param Collection<int, GradeMigrationRowData> $rows
     * @return Collection<int, GradeMigrationRowData>
     */
    public function sortRows(Collection $rows): Collection
    {
        // Urutan ASCENDING per (nim, kode_mk, tahun, semester) WAJIB dijaga
        // agar StatusAmbilResolverService dapat mendeteksi percobaan ULANG
        // dengan benar berdasarkan riwayat yang sudah tersimpan.
        return $rows->sort(function (GradeMigrationRowData $a, GradeMigrationRowData $b): int {
            return [$a->nim, $a->kodeMk, $a->tahun, $a->semester]
                <=> [$b->nim, $b->kodeMk, $b->tahun, $b->semester];
        })->values();
    }

    public function processRow(GradeMigrationRowData $row): GradeMigrationRowResult
    {
        try {
            return DB::transaction(function () use ($row): GradeMigrationRowResult {
                $context = $this->resolver->resolve($row);

                $krs = Krs::query()->firstOrCreate(
                    [
                        'mahasiswa_id' => $context->mahasiswa->id,
                        'tahun_akademik_id' => $context->tahunAkademik->id,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'kelas_id' => null,
                        'tgl_krs' => $context->tahunAkademik->tanggal_mulai ?? now(),
                        'status_krs' => 'DISETUJUI',
                        'is_paket_snapshot' => false,
                        'dosen_wali_id' => null,
                        'diajukan_at' => now(),
                        'disetujui_pada' => now(),
                        'is_financial_verified' => true,
                        'total_sks_diambil' => 0,
                    ],
                );

                $existingDetail = KrsDetail::query()
                    ->where('krs_id', $krs->id)
                    ->where('mata_kuliah_id', $context->mataKuliah->id)
                    ->first();

                if ($existingDetail instanceof KrsDetail) {
                    return GradeMigrationRowResult::dilewati(
                        $row,
                        $context->mahasiswa->id,
                        $existingDetail->id,
                    );
                }

                /** @var StatusAmbil $statusAmbil */
                $statusAmbil = $this->statusAmbilResolver->resolve(
                    $context->mahasiswa->id,
                    $context->mataKuliah->id,
                    $context->tahunAkademik,
                );

                $krsDetail = KrsDetail::query()->create([
                    'krs_id' => $krs->id,
                    'jadwal_kuliah_id' => null,
                    'mata_kuliah_id' => $context->mataKuliah->id,
                    'kode_mk_snapshot' => $context->mataKuliah->kode_mk,
                    'nama_mk_snapshot' => $context->mataKuliah->nama_mk,
                    'sks_snapshot' => $context->mataKuliah->sks_default,
                    'activity_type_snapshot' => $context->mataKuliah->activity_type,
                    'ekuivalensi_id' => null,
                    'status_ambil' => $statusAmbil->value,
                    'nilai_angka' => $row->nilaiAngka,
                    'nilai_huruf' => $context->skalaNilai->huruf,
                    'nilai_indeks' => $context->skalaNilai->bobot_indeks,
                    'is_published' => true,
                    'is_locked' => false,
                ]);

                $krs->increment('total_sks_diambil', $context->mataKuliah->sks_default);

                $this->transkripSync->sync($context->mahasiswa, $context->mataKuliah, $krsDetail);

                return GradeMigrationRowResult::berhasil(
                    $row,
                    $context->mahasiswa->id,
                    $krsDetail->id,
                );
            });
        } catch (GradeMigrationResolutionException $e) {
            return GradeMigrationRowResult::gagal($row, $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return GradeMigrationRowResult::gagal($row, 'Kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function recordResult(MigrationBatch $batch, GradeMigrationRowResult $result): void
    {
        MigrationLog::query()->create([
            'migration_batch_id' => $batch->id,
            'row_number' => $result->row->rowNumber,
            'nim' => $result->row->nim,
            'mahasiswa_id' => $result->mahasiswaId,
            'krs_detail_id' => $result->krsDetailId,
            'status' => $result->status,
            'pesan' => $result->pesan,
            'row_data' => $result->row->toArray(),
        ]);

        match ($result->status) {
            MigrationRowStatus::BERHASIL => $batch->increment('total_berhasil'),
            MigrationRowStatus::GAGAL => $batch->increment('total_gagal'),
            MigrationRowStatus::DILEWATI => $batch->increment('total_dilewati'),
        };
    }
}