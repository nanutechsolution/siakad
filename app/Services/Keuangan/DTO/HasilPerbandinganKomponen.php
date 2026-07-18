<?php

declare(strict_types=1);

namespace App\Services\Keuangan\DTO;

/**
 * Hasil perbandingan 1 mahasiswa antara komponen skema tarif aktif dengan
 * komponen yang sudah ada di tagihannya.
 *
 * Value object murni - tidak menyentuh DB, tidak tahu apa-apa soal Eloquent.
 * Ini yang membuatnya bisa dipakai identik oleh alur Preview (di request
 * thread) maupun alur Eksekusi (di job), sehingga hasil preview dijamin
 * konsisten dengan hasil eksekusi selama data belum berubah di antara
 * keduanya.
 */
final class HasilPerbandinganKomponen
{
    /**
     * @param array<int, array{komponen_biaya_id:int,nama_komponen:string,nominal:float}> $toAdd
     *        Komponen yang ada di skema tapi belum ada di tagihan -> akan di-insert.
     * @param array<int, array{tagihan_detail_id:int,komponen_biaya_id:int,nama_komponen:string,nominal_existing:float,nominal_skema_baru:float}> $toReview
     *        Komponen ada di keduanya tapi nominal dasar berbeda -> TIDAK diubah, hanya dilaporkan.
     * @param array<int, array{tagihan_detail_id:int,komponen_biaya_id:int,nama_komponen_snapshot:string,nominal_existing:float}> $toWarn
     *        Komponen ada di tagihan tapi sudah tidak ada di skema aktif -> TIDAK dihapus, hanya warning.
     * @param array<int, array{komponen_biaya_id:int}> $unchanged
     *        Komponen sudah cocok persis -> tidak ada aksi apa pun.
     */
    public function __construct(
        public readonly array $toAdd = [],
        public readonly array $toReview = [],
        public readonly array $toWarn = [],
        public readonly array $unchanged = [],
    ) {}

    public function adaPerubahan(): bool
    {
        return count($this->toAdd) > 0 || count($this->toReview) > 0 || count($this->toWarn) > 0;
    }

    public function toSummaryArray(): array
    {
        return [
            'jumlah_ditambah' => count($this->toAdd),
            'jumlah_review' => count($this->toReview),
            'jumlah_warning' => count($this->toWarn),
            'jumlah_tidak_berubah' => count($this->unchanged),
        ];
    }
}
