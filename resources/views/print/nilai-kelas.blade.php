<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Nilai - {{ $jadwal->mataKuliah?->nama_mk }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; padding: 20px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .kop-surat p { margin: 5px 0 0 0; font-size: 11px; }
        .meta-tabel { width: 100%; margin-bottom: 15px; }
        .meta-tabel td { padding: 3px 0; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 6px; text-align: left; }
        .table-data th { background-color: #f2f2f2; text-align: center; }
        .ttd-container { margin-top: 40px; width: 100%; }
        .ttd-box { width: 300px; float: right; text-align: center; }
        .ttd-space { height: 70px; }
        
        /* Pengaturan Cetak Browser */
        @media print {
            body { padding: 0; }
            @page { size: A4; margin: 1.5cm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="text-right no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer; background: #22c55e; color: #fff; border: none; border-radius: 4px;">Cetak / Simpan PDF</button>
    </div>

    <div class="kop-surat text-center">
        <h2>DAFTAR NILAI MAHASISWA</h2>
        <p>SISTEM INFORMASI AKADEMIK UNIVERSITAS</p>
    </div>

    <table class="meta-tabel">
        <tr>
            <td style="width: 15%;">Mata Kuliah</td>
            <td style="width: 35%;">: <strong>{{ $jadwal->mataKuliah?->kode_mk }} - {{ $jadwal->mataKuliah?->nama_mk }}</strong></td>
            <td style="width: 15%;">Tahun Akademik</td>
            <td style="width: 35%;">: {{ $jadwal->tahunAkademik?->nama_tahun }}</td>
        </tr>
        <tr>
            <td>Kelas / SKS</td>
            <td>: {{ $jadwal->kelas?->nama_kelas }} / {{ $jadwal->mataKuliah?->sks_snapshot ?? '-' }} SKS</td>
            <td>Dosen Penguji</td>
            <td>: {{ Auth::user()?->person?->nama_lengkap }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">NIM</th>
                <th>Nama Mahasiswa</th>
                @foreach($komponenAktif as $komponen)
                    <th style="width: 10%;">{{ $komponen->komponen?->nama_komponen }}<br>({{ (int)$komponen->bobot_persen }}%)</th>
                @endforeach
                <th style="width: 12%;">Nilai Angka</th>
                <th style="width: 10%;">Nilai Huruf</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peserta as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row->krs?->mahasiswa?->nim }}</td>
                    <td>{{ $row->krs?->mahasiswa?->person?->nama_lengkap }}</td>
                    
                    {{-- Loop nilai per komponen --}}
                    @foreach($komponenAktif as $komponen)
                        <td class="text-center">
                            {{ $row->getNilaiKomponen((int) $komponen->komponen_id) }}
                        </td>
                    @endforeach
                    
                    <td class="text-center" style="font-weight: bold;">{{ number_format((float)$row->nilai_angka, 2) }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $row->nilai_huruf ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + count($komponenAktif) }}" class="text-center">Belum ada peserta di kelas ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd-container">
        <div class="ttd-box">
            <p>Kota Kampus, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Dosen Pengampu,</p>
            <div class="ttd-space"></div>
            <p><strong><u>{{ Auth::user()?->person?->nama_lengkap }}</u></strong></p>
            <p>NIDN. {{ Auth::user()?->person?->trxDosen?->nidn ?? '-' }}</p>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>