<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Keaslian Dokumen — {{ $institutionName ?? 'Universitas Stella Maris Sumba' }}</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #1e293b;
            --accent: #2563eb;
            --success-bg: #f0fdf4;
            --success-text: #166534;
            --success-border: #bbf7d0;
            --danger-bg: #fef2f2;
            --danger-text: #991b1b;
            --danger-border: #fecaca;
            --border-color: #e2e8f0;
            --text-main: #334155;
            --text-muted: #64748b;
            --bg-body: #f1f5f9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            padding: 40px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header Institusi */
        .institution-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
            border-bottom: 4px solid #3b82f6;
        }

        .logo-placeholder {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-placeholder svg {
            width: 32px;
            height: 32px;
            fill: #ffffff;
        }

        .institution-name {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 4px;
        }

        .unit-name {
            font-size: 0.9rem;
            color: #93c5fd;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .portal-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #cbd5e1;
            background: rgba(255, 255, 255, 0.1);
            display: inline-block;
            padding: 4px 12px;
            border-radius: 99px;
            margin-top: 4px;
        }

        /* Body Content */
        .content-body {
            padding: 36px 32px;
        }

        /* Status Badge */
        .status-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
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

        /* Section Title */
        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 28px 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr td {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.925rem;
            vertical-align: top;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
            width: 40%;
            font-weight: 500;
        }

        .info-value {
            color: var(--text-main);
            font-weight: 600;
            text-align: right;
        }

        .code-style {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #f8fafc;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            font-size: 0.85rem;
        }

        /* Signatures Container */
        .signature-grid {
            display: grid;
            gap: 12px;
        }

        .signature-card {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .signer-name {
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .signer-title {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .signer-time {
            text-align: right;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* QR Section */
        .qr-section {
            margin-top: 32px;
            background: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .qr-box {
            width: 110px;
            height: 110px;
            background: #ffffff;
            margin: 0 auto 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box svg {
            width: 90px;
            height: 90px;
        }

        .qr-text {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Error state message */
        .error-desc {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 12px;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            background: #f8fafc;
            border-top: 1px solid var(--border-color);
            padding: 24px 32px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .footer-univ {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        @media (max-width: 640px) {
            body {
                padding: 16px 8px;
            }

            .content-body {
                padding: 24px 20px;
            }

            .signature-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .signer-time {
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <!-- Header Institusi -->
            <div class="institution-header">
                <div class="logo-placeholder">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z" />
                    </svg>
                </div>
                <div class="institution-name">{{ $institutionName ?? 'Universitas Stella Maris Sumba' }}</div>
                <div class="unit-name">{{ $fakultas ?? 'Direktorat Administrasi Akademik' }}</div>
                <div><span class="portal-title">Sistem Informasi Akademik</span></div>
            </div>

            <!-- Content Body -->
            <div class="content-body">
                @if($valid ?? true)
                <!-- Status Valid -->
                <div class="status-container">
                    <div class="badge valid">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Dokumen Sah & Terverifikasi
                    </div>
                </div>

                <!-- Informasi Dokumen -->
                <div class="section-title">Detail Dokumen Akademik</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Jenis Dokumen</td>
                        <td class="info-value">{{ $documentType ?? 'Ijazah Kelulusan' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Nomor Dokumen</td>
                        <td class="info-value"><span class="code-style">{{ $nomorDokumen ?? '1234/UN.ABC/SK.01/2026' }}</span></td>
                    </tr>
                    <tr>
                        <td class="info-label">Kode Verifikasi</td>
                        <td class="info-value"><span class="code-style">{{ $kodeVerifikasi ?? 'VER-98F2A1' }}</span></td>
                    </tr>
                    <tr>
                        <td class="info-label">Nama Pemilik</td>
                        <td class="info-value">{{ $namaPemilik ?? 'Ahmad Fauzi' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">NIM / NPM</td>
                        <td class="info-value"><span class="code-style">{{ $nim ?? '2208107010001' }}</span></td>
                    </tr>
                    <tr>
                        <td class="info-label">Program Studi</td>
                        <td class="info-value">{{ $programStudi ?? 'Teknik Informatika' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Fakultas</td>
                        <td class="info-value">{{ $fakultas ?? 'Fakultas Teknik' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status Dokumen</td>
                        <td class="info-value"><span style="color: var(--success-text);">Aktif / Sah</span></td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Diterbitkan</td>
                        <td class="info-value">{{ $tanggalDiterbitkan ?? '15 Januari 2026' }}</td>
                    </tr>
                </table>

                <!-- Informasi Penandatangan -->
                <div class="section-title">Tanda Tangan Digital Tersertifikasi</div>
                <div class="signature-grid">
                    @if(isset($signatures) && is_iterable($signatures))
                    @foreach($signatures as $sig)
                    <div class="signature-card">
                        <div>
                            <div class="signer-name">{{ $sig->nama_pejabat ?? 'Prof. Dr. Ir. Rektor, M.Sc.' }}</div>
                            <div class="signer-title">{{ $sig->jabatan ?? 'Rektor Universitas' }}</div>
                        </div>
                        <div class="signer-time">
                            {{ $sig->tanggal ?? '15 Jan 2026' }}<br>
                            <span style="font-size: 0.75rem;">{{ $sig->waktu ?? '10:30 WIB' }}</span>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="signature-card">
                        <div>
                            <div class="signer-name">Prof. Dr. Ir. H. Mangkubumi, M.Sc.</div>
                            <div class="signer-title">Dekan Fakultas Teknik</div>
                        </div>
                        <div class="signer-time">
                            15 Januari 2026<br>
                            <span style="font-size: 0.75rem;">14:00 WIB</span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- QR Code Verification -->
                <div class="qr-section">
                    <div class="qr-box">
                        <svg viewBox="0 0 100 100">
                            <path d="M10,10 h30 v30 h-30 z M20,20 h10 v10 h-10 z M50,10 h10 v10 h-10 z M70,10 h20 v20 h-20 z M50,30 h20 v10 h-20 z M10,50 h20 v20 h-20 z M40,50 h10 v20 h-10 z M60,50 h30 v10 h-30 z M70,70 h20 v20 h-20 z M10,80 h10 v10 h-10 z M30,80 h10 v10 h-10 z M50,80 h10 v10 h-10 z" fill="#1e293b" />
                        </svg>
                    </div>
                    <div class="qr-text">Scan QR Code untuk memverifikasi dokumen ini</div>
                </div>

                @else
                <!-- Status Tidak Valid -->
                <div class="status-container" style="margin-bottom: 20px;">
                    <div class="badge invalid">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Dokumen Tidak Sah / Tidak Ditemukan
                    </div>
                    <p class="error-desc">
                        Maaf, nomor dokumen atau kode verifikasi yang Anda masukkan tidak terdaftar di dalam database resmi Sistem Informasi Akademik kami, atau dokumen tersebut telah dicabut/tidak berlaku.
                    </p>
                </div>
                @endif
            </div>

            <!-- Footer Resmi -->
            <div class="footer">
                <div class="footer-univ">{{ $institutionName ?? 'Universitas Stella Maris Sumba' }}</div>
                <div>&copy; {{ date('Y') }} &bull; Seluruh Hak Cipta Dilindungi</div>
                <div style="margin-top: 6px;">Dokumen elektronik ini diterbitkan melalui Sistem Informasi Akademik resmi dan memiliki keabsahan digital.</div>
            </div>
        </div>
    </div>
</body>

</html>