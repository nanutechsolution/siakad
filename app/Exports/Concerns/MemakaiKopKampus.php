<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use App\Settings\KampusSettings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Kop surat kampus untuk export Excel: logo di kiri, teks kop rata tengah,
 * garis ganda, lalu judul dokumen. Data diambil dari KampusSettings.
 *
 * Class pemakai wajib mengimplementasikan WithDrawings dan WithEvents,
 * memanggil siapkanKop() di constructor, serta memanggil gambarKop() di AfterSheet.
 */
trait MemakaiKopKampus
{
    protected KampusSettings $kampus;

    /** @var array<int, string> */
    protected array $barisKop = [];

    protected bool $adaAkreditasi = false;

    protected function siapkanKop(): void
    {
        $this->kampus = app(KampusSettings::class);
        $this->adaAkreditasi = filled($this->kampus->akreditasi);

        $baris = [mb_strtoupper((string) $this->kampus->nama)];

        if ($this->adaAkreditasi) {
            $baris[] = mb_strtoupper((string) $this->kampus->akreditasi);
        }

        if (filled($this->kampus->alamat)) {
            $baris[] = (string) $this->kampus->alamat;
        }

        $baris[] = 'Telepon: ' . ($this->kampus->telepon ?: '-')
            . '  |  Email: ' . ($this->kampus->email ?: '-')
            . '  |  Website: ' . ($this->kampus->website ?: '-');

        $this->barisKop = $baris;
    }

    protected function jumlahBarisKop(): int
    {
        return count($this->barisKop);
    }

    /**
     * Logo kampus di pojok kiri atas. Hanya mendukung PNG/JPG/GIF
     * (SVG tidak didukung Excel). Jika logo tidak ada, dilewati.
     *
     * @return array<int, Drawing>
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

    /**
     * Menggambar kop (baris 1..n), garis ganda (n dan n+1), dan judul (n+2).
     *
     * @return int Nomor baris judul. Konten berikutnya dimulai di baris sesudahnya.
     */
    protected function gambarKop(Worksheet $sheet, string $kolomAkhir, string $judul): int
    {
        $jumlah = $this->jumlahBarisKop();

        foreach ($this->barisKop as $i => $teks) {
            $baris = $i + 1;

            $sheet->mergeCells("A{$baris}:{$kolomAkhir}{$baris}");
            $sheet->setCellValueExplicit("A{$baris}", $teks, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $style = $sheet->getStyle("A{$baris}");
            $style->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            if ($i === 0) {
                $style->getFont()->setName('Times New Roman')->setBold(true)->setSize(16);
                $sheet->getRowDimension($baris)->setRowHeight(26);
            } elseif ($i === 1 && $this->adaAkreditasi) {
                $style->getFont()->setBold(true)->setSize(10);
                $sheet->getRowDimension($baris)->setRowHeight(16);
            } else {
                $style->getFont()->setSize(9);
                $sheet->getRowDimension($baris)->setRowHeight(15);
            }
        }

        // Garis ganda: tebal di bawah kop, tipis di baris pemisah
        $sheet->getStyle("A{$jumlah}:{$kolomAkhir}{$jumlah}")
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

        $pemisah = $jumlah + 1;
        $sheet->getRowDimension($pemisah)->setRowHeight(4);
        $sheet->getStyle("A{$pemisah}:{$kolomAkhir}{$pemisah}")
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Judul dokumen
        $barisJudul = $jumlah + 2;
        $sheet->mergeCells("A{$barisJudul}:{$kolomAkhir}{$barisJudul}");
        $sheet->setCellValueExplicit("A{$barisJudul}", $judul, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->getStyle("A{$barisJudul}")->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle("A{$barisJudul}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($barisJudul)->setRowHeight(24);

        return $barisJudul;
    }
}
