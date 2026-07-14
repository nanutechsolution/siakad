<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class KeuanganPendingVerifikasiList extends Widget
{
    protected string $view = 'filament.widgets.keuangan-pending-verifikasi-list';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $rows = DB::table('pembayaran_mahasiswas')
            ->join('tagihan_mahasiswas', 'tagihan_mahasiswas.id', '=', 'pembayaran_mahasiswas.tagihan_id')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'tagihan_mahasiswas.mahasiswa_id')
            ->join('ref_person', 'ref_person.id', '=', 'mahasiswas.person_id')
            ->join('ref_status_verifikasi_pembayaran', 'ref_status_verifikasi_pembayaran.id', '=', 'pembayaran_mahasiswas.status_verifikasi_id')
            ->where('ref_status_verifikasi_pembayaran.is_final', 0)
            ->whereNull('pembayaran_mahasiswas.deleted_at')
            ->orderByDesc('pembayaran_mahasiswas.tanggal_bayar')
            ->limit(5)
            ->select([
                'ref_person.nama_lengkap',
                'mahasiswas.nim',
                'pembayaran_mahasiswas.nominal_bayar',
                'pembayaran_mahasiswas.tanggal_bayar',
                'pembayaran_mahasiswas.metode_pembayaran',
            ])
            ->get();

        return [
            'rows' => $rows,
        ];
    }
}
