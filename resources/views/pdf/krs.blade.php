<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Rencana Studi</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
        }
        .header p {
            margin: 0;
            font-size: 11px;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .info-table .label {
            width: 15%;
            font-weight: bold;
        }
        .info-table .separator {
            width: 2%;
        }
        .info-table .value {
            width: 33%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        .data-table th {
            text-align: center;
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center !important;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 80px;
        }
        .bold-underline {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>UNIVERSITAS STELLA MARIS SUMBA</h1>
        <h2>PORTAL LAYANAN DIGITAL BTSI</h2>
        <p>Jl. Kampus Unmaris, Tambolaka, Sumba Barat Daya, NTT</p>
    </div>

    <div class="title">KARTU RENCANA STUDI (KRS)</div>

    <table class="info-table">
        <tr>
            <td class="label">NIM</td>
            <td class="separator">:</td>
            <td class="value">{{ $mahasiswa->nim }}</td>
            <td class="label">Tahun Akademik</td>
            <td class="separator">:</td>
            <td class="value">{{ $tahunAkademik->nama_tahun }}</td>
        </tr>
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="separator">:</td>
            <td class="value">{{ $person->nama_lengkap ?? '-' }}</td>
            <td class="label">Semester</td>
            <td class="separator">:</td>
            <td class="value">{{ $tahunAkademik->semester === 1 ? 'Ganjil' : ($tahunAkademik->semester === 2 ? 'Genap' : 'Pendek') }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi</td>
            <td class="separator">:</td>
            <td class="value">{{ $prodi->nama_prodi ?? '-' }}</td>
            <td class="label">Status KRS</td>
            <td class="separator">:</td>
            <td class="value">{{ $krs->status_krs }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode MK</th>
                <th style="width: 45%;">Mata Kuliah</th>
                <th style="width: 10%;">SKS</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSks = 0; @endphp
            @forelse($krsDetails as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $detail->kode_mk_snapshot }}</td>
                    <td>{{ $detail->nama_mk_snapshot }}</td>
                    <td class="text-center">{{ $detail->sks_snapshot }}</td>
                    <td class="text-center">{{ $detail->jadwalKuliah->kelas->nama_kelas ?? '-' }}</td>
                    <td class="text-center">{{ $detail->status_ambil === 'B' ? 'Baru' : 'Ulang' }}</td>
                </tr>
                @php $totalSks += $detail->sks_snapshot; @endphp
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada mata kuliah yang diambil.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right;">Total SKS :</th>
                <th class="text-center">{{ $totalSks }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Menyetujui,<br>
                Dosen Pembimbing Akademik
                <div class="signature-space"></div>
                <span class="bold-underline">{{ $dosenWali->person->nama_lengkap ?? '_________________________' }}</span><br>
                NIDN. {{ $dosenWali->nidn ?? '-' }}
            </td>
            <td></td>
            <td>
                Tambolaka, {{ now()->format('d-m-Y') }}<br>
                Mahasiswa Ybs,
                <div class="signature-space"></div>
                <span class="bold-underline">{{ $person->nama_lengkap ?? '_________________________' }}</span><br>
                NIM. {{ $mahasiswa->nim }}
            </td>
        </tr>
    </table>

</body>
</html>