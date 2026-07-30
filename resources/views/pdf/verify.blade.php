<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Dokumen Resmi — SIAKAD</title>
    <style>
        :root {
            --primary: #0f172a;
            --success-bg: #f0fdf4;
            --success-text: #166534;
            --success-border: #bbf7d0;
            --danger-bg: #fef2f2;
            --danger-text: #991b1b;
            --danger-border: #fecaca;
            --border-color: #e2e8f0;
            --text-main: #334155;
            --text-muted: #64748b;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 520px;
            margin: 60px auto;
            padding: 0 20px;
            color: var(--text-main);
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
            padding: 32px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            letter-spacing: -0.025em;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .badge.valid {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }

        .badge.invalid {
            background: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid var(--danger-border);
        }

        .section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary);
            margin: 28px 0 12px 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        tr td {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
            vertical-align: top;
        }

        tr:last-child td {
            border-bottom: none;
        }

        td.label {
            color: var(--text-muted);
            width: 38%;
            font-weight: 500;
        }

        td.value {
            color: var(--text-main);
            font-weight: 600;
            text-align: right;
        }

        .signature-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .signature-item:last-child {
            border-bottom: none;
        }

        .signer-name {
            font-weight: 600;
            color: var(--text-main);
        }

        .signer-title {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .signed-date {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-align: right;
        }

        .error-message {
            margin-top: 12px;
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 32px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <h2>SISTEM INFORMASI AKADEMIK</h2>
        </div>

        @if($valid)
        <div style="text-align: center;">
            <span class="badge valid">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Dokumen Sah & Terverifikasi
            </span>
        </div>

        <table>
            <tr>
                <td class="label">Jenis Dokumen</td>
                <td class="value">{{ $documentType }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Dokumen</td>
                <td class="value" style="font-family: monospace; font-size: 0.95rem;">{{ $nomorDokumen }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value"><span style="color: var(--success-text);">{{ strtoupper($status) }}</span></td>
            </tr>
            <tr>
                <td class="label">Diterbitkan Pada</td>
                <td class="value">{{ \Carbon\Carbon::parse($generatedAt)->translatedFormat('d F Y H:i') }} WIB</td>
            </tr>
        </table>

        @if($signatures->isNotEmpty())
        <div class="section-title">Informasi Penandatangan</div>
        <div>
            @foreach($signatures as $signature)
            <div class="signature-item">
                <div>
                    <div class="signer-name">{{ $signature->nama_penandatangan_snapshot }}</div>
                    <div class="signer-title">{{ $signature->jabatan_snapshot }}</div>
                </div>
                <div class="signed-date">
                    {{ \Carbon\Carbon::parse($signature->signed_at)->translatedFormat('d M Y') }}<br>
                    <span style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($signature->signed_at)->format('H:i') }} WIB</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @else
        <div style="text-align: center;">
            <span class="badge invalid">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Dokumen Tidak Sah / Palsu
            </span>
            <p class="error-message">
                Maaf, dokumen dengan nomor/kode verifikasi tersebut tidak ditemukan di dalam database sistem akademik kami, atau masa berlaku dokumen telah dicabut.
            </p>
        </div>
        @endif
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} SIAKAD. Dokumen ini diterbitkan secara elektronik dan sah tanpa cap basah.
    </div>
</body>

</html>