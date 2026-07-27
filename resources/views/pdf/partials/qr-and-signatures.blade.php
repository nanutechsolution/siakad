@if($qrCodeBase64 ?? null)
    <table style="width:100%; margin-top:30px; font-size:10px;">
        <tr>
            <td width="70%"></td>
            <td width="30%" class="text-center">
                <img src="{{ $qrCodeBase64 }}" width="70"><br>
                <span style="font-size:7px;">Pindai untuk verifikasi keaslian</span>
            </td>
        </tr>
    </table>
@endif

<table style="width:100%; margin-top:20px; font-size:10px;">
    <tr>
        @foreach($signers as $signer)
            <td class="text-center" style="width:{{ 100 / count($signers) }}%;">
                <p>{{ $signer['label'] }}</p>
                <div style="height:50px;"></div>
                <p><strong>{{ $signer['namaLengkap'] }}</strong></p>
            </td>
        @endforeach
    </tr>
</table>