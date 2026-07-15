<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Jadwal Kuliah</title>
    <style>
        @page {
            margin: 20px 24px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
        }

        h1 {
            font-size: 16px;
            margin: 0 0 2px 0;
        }

        .subtitle {
            font-size: 10px;
            color: #555;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #cecb10;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f7f8fa;
        }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #888;
            text-align: right;
        }
    </style>
</head>

<body>

    @include('pdf.partials.header', [
    'judulDokumen' => 'Jadwal Perkuliahan',
    'infoBaris' => [
    'Tahun Akademik: '.$activeTaLabel ,
    'Dicetak pada: '.now()->format('d/m/Y H:i'),
    'Total: '. $jadwals->count(),
    ],
    ])
    <table>
        <thead>
            <tr>
                <th>Kode MK</th>
                <th>Mata Kuliah</th>
                <th>SKS</th>
                <th>Kelas</th>
                <th>Hari</th>
                <th>Jam</th>
                <th>Ruang</th>
                <th>Dosen Pengajar</th>
                <th>Kapasitas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwals as $jadwal)
            @php
            $persen = $jadwal->kuota_kelas > 0 ? ($jadwal->isi_kelas / $jadwal->kuota_kelas) * 100 : 0;
            $badgeClass = $jadwal->isi_kelas >= $jadwal->kuota_kelas
            ? 'badge-danger'
            : ($persen >= 80 ? 'badge-warning' : 'badge-success');
            @endphp
            <tr>
                <td>{{ $jadwal->mataKuliah?->kode_mk ?? '-' }}</td>
                <td>{{ $jadwal->mataKuliah?->nama_mk ?? '-' }}</td>
                <td>{{ $jadwal->mataKuliah?->sks_default ?? '-' }}</td>
                <td>
                    {{ $jadwal->kelas?->nama_kelas ?? '-' }}
                    @if($jadwal->kelas?->prodi?->nama_prodi)
                    <br><span style="color:#777;font-size:8px;">{{ $jadwal->kelas->prodi->nama_prodi }}</span>
                    @endif
                </td>
                <td>{{ $jadwal->hari ?? '-' }}</td>
                <td>
                    {{ $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '--:--' }}
                    -
                    {{ $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '--:--' }}
                </td>
                <td>{{ $jadwal->ruang?->nama_ruang ?? '-' }}</td>
                <td>
                    @forelse ($jadwal->dosenPengajars as $dp)
                    {{ $dp->dosen?->person?->nama_lengkap ?? '-' }}{{ $dp->is_koordinator ? ' (Koord.)' : '' }}<br>
                    @empty
                    -
                    @endforelse
                </td>
                <td>
                    <span class="badge {{ $badgeClass }}">{{ $jadwal->isi_kelas }} / {{ $jadwal->kuota_kelas }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding: 16px; color:#999;">Tidak ada data jadwal untuk filter yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dicetak otomatis oleh SIAKAD BTSI Kampus &middot; {{ $generatedAt->format('d-m-Y H:i:s') }}</div>
</body>

</html>