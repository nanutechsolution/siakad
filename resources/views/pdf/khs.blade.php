<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>KHS - {{ $krs->mahasiswa->person->nama_lengkap }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 5px;
        }

        .nilai-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .nilai-table th,
        .nilai-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .nilai-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
        }

        .ttd-box {
            width: 300px;
            float: right;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2></h2>
        <p>: </p>
    </div>
    @include('pdf.partials.header', [
    'judulDokumen' => 'KARTU HASIL STUDI (KHS)',
    'infoBaris' => [
    'Semester: {{ $krs->tahunAkademik->nama_tahun }},
    ],
    ])

    <table class="info-table">
        <tr>
            <td width="20%"><strong>NIM</strong></td>
            <td>: {{ $krs->mahasiswa->nim }}</td>
            <td width="20%"><strong>Program Studi</strong></td>
            <td>: {{ $krs->mahasiswa->prodi->nama_prodi }}</td>
        </tr>
        <tr>
            <td><strong>Nama</strong></td>
            <td>: {{ $krs->mahasiswa->person->nama_lengkap }}</td>
            <td><strong>Status</strong></td>
            <td>: Aktif</td>
        </tr>
    </table>

    <table class="nilai-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Mata Kuliah</th>
                <th>SKS</th>
                <th>Nilai</th>
                <th>Indeks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($krs->krsDetails as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $detail->mataKuliah->kode_mk }}</td>
                <td>{{ $detail->nama_mk_snapshot }}</td>
                <td class="text-center">{{ $detail->sks_snapshot }}</td>
                <td class="text-center">{{ $detail->nilai_huruf }}</td>
                <td class="text-center">{{ $detail->nilai_indeks }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total SKS</strong></td>
                <td class="text-center"><strong>{{ $krs->krsDetails->sum('sks_snapshot') }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p><strong>IPS: {{ $krs->riwayatStatus->ips ?? '-' }}</strong> | <strong>IPK: {{ $krs->riwayatStatus->ipk ?? '-' }}</strong></p>

        <div class="ttd-box">
            <p>Dicetak pada: {{ date('d-m-Y') }}</p>
            <br><br><br>
            <p><strong>( Bagian Akademik )</strong></p>
        </div>
    </div>

</body>

</html>