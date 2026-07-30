<?php

namespace App\Http\Controllers;

use App\Models\TagihanMahasiswa;
use App\Models\TagihanNonReguler;
use App\Services\Pembayaran\Channels\MidtransChannel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * ASUMSI yang perlu Anda sesuaikan:
 * - auth()->user()->mahasiswa_id ada dan menunjuk ke Mahasiswa yang login
 *   (pola yang sama dipakai di TagihanNonRegulerResource::getEloquentQuery()).
 * - Middleware 'auth' di route cukup — ganti ke middleware/guard panel
 *   Filament mahasiswa Anda kalau beda.
 */
class MidtransCheckoutController extends Controller
{
    public function __construct(
        private readonly MidtransChannel $channel,
    ) {}

    public function show(Request $request, string $tagihanType, string $tagihanId): Response
    {
        $mahasiswaId = Auth::user()->person?->mahasiswa?->id;
        abort_if(! $mahasiswaId, 403);

        $tagihan = $this->resolveTagihanMilikSendiri($tagihanType, $tagihanId, $mahasiswaId);

        $mahasiswa = $tagihan->mahasiswa;

        $hasil = $this->channel->initiate($tagihan, $mahasiswaId, [
            'first_name' => $mahasiswa->person->nama_lengkap ?? 'Mahasiswa',
            'email' => $mahasiswa->person->email ?? 'noemail@example.com',
            'phone' => $mahasiswa->person->no_hp ?? '-',
        ]);

        return response()->view('midtrans.checkout', [
            'snapToken' => $hasil['snap_token'],
            'clientKey' => config('midtrans.client_key'),
            'isProduction' => config('midtrans.is_production'),
            'kembaliUrl' => route('midtrans.result', [
                'orderId' => $hasil['order_id'],
            ])
        ]);
    }

    private function resolveTagihanMilikSendiri(string $tagihanType, string $tagihanId, string $mahasiswaId): TagihanMahasiswa|TagihanNonReguler
    {
        $modelClass = Relation::getMorphedModel($tagihanType);

        abort_if(
            $modelClass === null || ! in_array($modelClass, [TagihanMahasiswa::class, TagihanNonReguler::class], true),
            404,
        );

        $tagihan = $modelClass::whereKey($tagihanId)->first();

        abort_if(! $tagihan, 404);
        abort_if($tagihan->mahasiswa_id !== $mahasiswaId, 403, 'Tagihan ini bukan milik Anda.');

        return $tagihan;
    }
}
