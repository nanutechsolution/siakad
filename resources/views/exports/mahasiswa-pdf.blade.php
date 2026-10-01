<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Daftar Data Mahasiswa</title>
    <style>
        @page {
            margin: 28px 24px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #111827;
        }

        .kop {
            text-align: center;
            margin-bottom: 10px;
        }

        .kop .institusi {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop .judul {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
        }

        .meta {
            margin-bottom: 8px;
            font-size: 8px;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            background: #4F46E5;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 3px;
            border: 1px solid #4338CA;
        }

        td {
            padding: 3px;
            border: 1px solid #D1D5DB;
            vertical-align: top;
        }

        tbody tr:nth-child(even) td {
            background: #F9FAFB;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="kop">
        <div class="institusi">{{ $institusi }}</div>
        <div class="judul">DAFTAR DATA MAHASISWA</div>
    </div>

    <div class="meta">
        Total: <strong>{{ number_format($total, 0, ',', '.') }}</strong> mahasiswa
        &nbsp;|&nbsp; Dicetak: {{ $dicetak }}
        &nbsp;|&nbsp; Oleh: {{ $pencetak }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="center">No</th>
                @foreach ($headings as $heading)
                <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                @foreach ($row as $cell)
                <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>