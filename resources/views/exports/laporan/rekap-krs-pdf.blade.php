<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Rekap KRS</title>
    <style>
        @page {
            margin: 20px 25px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 14px;
            margin: 0 0 4px 0;
        }

        .header .meta {
            font-size: 9px;
            color: #555;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data th {
            background-color: #1F4E78;
            color: #fff;
            padding: 5px 4px;
            font-size: 9px;
            text-align: center;
            border: 1px solid #000;
        }

        table.data td {
            padding: 4px;
            border: 1px solid #ccc;
            font-size: 9px;
        }

        table.data tr:nth-child(even) {
            background-color: #f5f7fa;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            color: #fff;
        }

        .badge-success {
            background-color: #16a34a;
        }

        .badge-warning {
            background-color: #d97706;
        }

        .badge-danger {
            background-color: #dc2626;
        }

        .badge-gray {
            background-color: #6b7280;
        }

        .summary-box {
            margin-top: 16px;
            padding: 10px;
            background-color: #f0f4f8;
            border: 1px solid #cbd5e1;
            font-size: 9px;
        }

        .summary-box h3 {
            margin: 0 0 6px 0;
            font-size: 11px;
        }

        .summary-row {
            display: block;
            margin-bottom: 3px;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            font-size: 8px;
            color: #888;
            width: 100%;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN REKAP KRS</h1>
        <div class="meta">{{ $filterSummary }}</div>
        <div class="meta">Dicetak: {{ $tanggalCetak }}</div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Prodi</th>
                <th>Angkatan</th>
                <th>Smt</th>
                <th>Jml MK</th>
                <th>Total SKS</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $i => $row)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $row->nim }}</td>
                <td>{{ $row->nama_mahasiswa }}</td>
                <td>{{ $row->nama_prodi }}</td>
                <td class="text-center">{{ $row->angkatan }}</td>
                <td class="text-center">{{ $row->semester }}</td>
                <td class="text-center">{{ $row->jumlah_mata_kuliah }}</td>
                <td class="text-center">{{ $row->total_sks }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ match($row->status_krs) {
                            'APPROVED' => 'success',
                            'SUBMITTED' => 'warning',
                            'REJECTED' => 'danger',
                            default => 'gray',
                        } }}">{{ $row->status_krs }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data untuk filter yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($summary))
    <div class="summary-box">
        <h3>Ringkasan</h3>
        <span class="summary-row">Total Mahasiswa: {{ number_format($summary['total_mahasiswa'] ?? 0, 0, ',', '.') }}</span>
        <span class="summary-row">Total Mata Kuliah: {{ number_format($summary['total_mata_kuliah'] ?? 0, 0, ',', '.') }}</span>
        <span class="summary-row">Total SKS: {{ number_format($summary['total_sks'] ?? 0, 0, ',', '.') }}</span>
        <span class="summary-row">Rata-rata SKS/Mahasiswa: {{ $summary['rata_sks_per_mahasiswa'] ?? 0 }}</span>
    </div>
    @endif

    <div class="footer">Portal Layanan Digital BTSI - SIAKAD UNMARIS &bull; Dicetak otomatis oleh sistem</div>
</body>

</html>