<?php

declare(strict_types=1);

namespace App\Exports\Absensi;

use App\DataTransferObjects\Absensi\AbsensiDocumentData;
use App\Exports\Concerns\MemakaiKopKampus;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiDocumentExport implements FromArray, ShouldAutoSize, WithCustomStartCell, WithDrawings, WithEvents, WithStyles
{
    use MemakaiKopKampus;

    private const JUDUL = 'DAFTAR HADIR PERKULIAHAN';

    /** Lebar minimum kop (jumlah kolom) agar teks kop tidak terlalu sempit pada mode manual. */
    private const MIN_KOLOM_KOP = 6;

    private const JUMLAH_BARIS_INFO = 6;

    private int $jumlahKolom;

    private int $barisHeading;

    public function __construct(private readonly AbsensiDocumentData $document)
    {
        $this->siapkanKop();

        $this->jumlahKolom = count($this->headers());

        // kop + pemisah + judul + 6 baris keterangan + 1 baris kosong, lalu heading tabel
        $this->barisHeading = $this->jumlahBarisKop() + 2 + self::JUMLAH_BARIS_INFO + 2;
    }

    public function startCell(): string
    {
        return 'A' . $this->barisHeading;
    }

    /**
     * @return array<int, string>
     */
    private function headers(): array
    {
        return match ($this->document->mode) {
            'online' => ['No', 'NIM', 'Nama Mahasiswa', 'Status', 'Jam', 'Keterangan'],
            'template' => array_merge(
                ['No', 'NIM', 'Nama Mahasiswa'],
                array_map(fn (int $number) => 'P' . $number, $this->document->pertemuan)
            ),
            default => ['No', 'NIM', 'Nama Mahasiswa', 'Tanda Tangan / Keterangan'],
        };
    }

    public function array(): array
    {
        $rows = array_map(function (array $row) {
            return match ($this->document->mode) {
                'online' => [$row['no'], $row['nim'], $row['nama'], $row['status'], $row['waktu'], $row['keterangan']],
                'template' => array_merge(
                    [$row['no'], $row['nim'], $row['nama']],
                    array_fill(0, count($this->document->pertemuan), '')
                ),
                default => [$row['no'], $row['nim'], $row['nama'], ''],
            };
        }, $this->document->rows);

        return array_merge([$this->headers()], $rows);
    }

    public function styles(Worksheet $sheet): array
    {
        $akhirTabel = Coordinate::stringFromColumnIndex($this->jumlahKolom);

        return [
            "A{$this->barisHeading}:{$akhirTabel}{$this->barisHeading}" => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8EEF5'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $akademik = $this->document->akademik;

                $akhirTabel = Coordinate::stringFromColumnIndex($this->jumlahKolom);
                $akhirKop = Coordinate::stringFromColumnIndex(max($this->jumlahKolom, self::MIN_KOLOM_KOP));

                // ── Kop + judul ──
                $barisJudul = $this->gambarKop($sheet, $akhirKop, self::JUDUL);

                // ── Keterangan dokumen (label A:C, nilai D:akhir) ──
                $info = [
                    'Tahun Akademik' => trim(($akademik['tahun_akademik'] ?? '') . ' - ' . ($akademik['semester'] ?? ''), ' -'),
                    'Program Studi' => (string) ($akademik['prodi'] ?? ''),
                    'Mata Kuliah' => trim(($akademik['kode_mk'] ?? '') . ' - ' . ($akademik['mata_kuliah'] ?? ''), ' -'),
                    'Kelas / SKS' => trim(($akademik['kelas'] ?? '') . ' / ' . ($akademik['sks'] ?? ''), ' /'),
                    'Dosen' => (string) ($akademik['dosen'] ?? ''),
                    'Pertemuan / Tanggal' => trim(
                        (! empty($akademik['pertemuan']) ? 'P' . $akademik['pertemuan'] : '') . ' / ' . ($akademik['tanggal'] ?? ''),
                        ' /'
                    ),
                ];

                $baris = $barisJudul + 1;

                foreach ($info as $label => $nilai) {
                    $sheet->mergeCells("A{$baris}:C{$baris}");
                    $sheet->setCellValueExplicit("A{$baris}", $label, DataType::TYPE_STRING);
                    $sheet->getStyle("A{$baris}")->getFont()->setBold(true)->setSize(10);

                    $sheet->mergeCells("D{$baris}:{$akhirKop}{$baris}");
                    $sheet->setCellValueExplicit("D{$baris}", ': ' . ($nilai !== '' ? $nilai : '-'), DataType::TYPE_STRING);
                    $sheet->getStyle("D{$baris}")->getFont()->setSize(10);
                    $sheet->getStyle("D{$baris}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);

                    $baris++;
                }

                // ── Tabel data ──
                $heading = $this->barisHeading;
                $terakhir = max($sheet->getHighestRow(), $heading);

                $sheet->getRowDimension($heading)->setRowHeight(22);

                $sheet->getStyle("A{$heading}:{$akhirTabel}{$terakhir}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('9CA3AF');

                if ($terakhir > $heading) {
                    $sheet->getStyle('A' . ($heading + 1) . ":A{$terakhir}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle('A' . ($heading + 1) . ":{$akhirTabel}{$terakhir}")
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    // Tinggi baris lebih lega untuk kolom tanda tangan / pertemuan
                    if (in_array($this->document->mode, ['manual', 'template'], true)) {
                        for ($r = $heading + 1; $r <= $terakhir; $r++) {
                            $sheet->getRowDimension($r)->setRowHeight(22);
                        }
                    }
                }

                $sheet->freezePane('A' . ($heading + 1));

                // ── Pengaturan cetak ──
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setRowsToRepeatAtTopByStartAndEnd($heading, $heading);
                $sheet->getPageMargins()->setLeft(0.25)->setRight(0.25);
            },
        ];
    }
}