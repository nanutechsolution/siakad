<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dokumen')</title>
    <style>
        @page {
            margin: 90px 40px 60px 40px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 70px;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 9px;
            color: #555;
            border-top: 1px solid #999;
            padding-top: 4px;
        }

        .kop {
            width: 100%;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 6px;
            margin-bottom: 4px;
        }

        .kop table {
            width: 100%;
        }

        .kop .logo {
            width: 60px;
        }

        .kop .institusi h1 {
            font-size: 14px;
            margin: 0;
        }

        .kop .institusi p {
            font-size: 9px;
            margin: 0;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 10px;
        }

        table.data th {
            background-color: #eee;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    @php $kopSurat = app(\App\Services\Pdf\KopSuratResolver::class)->resolve(); @endphp

    <header>
        <div class="kop">
            <table>
                <tr>
                    <td class="logo">
                        @if(
                        !empty($kopSurat['logoAbsolutePath']) &&
                        file_exists($kopSurat['logoAbsolutePath'])
                        )
                        <img src="file://{{ $kopSurat['logoAbsolutePath'] }}" width="55">
                        @endif
                    </td>
                    <td class="institusi">
                        <h1>{{ $kopSurat['nama'] }} @if($kopSurat['akreditasi'])(Akreditasi {{ $kopSurat['akreditasi'] }})@endif</h1>
                        <p>{{ $kopSurat['alamat'] }}</p>
                        <p>Telp: {{ $kopSurat['telepon'] }} | Email: {{ $kopSurat['email'] }} | {{ $kopSurat['website'] }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </header>

    <footer>
        Dicetak melalui SIAKAD pada {{ now()->translatedFormat('d F Y H:i') }} — Dokumen ini sah tanpa tanda tangan basah selama dicetak dari sistem resmi.
    </footer>

    @yield('content')
</body>

</html>