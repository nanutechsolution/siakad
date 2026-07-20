<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Transkrip Akademik - {{ $mahasiswa['nim'] }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
        }

        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #1F4E78;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 15px;
            margin: 0 0 2px 0;
            color: #1F4E78;
        }

        .header h2 {
            font-size: 11px;
            margin: 0;
            font-weight: normal;
            color: #555;
        }

        .info-table {
            width: 100%;
            margin-bottom: 14px;
        }

        .info-table td {
            padding: 3px 6px;
            font-size: 10px;
        }

        .info-table .label {
            font-weight: bold;
            width: 130px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.data th {
            background-color: #1F4E78;
            color: #fff;
            padding: 6px 4px;
            font-size: 9px;
            text-align: center;
            border: 1px solid #000;
        }

        table.data td {
            padding: 4px 5px;
            border: 1px solid #ccc;
            font-size: 9px;
        }

        table.data tr:nth-child(even) {
            background-color: #f5f7fa;
        }

        .text-center {
            text-align: center;
        }

        .summary-box {
            margin-top: 16px;
            padding: 10px 14px;
            background-color: #f0f4f8;
            border: 1px solid #cbd5e1;
            font-size: 10px;
        }

        .summary-grid {
            width: 100%;
        }

        .summary-grid td {
            padding: 4px;
            font-size: 10px;
        }

        .footer {
            position: fixed;
            bottom: -12px;
            font-size: 8px;
            color: #888;
            width: 100%;
            text-align: center;
        }

        .signature {
            margin-top: 40px;
            width: 100%;
        }

        .signature td {
            text-align: center;
            font-size: 9px;
            padding-top: 50px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>TRANSKRIP AKADEMIK</h1>
        <h2>Portal Layanan Digital BTSI &bull; SIAKAD UNMARIS</h2>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">NIM</td>
            <td>: {{ $mahasiswa['nim'] }}</td>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ $tanggal_cetak }}</td>
        </tr>
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td>: {{ $mahasiswa['nama'] }}</td>
            <td class="label">Predikat</td>
            <td>: {{ $predikat }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi</td>
            <td>: {{ $mahasiswa['prodi'] }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th style="width: 8%">SKS</th>
                <th style="width: 10%">Nilai Angka</th>
                <th style="width: 10%">Nilai Huruf</th>
                <th style="width: 10%">Bobot</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transkrip as $i => $mk)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $mk['kode_mk'] }}</td>
                <td>{{ $mk['nama_mk'] }}</td>
                <td class="text-center">{{ $mk['sks'] }}</td>
                <td class="text-center">{{ $mk['nilai_angka'] }}</td>
                <td class="text-center"><strong>{{ $mk['nilai_huruf'] }}</strong></td>
                <td class="text-center">{{ number_format($mk['nilai_indeks'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data nilai.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <table class="summary-grid">
            <tr>
                <td><strong>Total SKS Diakui</strong></td>
                <td>: {{ $total_sks }}</td>
                <td><strong>Indeks Prestasi Kumulatif (IPK)</strong></td>
                <td>: <strong>{{ number_format($ipk, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="signature">
        <tr>
            <td style="width: 50%"></td>
            <td style="width: 50%">
                Waingapu, {{ $tanggal_cetak }}<br><br><br><br>
                ( __________________________ )<br>
                Kepala Bagian Akademik
            </td>
        </tr>
    </table>

    <div class="footer">Dokumen ini dicetak otomatis oleh sistem SIAKAD UNMARIS dan sah tanpa tanda tangan basah untuk keperluan internal.</div>
</body>

</html>