<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        h1 {
            font-size: 15px;
            margin: 0 0 2px 0;
        }

        .subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #111827;
            color: #ffffff;
            text-align: left;
            padding: 6px 8px;
            font-size: 9.5px;
        }

        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .empty {
            text-align: center;
            padding: 24px;
            color: #6b7280;
        }

        .footer {
            margin-top: 14px;
            font-size: 8.5px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    @include('pdf.partials.header', [
    'judulDokumen' => $title,
    'infoBaris' => $infoBaris ?? ['Dicetak pada: '.$generatedAt->format('d/m/Y H:i')],
    ])

    @if ($rows->isEmpty())
    <div class="empty">Tidak ada data untuk filter yang dipilih.</div>
    @else
    <table>
        <thead>
            <tr>
                @foreach ($headings as $label)
                <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
            @php($row = (array) $row)
            <tr>
                @foreach (array_keys($headings) as $key)
                <td>{{ $row[$key] ?? '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    <div class="footer">SIAKAD — Modul Laporan Keuangan. Total baris: {{ $rows->count() }}.</div>
</body>

</html>