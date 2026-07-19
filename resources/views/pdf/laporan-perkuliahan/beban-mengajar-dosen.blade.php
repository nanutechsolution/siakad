<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Beban Mengajar Dosen</title>
    <style>
        /* Tipografi & Dasar Laporan */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #334155; /* Slate 700 */
            line-height: 1.4;
        }

        /* Desain Ringkasan / KPI Box (Aman untuk DomPDF) */
        .summary-container {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary-card {
            background-color: #f8fafc; /* Slate 50 */
            border: 1px solid #e2e8f0; /* Slate 200 */
            border-radius: 4px;
            padding: 8px 12px;
            width: 48%; /* Pembagian ruang seimbang */
        }

        .summary-label {
            font-size: 8.5px;
            color: #64748b; /* Slate 500 */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a; /* Navy Blue */
        }

        /* Scoped Selector Tabel Data Utama */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .table-data th {
            background-color: #1e3a8a; /* Navy Blue */
            color: #ffffff;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 8px 6px;
            border: 1px solid #1e3a8a;
        }

        .table-data td {
            border: 1px solid #cbd5e1; /* Slate 300 */
            padding: 6px 6px;
            vertical-align: middle;
        }

        /* Zebra Striping */
        .table-data tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Proteksi Pemotongan Baris di Ujung Halaman */
        .table-data tr {
            page-break-inside: avoid;
        }

        /* Utility Helpers */
        .text-center {
            text-align: center;
        }

        .font-semibold {
            font-weight: 600;
        }

        .text-muted {
            color: #64748b;
            font-size: 8.5px;
        }

        .empty-row {
            text-align: center;
            padding: 20px !important;
            color: #64748b;
            font-style: italic;
            background-color: #f1f5f9;
        }
    </style>
</head>

<body>
    <!-- Mengintegrasikan Kop Surat Institusi Resmi -->
    @include('pdf.partials.header', ['judulDokumen' => 'Laporan Beban Mengajar Dosen'])

    <!-- Ringkasan Data Utama (KPI Dashboard Minimalis) -->
    <table class="summary-container">
        <tr>
            <td class="summary-card">
                <div class="summary-label">Total Dosen Terplot</div>
                <div class="summary-value">{{ number_format($summary['total_dosen']) }} Org</div>
            </td>
            <!-- Spacer antar card -->
            <td style="width: 4%;"></td>
            <td class="summary-card">
                <div class="summary-label">Total SKS Mengajar</div>
                <div class="summary-value">{{ number_format($summary['total_sks']) }} SKS</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Detail Beban Mengajar -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 12%;">NIDN</th>
                <th style="width: 38%;">Nama Dosen</th>
                <th style="width: 12%;">Jml Matakuliah</th>
                <th style="width: 12%;">Total SKS</th>
                <th style="width: 12%;">Jml Kelas</th>
                <th style="width: 14%;">Jml Mahasiswa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td class="text-center text-muted">{{ $row['nidn'] ?: '-' }}</td>
                <td class="font-semibold" style="color: #1e293b;">{{ $row['nama_dosen'] }}</td>
                <td class="text-center">{{ $row['jumlah_mata_kuliah'] }}</td>
                <td class="text-center font-semibold" style="color: #1e3a8a;">{{ $row['total_sks'] }}</td>
                <td class="text-center">{{ $row['jumlah_kelas'] }}</td>
                <td class="text-center">{{ number_format($row['jumlah_mahasiswa']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty-row">
                    Tidak ada data beban mengajar dosen yang tersedia.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Mengintegrasikan Footer Laporan Resmi -->
    @include('pdf.partials.footer')
</body>

</html>