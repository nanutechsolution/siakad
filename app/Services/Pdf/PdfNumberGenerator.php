<?php

namespace App\Services\Pdf;

use App\Enums\Pdf\PdfDocumentType;
use App\Models\PdfNumberSequence;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PdfNumberGenerator
{
    public function __construct(protected PdfDocumentTypeRegistry $registry) {}

    public function generate(PdfDocumentType $type, ?string $kodeUnit = null, ?int $tahun = null): string
    {
        $definition = $this->registry->get($type);

        if (! ($definition['requires_number'] ?? false)) {
            throw new RuntimeException("Jenis dokumen [{$type->value}] tidak dikonfigurasi untuk memakai nomor resmi.");
        }

        $kodeUnit ??= 'PUSAT';
        $tahun ??= (int) now()->format('Y');

        // DB::transaction dengan retry (attempts=3) untuk menangani deadlock
        // saat dua request membuat sequence baru bersamaan (dilindungi juga
        // oleh unique constraint document_type+kode_unit+periode_tahun).
        return DB::transaction(function () use ($type, $definition, $kodeUnit, $tahun) {
            PdfNumberSequence::query()->firstOrCreate(
                [
                    'document_type' => $type->value,
                    'kode_unit' => $kodeUnit,
                    'periode_tahun' => $tahun,
                ],
                [
                    'last_sequence' => 0,
                    'format_template' => $definition['nomor_format'],
                ]
            );

            $sequence = PdfNumberSequence::query()
                ->where('document_type', $type->value)
                ->where('kode_unit', $kodeUnit)
                ->where('periode_tahun', $tahun)
                ->lockForUpdate()
                ->first();

            $nextSequence = $sequence->last_sequence + 1;
            $sequence->update(['last_sequence' => $nextSequence]);

            return $this->format($sequence->format_template, [
                'SEQ' => $nextSequence,
                'KODE_JENIS' => $definition['kode_jenis'] ?? strtoupper($type->value),
                'KODE_UNIT' => $kodeUnit,
                'BULAN_ROMAWI' => $this->toRoman((int) now()->format('n')),
                'TAHUN' => $tahun,
            ]);
        }, 3);
    }

    protected function format(string $template, array $tokens): string
    {
        return preg_replace_callback('/\{(\w+)(?::(\d+))?\}/', function ($matches) use ($tokens) {
            $key = $matches[1];
            $pad = $matches[2] ?? null;
            $value = (string) ($tokens[$key] ?? '');

            return $pad ? str_pad($value, (int) $pad, '0', STR_PAD_LEFT) : $value;
        }, $template);
    }

    protected function toRoman(int $month): string
    {
        $map = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return $map[$month - 1] ?? (string) $month;
    }
}
