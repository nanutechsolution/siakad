<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Mutu Internal</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h2 { margin-bottom: 4px; }
        .summary { margin-bottom: 10px; }
        .summary span { margin-right: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Audit Mutu Internal (AMI)</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <div class="summary">
        <span>Total Temuan: {{ $summary['total_temuan'] }}</span>
        <span>KTS Mayor: {{ $summary['kts_mayor'] }}</span>
        <span>KTS Minor: {{ $summary['kts_minor'] }}</span>
        <span>Observasi: {{ $summary['observasi'] }}</span>
        <span>Open: {{ $summary['open'] }}</span>
        <span>Closed: {{ $summary['closed'] }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Prodi</th>
                <th>Standar</th>
                <th>Klasifikasi</th>
                <th>Auditor</th>
                <th>Status Workflow</th>
                <th>Deadline</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['periode'] }}</td>
                    <td>{{ $row['prodi'] }}</td>
                    <td>{{ $row['standar'] }}</td>
                    <td>{{ $row['klasifikasi'] }}</td>
                    <td>{{ $row['auditor'] }}</td>
                    <td>{{ $row['status_workflow'] }}</td>
                    <td>{{ $row['deadline_perbaikan'] }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
