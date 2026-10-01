<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\MemakaiKopKampus;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Base class export Excel ber-kop kampus.
 *
 * Susunan sheet:
 *   kop (n baris) -> garis ganda -> judul -> keterangan (label, lalu teks tengah)
 *   -> 1 baris kosong -> heading tabel -> data.
 *
 * Class turunan WAJIB:
 *   1. memanggil parent::__construct() di constructor
 *   2. mengimplementasikan judul() dan jumlahKolom()
 *   3. memakai salah satu concern data (FromArray / FromQuery / FromCollection).
 *      Jika pakai FromArray, baris pertama array harus berisi heading.
 *      Jika pakai FromQuery/FromCollection, tambahkan WithHeadings.
 *
 * Hook opsional (override sesuai kebutuhan): keteranganLabel(), keteranganTengah(),
 * minKolomKop(), kolomTeks(), kolomTengah(), autoFilter(), tinggiBarisData(),
 * warnaHeading(), warnaTeksHeading(), warnaBorder(), alignVertikalData(), orientasi().
 */
abstract class BaseKopExport extends DefaultValueBinder implements
    ShouldAutoSize,
    WithColumnFormatting,
    WithCustomStartCell,
    WithCustomValueBinder,
    WithDrawings,
    WithEvents,
    WithStyles
{
    use MemakaiKopKampus;

    private ?array $cacheKeteranganLabel = null;

    private ?array $cacheKeteranganTengah = null;

    public function __construct()
    {
        $this->siapkanKop();
    }

    /** Judul dokumen di bawah kop. */
    abstract protected function judul(): string;

    /** Jumlah kolom tabel (sama dengan jumlah heading). */
    abstract protected function jumlahKolom(): int;

    /**
     * Keterangan berpasangan label => nilai (label di kolom A:C, nilai di D sampai akhir).
     *
     * @return array<string, string>
     */
    protected function keteranganLabel(): array
    {
        return [];
    }

    /**
     * Baris keterangan rata tengah (misalnya filter aktif, total, waktu cetak).
     *
     * @return array<int, string>
     */
    protected function keteranganTengah(): array
    {
        return [];
    }

    /** Lebar minimum kop dalam jumlah kolom. */
    protected function minKolomKop(): int
    {
        return 6;
    }

    /**
     * Huruf kolom yang dipaksa bertipe teks (NIM, NIK, kode, dan sebagainya).
     *
     * @return array<int, string>
     */
    protected function kolomTeks(): array
    {
        return [];
    }

    /**
     * Huruf kolom data yang rata tengah.
     *
     * @return array<int, string>
     */
    protected function kolomTengah(): array
    {
        return [];
    }

    protected function autoFilter(): bool
    {
        return false;
    }

    protected function tinggiBarisData(): ?float
    {
        return null;
    }

    protected function warnaHeading(): string
    {
        return '4F46E5';
    }

    protected function warnaTeksHeading(): string
    {
        return 'FFFFFF';
    }

    protected function warnaBorder(): string
    {
        return 'D1D5DB';
    }

    protected function alignVertikalData(): string
    {
        return Alignment::VERTICAL_CENTER;
    }

    protected function orientasi(): string
    {
        return PageSetup::ORIENTATION_LANDSCAPE;
    }

    /** Nomor baris heading tabel. */
    protected function barisHeading(): int
    {
        // kop + pemisah + judul (n+2), keterangan mulai n+3, lalu 1 baris kosong
        return $this->jumlahBarisKop() + 3 + $this->jumlahBarisKeterangan() + 1;
    }

    public function startCell(): string
    {
        return 'A' . $this->barisHeading();
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach ($this->kolomTeks() as $kolom) {
            $formats[$kolom] = NumberFormat::FORMAT_TEXT;
        }

        return $formats;
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), $this->kolomTeks(), true) && is_scalar($value)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        $akhir = Coordinate::stringFromColumnIndex($this->jumlahKolom());
        $heading = $this->barisHeading();

        return [
            "A{$heading}:{$akhir}{$heading}" => [
                'font' => ['bold' => true, 'color' => ['rgb' => $this->warnaTeksHeading()]],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->warnaHeading()],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->susunSheet($event->sheet->getDelegate());
            },
        ];
    }

    private function susunSheet(Worksheet $sheet): void
    {
        $jumlahKolom = $this->jumlahKolom();
        $akhirTabel = Coordinate::stringFromColumnIndex($jumlahKolom);
        $kolomKop = max($jumlahKolom, $this->minKolomKop());
        $akhirKop = Coordinate::stringFromColumnIndex($kolomKop);

        // ── Kop + judul ──
        $barisJudul = $this->gambarKop($sheet, $akhirKop, $this->judul());
        $baris = $barisJudul + 1;

        // ── Keterangan berpasangan ──
        foreach ($this->labelKeterangan() as $label => $nilai) {
            $sheet->mergeCells("A{$baris}:C{$baris}");
            $sheet->setCellValueExplicit("A{$baris}", (string) $label, DataType::TYPE_STRING);
            $sheet->getStyle("A{$baris}")->getFont()->setBold(true)->setSize(10);

            $sheet->mergeCells("D{$baris}:{$akhirKop}{$baris}");
            $sheet->setCellValueExplicit(
                "D{$baris}",
                ': ' . (trim((string) $nilai) !== '' ? $nilai : '-'),
                DataType::TYPE_STRING
            );
            $sheet->getStyle("D{$baris}")->getFont()->setSize(10);
            $sheet->getStyle("D{$baris}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setWrapText(true);

            $baris++;
        }

        // ── Keterangan rata tengah ──
        foreach ($this->tengahKeterangan() as $teks) {
            $sheet->mergeCells("A{$baris}:{$akhirKop}{$baris}");
            $sheet->setCellValueExplicit("A{$baris}", $teks, DataType::TYPE_STRING);
            $sheet->getStyle("A{$baris}")->getFont()->setSize(9)->getColor()->setRGB('475569');
            $sheet->getStyle("A{$baris}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getRowDimension($baris)->setRowHeight(
                14 * max(1, (int) ceil(mb_strlen($teks) / ($kolomKop * 14)))
            );

            $baris++;
        }

        // ── Tabel data ──
        $heading = $this->barisHeading();
        $terakhir = max($sheet->getHighestRow(), $heading);

        $sheet->getRowDimension($heading)->setRowHeight(22);

        $sheet->getStyle("A{$heading}:{$akhirTabel}{$terakhir}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB($this->warnaBorder());

        if ($terakhir > $heading) {
            $awalData = $heading + 1;

            $sheet->getStyle("A{$awalData}:{$akhirTabel}{$terakhir}")
                ->getAlignment()->setVertical($this->alignVertikalData());

            foreach ($this->kolomTengah() as $kolom) {
                $sheet->getStyle("{$kolom}{$awalData}:{$kolom}{$terakhir}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            if ($this->tinggiBarisData() !== null) {
                for ($r = $awalData; $r <= $terakhir; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight($this->tinggiBarisData());
                }
            }
        }

        $sheet->freezePane('A' . ($heading + 1));

        if ($this->autoFilter()) {
            $sheet->setAutoFilter("A{$heading}:{$akhirTabel}{$terakhir}");
        }

        // ── Pengaturan cetak ──
        $sheet->getPageSetup()
            ->setOrientation($this->orientasi())
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($heading, $heading);
        $sheet->getPageMargins()->setLeft(0.25)->setRight(0.25);
    }

    private function jumlahBarisKeterangan(): int
    {
        return count($this->labelKeterangan()) + count($this->tengahKeterangan());
    }

    /** @return array<string, string> */
    private function labelKeterangan(): array
    {
        return $this->cacheKeteranganLabel ??= $this->keteranganLabel();
    }

    /** @return array<int, string> */
    private function tengahKeterangan(): array
    {
        return $this->cacheKeteranganTengah ??= array_values($this->keteranganTengah());
    }
}
