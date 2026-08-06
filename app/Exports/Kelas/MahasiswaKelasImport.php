<?php


namespace App\Exports\Kelas;

use App\Models\Mahasiswa;
use App\Services\MahasiswaPlottingService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class MahasiswaKelasImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows
{
    public function __construct(
        protected int $kelasId,
        protected MahasiswaPlottingService $plottingService
    ) {}
    public function prepareForValidation(array $data, int $index): array
    {
        if (isset($data['nim'])) {
            $data['nim'] = preg_replace('/[^0-9]/', '', (string) $data['nim']);
        }

        return $data;
    }

    public function model(array $row)
    {
        $nim = preg_replace('/[^0-9]/', '', (string) $row['nim']);

        $mahasiswa = Mahasiswa::where('nim', $nim)->first();

        if (! $mahasiswa) {
            throw new \RuntimeException("NIM {$nim} tidak ditemukan.");
        }

        // Parse tanggal masuk
        $tanggalMasuk = $this->parseDate($row['tanggal_masuk_yyyy_mm_dd'] ?? null) ?? now();

        // Eksekusi via domain service
        $this->plottingService->plot(
            mahasiswaId: $mahasiswa->id,
            kelasId: $this->kelasId,
            tanggalMasuk: $tanggalMasuk
        );

        return null; // Logic di-handle oleh Domain Service
    }

    public function rules(): array
    {
        return [
            '*.nim' => ['required', 'exists:mahasiswas,nim'],
            '*.tanggal_masuk_yyyy_mm_dd' => ['nullable'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.nim.exists' => 'NIM :input tidak ditemukan di database.',
        ];
    }

    public function chunkSize(): int
    {
        return 200; // Mencegah memory peak pada dataset besar
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
