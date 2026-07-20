<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekap KHS</title>
    <style>
        @page { margin: 20px 25px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { font-size: 14px; margin: 0 0 4px 0; }
        .header .meta { font-size: 9px; color: #555; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th {
            background-color: #1F4E78; color: #fff; padding: 5px 4px;
            font-size: 9px; text-align: center; border: 1px solid #000;
        }
        table.data td { padding: 4px; border: 1px solid #ccc; font-size: 9px; }
        table.data tr:nth-child(even) { background-color: #f5f7fa; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; color: #fff; }
        .badge-success { background-color: #16a34a; }
        .badge-warning { background-color: #d97706; }
        .badge-danger { background-color: #dc2626; }
        .summary-box {
            margin-top: 16px; padding: 10px; background-color: #f0f4f8;
            border: 1px solid #cbd5e1; font-size: 9px;
        }
        .summary-box h3 { margin: 0 0 6px 0; font-size: 11px; }
        .summary-row { display: block; margin-bottom: 3px; }
        .footer { position: fixed; bottom: -10px; font-size: 8px; color: #888; width: 100%; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN REKAP KHS</h1>
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
                <th>IPS</th>
                <th>SKS Smt</th>
                <th>SKS Kumulatif</th>
                <th>Status Akademik</th>
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
                    <td class="text-center"><strong>{{ number_format($row->ips, 2) }}</strong></td>
                    <td class="text-center">{{ $row->sks_semester }}</td>
                    <td class="text-center">{{ $row->sks_total }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ match($row->status_akademik) {
                            'Sangat Memuaskan', 'Memuaskan' => 'success',
                            'Baik', 'Cukup' => 'warning',
                            default => 'danger',
                        } }}">{{ $row->status_akademik }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data untuk filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($summary))
        <div class="summary-box">
            <h3>Ringkasan</h3>
            <span class="summary-row">Total Mahasiswa: {{ number_format($summary['total_mahasiswa'] ?? 0, 0, ',', '.') }}</span>
            <span class="summary-row">Rata-rata IPS: {{ number_format($summary['rata_ips'] ?? 0, 2) }}</span>
            <span class="summary-row">IPS Tertinggi: {{ number_format($summary['max_ips'] ?? 0, 2) }}</span>
            <span class="summary-row">IPS Terendah: {{ number_format($summary['min_ips'] ?? 0, 2) }}</span>
        </div>
    @endif

    <div class="footer">Portal Layanan Digital BTSI - SIAKAD UNMARIS &bull; Dicetak otomatis oleh sistem</div>
</body>
</html>