<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $judul ?? 'Dokumen Pembimbing Akademik' }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        .kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .kop h1 {
            font-size: 14px;
            margin: 0;
            text-transform: uppercase;
        }

        .kop p {
            font-size: 10px;
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #999;
            padding: 4px 6px;
            font-size: 10px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background-color: #f0f0f0;
        }

        .ttd {
            margin-top: 40px;
            width: 240px;
            float: right;
            text-align: center;
        }

        .ttd .nama {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .judul {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 16px 0;
            font-size: 13px;
            text-transform: uppercase;
        }

        .subjudul {
            font-weight: bold;
            margin-top: 16px;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    <div class="kop">
        <h1>[Nama Perguruan Tinggi]</h1>
        <p>[Alamat lengkap] &middot; [Telepon / Email / Website]</p>
    </div>

    @yield('content')
</body>

</html>