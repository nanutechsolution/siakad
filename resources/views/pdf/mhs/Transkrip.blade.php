<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Transkrip Sementara - {{ $mahasiswa->nim }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header img {
            height: 55px;
        }

        .header h1 {
            font-size: 14px;
            margin: 4px 0 0;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
            color: #4b5563;
        }

        table.info {
            width: 100%;
            margin-bottom: 12px;
        }

        table.info td {
            padding: 2px 0;
            font-size: 10px;
        }

        table.info td.label {
            width: 120px;
            color: #4b5563;
        }

        table.ringkasan {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        table.ringkasan td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: center;
        }

        table.nilai {
            width: 100%;
            border-collapse: collapse;
        }

        table.nilai th,
        table.nilai td {
            border: 1px solid #d1d5db;
            padding: 5px;
            font-size: 10px;
        }

        table.nilai th {
            background: #f3f4f6;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: right;
        }

        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            font-size: 60px;
            color: #f3f4f6;
            transform: rotate(-25deg);
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="watermark">SEMENTARA</div>

    @include('pdf.partials.header', [
    'judulDokumen' => 'Transkrip Akademik Sementara',
   
    ])
    <table class="info">
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td>: {{ $mahasiswa->nama_lengkap }}</td>
            <td class="label">Program Studi</td>
            <td>: {{ $mahasiswa->prodi?->nama_prodi }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td>: {{ $mahasiswa->nim }}</td>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <table class="ringkasan">
        <tr>
            <td>IPK<br><strong>{{ number_format($data['ipk'] ?? 0, 2) }}</strong></td>
            <td>Total SKS Ditempuh<br><strong>{{ $data['total_sks_ditempuh'] }}</strong></td>
            <td>Total SKS Lulus<br><strong>{{ $data['total_sks_lulus'] }}</strong></td>
        </tr>
    </table>

    <table class="nilai">
        <thead>
            <tr>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th class="text-center">SKS</th>
                <th class="text-center">Nilai</th>
                <th class="text-center">Mutu</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['mata_kuliah'] as $row)
            <tr>
                <td>{{ $row->mataKuliah?->kode_mk }}</td>
                <td>{{ $row->mataKuliah?->nama_mk }}</td>
                <td class="text-center">{{ $row->sks_diakui }}</td>
                <td class="text-center">{{ $row->nilai_huruf_final }}</td>
                <td class="text-center">{{ number_format($row->nilai_indeks_final, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh sistem pada {{ now()->translatedFormat('d F Y H:i') }}<br>
        Dokumen ini adalah transkrip SEMENTARA (bukan pengganti transkrip resmi berstempel & bertanda tangan basah).
    </div>
</body>

</html>