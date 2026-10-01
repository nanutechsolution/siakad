<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use App\Settings\KampusSettings;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaExport extends DefaultValueBinder implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnFormatting,
    WithCustomValueBinder,
    WithCustomStartCell,
    WithDrawings,
    WithEvents,
    WithTitle,
    ShouldAutoSize
{
    /**
     * Kolom yang wajib disimpan sebagai teks (NIM, NIK, NISN)
     * agar angka nol di depan tidak hilang dan tidak berubah menjadi notasi ilmiah.
     */
    protected const KOLOM_TEKS = ['A', 'D', 'E'];

    protected const JUDUL = 'DAFTAR DATA MAHASISWA';

    protected KampusSettings $kampus;

    /** @var array<int, string> Baris teks kop (nama, akreditasi, alamat, kontak). */
    protected array $barisKop = [];

    protected string $kolomAkhir;

    protected int $barisHeading;

    protected string $keterangan;

    /**
     * @param  array<int, string>  $infoFilter  Keterangan filter aktif yang tampil di bawah judul.
     */
    public function __construct(protected Builder $query, array $infoFilter = [])
    {
        $this->kampus = app(KampusSettings::class);

        $this->barisKop = array_values(array_filter([
            mb_strtoupper((string) $this->kampus->nama),
            $this->kampus->akreditasi ? mb_strtoupper((string) $this->kampus->akreditasi) : null,
            $this->kampus->alamat ?: null,
            'Telepon: ' . ($this->kampus->telepon ?: '-')
                . '  |  Email: ' . ($this->kampus->email ?: '-')
                . '  |  Website: ' . ($this->kampus->website ?: '-'),
        ], fn($baris) => filled($baris)));

        $this->kolomAkhir = Coordinate::stringFromColumnIndex(count(MahasiswaExportMapper::headings()));

        // kop + garis pemisah + judul + keterangan + baris kosong, lalu heading tabel
        $this->barisHeading = count($this->barisKop) + 5;

        $total = (clone $query)->reorder()->count();

        $this->keterangan = implode('  |  ', array_merge(
            empty($infoFilter) ? ['Semua data'] : $infoFilter,
            [
                'Total: ' . number_format($total, 0, ',', '.') . ' mahasiswa',
                'Dicetak: ' . now()->timezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y, H:i') . ' WITA',
                'Oleh: ' . (auth()->user()?->name ?? '-'),
            ]
        ));
    }

    public function startCell(): string
    {
        return 'A' . $this->barisHeading;
    }

    public function query(): Builder
    {
        return $this->query->with(MahasiswaExportMapper::RELASI);
    }

    public function headings(): array
    {
        return MahasiswaExportMapper::headings();
    }

    /**
     * @param  Mahasiswa  $row
     */
    public function map($row): array
    {
        return MahasiswaExportMapper::baris($row);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), self::KOLOM_TEKS, true) && is_scalar($value)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * Logo kampus di pojok kiri atas. Hanya mendukung PNG/JPG/GIF
     * (SVG tidak didukung oleh Excel). Jika logo tidak ada, dilewati.
     */
    public function drawings(): array
    {
        $path = $this->kampus->logo_path
            ? storage_path('app/public/' . $this->kampus->logo_path)
            : null;

        if (! $path || ! file_exists($path)) {
            return [];
        }

        if (! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif'], true)) {
            return [];
        }

        $logo = new Drawing();
        $logo->setName('Logo');
        $logo->setDescription('Logo Kampus');
        $logo->setPath($path);
        $logo->setHeight(75);
        $logo->setCoordinates('A1');
        $logo->setOffsetX(6);
        $logo->setOffsetY(4);

        return [$logo];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            $this->barisHeading => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $akhir = $this->kolomAkhir;
                $jumlahKop = count($this->barisKop);

                // ── Teks kop (digabung A:akhir, rata tengah; logo mengambang di kiri) ──
                foreach ($this->barisKop as $i => $teks) {
                    $baris = $i + 1;
                    $sheet->mergeCells("A{$baris}:{$akhir}{$baris}");
                    $sheet->setCellValue("A{$baris}", $teks);

                    $style = $sheet->getStyle("A{$baris}");
                    $style->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    if ($i === 0) {
                        $style->getFont()->setName('Times New Roman')->setBold(true)->setSize(16);
                        $sheet->getRowDimension($baris)->setRowHeight(26);
                    } elseif ($i === 1 && $this->kampus->akreditasi) {
                        $style->getFont()->setBold(true)->setSize(10);
                        $sheet->getRowDimension($baris)->setRowHeight(16);
                    } else {
                        $style->getFont()->setSize(9);
                        $sheet->getRowDimension($baris)->setRowHeight(15);
                    }
                }

                // ── Garis ganda (tebal di bawah kop, tipis di baris pemisah) ──
                $sheet->getStyle("A{$jumlahKop}:{$akhir}{$jumlahKop}")
                    ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

                $pemisah = $jumlahKop + 1;
                $sheet->getRowDimension($pemisah)->setRowHeight(4);
                $sheet->getStyle("A{$pemisah}:{$akhir}{$pemisah}")
                    ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

                // ── Judul dokumen ──
                $judul = $jumlahKop + 2;
                $sheet->mergeCells("A{$judul}:{$akhir}{$judul}");
                $sheet->setCellValue("A{$judul}", self::JUDUL);
                $sheet->getStyle("A{$judul}")->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle("A{$judul}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension($judul)->setRowHeight(24);

                // ── Keterangan filter ──
                $info = $jumlahKop + 3;
                $sheet->mergeCells("A{$info}:{$akhir}{$info}");
                $sheet->setCellValue("A{$info}", $this->keterangan);
                $sheet->getStyle("A{$info}")->getFont()->setSize(9)->getColor()->setRGB('475569');
                $sheet->getStyle("A{$info}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getRowDimension($info)->setRowHeight(
                    14 * max(1, (int) ceil(mb_strlen($this->keterangan) / 150))
                );

                // ── Tabel data: border, freeze, filter ──
                $heading = $this->barisHeading;
                $terakhir = max($sheet->getHighestRow(), $heading);

                $sheet->getRowDimension($heading)->setRowHeight(20);

                $sheet->getStyle("A{$heading}:{$akhir}{$terakhir}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D1D5DB');

                $sheet->getStyle("A" . ($heading + 1) . ":{$akhir}{$terakhir}")
                    ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                $sheet->freezePane('A' . ($heading + 1));
                $sheet->setAutoFilter("A{$heading}:{$akhir}{$terakhir}");

                // ── Pengaturan cetak: A4 landscape, lebar 1 halaman, heading berulang ──
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setRowsToRepeatAtTopByStartAndEnd($heading, $heading);
            },
        ];
    }

    public function title(): string
    {
        return 'Data Mahasiswa';
    }
}
