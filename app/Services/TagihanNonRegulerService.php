<?php

namespace App\Services;

use App\Models\KeuanganKomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\TagihanNonReguler;
use App\Models\TagihanNonRegulerDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TagihanNonRegulerService
{
    /**
     * Tipe komponen biaya yang boleh dipakai untuk tagihan non reguler.
     * Sengaja di-hardcode di sini (bukan cuma di form Filament) supaya
     * aturan bisnis ini tetap berlaku walau service dipanggil dari
     * command/API lain di masa depan.
     */
    private const ALLOWED_TIPE_BIAYA = ['SEKALI', 'INSIDENTAL'];

    /**
     * Generate satu tagihan non reguler beserta detailnya.
     *
     * @param array{
     *     mahasiswa_id: string,
     *     deskripsi: string,
     *     tenggat_waktu: ?string,
     *     created_by: string,
     *     referensi_type?: ?string,
     *     referensi_id?: ?string,
     *     items: array<array{komponen_biaya_id: int, nominal_dasar: float, nominal_diskon?: float}>,
     * } $data
     */
    public function generate(array $data): TagihanNonReguler
    {
        return DB::transaction(function () use ($data) {
            $mahasiswa = $this->validasiMahasiswa($data['mahasiswa_id']);
            $items = $this->validasiKomponenBiaya($data['items'] ?? []);

            $kodeTransaksi = $this->buatKodeTransaksi();

            $totalTagihan = collect($items)->sum(
                fn (array $item) => $item['nominal_dasar'] - ($item['nominal_diskon'] ?? 0)
            );

            $tagihan = TagihanNonReguler::create([
                'mahasiswa_id' => $mahasiswa->id,
                'kode_transaksi' => $kodeTransaksi,
                'deskripsi' => $data['deskripsi'],
                'total_tagihan' => $totalTagihan,
                'total_bayar' => 0,
                'status_bayar' => 'BELUM',
                'referensi_type' => $data['referensi_type'] ?? null,
                'referensi_id' => $data['referensi_id'] ?? null,
                'tenggat_waktu' => $data['tenggat_waktu'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            foreach ($items as $item) {
                /** @var KeuanganKomponenBiaya $komponen */
                $komponen = $item['komponen'];
                $nominalDasar = $item['nominal_dasar'];
                $nominalDiskon = $item['nominal_diskon'] ?? 0;

                TagihanNonRegulerDetail::create([
                    'tagihan_id' => $tagihan->id,
                    'komponen_biaya_id' => $komponen->id,
                    'nama_komponen_snapshot' => $komponen->nama_komponen,
                    'nominal_dasar' => $nominalDasar,
                    'nominal_diskon' => $nominalDiskon,
                    // nominal_tagihan bukan generated column di tabel ini
                    // (lihat ANALISIS.md), jadi wajib dihitung eksplisit
                    // agar konsisten dengan tagihan_mahasiswas_details.
                    'nominal_tagihan' => $nominalDasar - $nominalDiskon,
                    'nominal_terbayar' => 0,
                ]);
            }

            return $tagihan->fresh('details');
        });
    }

    private function validasiMahasiswa(string $mahasiswaId): Mahasiswa
    {
        $mahasiswa = Mahasiswa::find($mahasiswaId);

        if (! $mahasiswa) {
            throw ValidationException::withMessages([
                'mahasiswa_id' => 'Mahasiswa tidak ditemukan.',
            ]);
        }

        return $mahasiswa;
    }

    /**
     * @param array<array{komponen_biaya_id: int, nominal_dasar: float, nominal_diskon?: float}> $items
     * @return array<array{komponen: KeuanganKomponenBiaya, nominal_dasar: float, nominal_diskon: float}>
     */
    private function validasiKomponenBiaya(array $items): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Minimal 1 komponen biaya harus dipilih.',
            ]);
        }

        $komponenIds = collect($items)->pluck('komponen_biaya_id')->unique();

        $komponenList = KeuanganKomponenBiaya::query()
            ->whereIn('id', $komponenIds)
            ->where('is_active', true)
            ->whereIn('tipe_biaya', self::ALLOWED_TIPE_BIAYA)
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($items as $item) {
            $komponen = $komponenList->get($item['komponen_biaya_id']);

            if (! $komponen) {
                throw ValidationException::withMessages([
                    'items' => "Komponen biaya ID {$item['komponen_biaya_id']} tidak valid untuk tagihan non reguler (harus SEKALI/INSIDENTAL dan aktif).",
                ]);
            }

            $result[] = [
                'komponen' => $komponen,
                'nominal_dasar' => (float) $item['nominal_dasar'],
                'nominal_diskon' => (float) ($item['nominal_diskon'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Format: INV-NR-YYYYMM####, reset urut tiap bulan.
     * Menggunakan lockForUpdate() supaya aman dari race condition saat
     * beberapa admin generate tagihan secara bersamaan.
     */
    private function buatKodeTransaksi(): string
    {
        $prefix = 'INV-NR-'.now()->format('Ym');

        $last = TagihanNonReguler::query()
            ->withTrashed()
            ->where('kode_transaksi', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('kode_transaksi')
            ->first();

        $lastSequence = $last
            ? (int) substr($last->kode_transaksi, -4)
            : 0;

        $nextSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$nextSequence}";
    }
}