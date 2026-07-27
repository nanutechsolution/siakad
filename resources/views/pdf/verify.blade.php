<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Dokumen — SIAKAD</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 480px;
            margin: 40px auto;
            padding: 0 16px;
            color: #1a1a1a;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: bold;
        }

        .valid {
            background: #dcfce7;
            color: #166534;
        }

        .invalid {
            background: #fee2e2;
            color: #991b1b;
        }

        table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
        }

        td {
            padding: 6px 4px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <h2>Verifikasi Dokumen SIAKAD</h2>

    @if($valid)
    <span class="badge valid">&#10003; Dokumen Sah</span>

    <table>
        <tr>
            <td>Jenis Dokumen</td>
            <td>{{ $documentType }}</td>
        </tr>
        <tr>
            <td>Nomor Dokumen</td>
            <td>{{ $nomorDokumen }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{{ strtoupper($status) }}</td>
        </tr>
        <tr>
            <td>Diterbitkan Pada</td>
            <td>{{ \Carbon\Carbon::parse($generatedAt)->translatedFormat('d F Y H:i') }}</td>
        </tr>
    </table>

    @if($signatures->isNotEmpty())
    <h4 style="margin-top:20px;">Ditandatangani Oleh</h4>
    <table>
        @foreach($signatures as $signature)
        <tr>
            <td>{{ $signature->nama_penandatangan_snapshot }}<br><small>{{ $signature->jabatan_snapshot }}</small></td>
            <td>{{ \Carbon\Carbon::parse($signature->signed_at)->translatedFormat('d M Y H:i') }}</td>
        </tr>
        @endforeach
    </table>
    @endif
    @else
    <span class="badge invalid">&#10007; Dokumen Tidak Ditemukan / Tidak Sah</span>
    <p style="margin-top:16px; font-size:14px; color:#555;">
        Nomor/kode dokumen yang Anda pindai tidak ditemukan dalam sistem kami, atau dokumen ini telah dicabut.
    </p>
    @endif
</body>

</html>