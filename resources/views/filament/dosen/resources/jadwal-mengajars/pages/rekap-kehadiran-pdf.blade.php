<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 20px 24px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 14px;
            margin: 0 0 2px;
            text-transform: uppercase;
        }

        .header p {
            margin: 0;
            font-size: 10px;
            color: #4b5563;
        }

        .info-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .info-table td {
            padding: 2px 0;
        }

        .info-table .label {
            width: 110px;
            color: #4b5563;
        }

        table.rekap {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        table.rekap th,
        table.rekap td {
            border: 1px solid #d1d5db;
            padding: 3px 4px;
            text-align: center;
        }

        table.rekap th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        table.rekap td.nama {
            text-align: left;
            white-space: nowrap;
        }

        .status-H {
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
        }

        .status-I {
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: bold;
        }

        .status-S {
            background-color: #fef9c3;
            color: #854d0e;
            font-weight: bold;
        }

        .status-A {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
        }

        .berisiko {
            background-color: #fef2f2;
        }

        .persen-aman {
            color: #166534;
            font-weight: bold;
        }

        .persen-berisiko {
            color: #991b1b;
            font-weight: bold;
        }

        .legend {
            margin-top: 10px;
            font-size: 9px;
        }

        .legend span {
            margin-right: 14px;
        }

        .footer-signature {
            margin-top: 40px;
            width: 100%;
        }

        .footer-signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }

        .signature-space {
            height: 60px;
        }

        .summary-box {
            margin-top: 10px;
            font-size: 9.5px;
        }
    </style>
</head>

<body>
    @include('pdf.partials.header', ['judulDokumen' => 'Rekap Kehadiran Perkuliahan'])

    @include('pdf.partials.footer')

    <table class="info-table">
        <tr>
            <td class="label">Mata Kuliah</td>
            <td>: {{ $record->mataKuliah->nama_mk ?? '-' }} ({{ $record->mataKuliah->kode_mk ?? '-' }})</td>
            <td class="label">Tahun Akademik</td>
            <td>: {{ $record->tahunAkademik->nama_tahun ?? '-' }} — {{ $record->tahunAkademik->semester == 1 ? 'Ganjil' : ($record->tahunAkademik->semester == 2 ? 'Genap' : 'Pendek') }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>: {{ $record->kelas->nama_kelas ?? '-' }}</td>
            <td class="label">Dosen Pengampu</td>
            <td>: {{ $dosenNama }}</td>
        </tr>
        <tr>
            <td class="label">Jadwal</td>
            <td>: {{ $record->hari }}, {{ \Illuminate\Support\Carbon::parse($record->jam_mulai)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($record->jam_selesai)->format('H:i') }}</td>
            <td class="label">Rata-rata Kehadiran</td>
            <td>: {{ $rataRataKehadiran }}%</td>
        </tr>
    </table>

    <table class="rekap">
        <thead>
            <tr>
                <th style="width: 60px;">NIM</th>
                <th style="width: 130px; text-align: left;">Nama Mahasiswa</th>
                @foreach ($sesiList as $sesi)
                <th>P{{ $sesi->pertemuan_ke }}</th>
                @endforeach
                <th>H</th>
                <th>I</th>
                <th>S</th>
                <th>A</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($matrix as $row)
            @php $berisiko = $row['persentase_hadir'] < $ambangBatasPersen; @endphp
                <tr class="{{ $berisiko ? 'berisiko' : '' }}">
                <td>{{ $row['mahasiswa']->nim }}</td>
                <td class="nama">{{ $row['mahasiswa']->person->nama_lengkap ?? '-' }}</td>
                @foreach ($sesiList as $sesi)
                @php $status = $row['sesi'][$sesi->id]; @endphp
                <td class="status-{{ $status->value }}">{{ $status->value }}</td>
                @endforeach
                <td>{{ $row['rekap']['H'] }}</td>
                <td>{{ $row['rekap']['I'] }}</td>
                <td>{{ $row['rekap']['S'] }}</td>
                <td>{{ $row['rekap']['A'] }}</td>
                <td class="{{ $berisiko ? 'persen-berisiko' : 'persen-aman' }}">{{ $row['persentase_hadir'] }}%</td>
                </tr>
                @endforeach
        </tbody>
    </table>

    <div class="legend">
        <span><strong>H</strong> = Hadir</span>
        <span><strong>I</strong> = Izin</span>
        <span><strong>S</strong> = Sakit</span>
        <span><strong>A</strong> = Alpa</span>
        <span style="color:#991b1b;">Baris merah = kehadiran di bawah {{ $ambangBatasPersen }}%</span>
    </div>

    <div class="summary-box">
        Dicetak pada: {{ $tanggalCetak }}
    </div>

    <table class="footer-signature">
        <tr>
            <td></td>
            <td>
                Mengetahui,<br>Dosen Pengampu
                <div class="signature-space"></div>
                <strong>{{ $dosenNama }}</strong>
            </td>
        </tr>
    </table>

</body>

</html>