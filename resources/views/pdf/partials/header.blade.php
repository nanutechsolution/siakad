@php
$kampus = app(\App\Settings\KampusSettings::class);
$logoFullPath = $kampus->logo_path ? storage_path('app/public/'.$kampus->logo_path) : null;
@endphp

<table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
    <tr>
        <!-- Bagian Logo dengan Batasan Proporsional -->
        <td style="width: 80px; vertical-align: middle; padding-bottom: 6px;">
            @if ($logoFullPath && file_exists($logoFullPath))
                <img src="{{ $logoFullPath }}" style="width: 65px; height: 65px; object-fit: contain;">
            @else
                <!-- Placeholder kotak minimalis jika gambar logo absen -->
                <div style="width: 65px; height: 65px; border: 1px dashed #cbd5e1; background-color: #f8fafc;"></div>
            @endif
        </td>
        
        <!-- Informasi Institusi Pusat -->
        <td style="vertical-align: middle; text-align: center; padding-bottom: 6px;">
            <!-- Nama Kampus: Lebih Besar, Bold Tebal, & Berwarna Navy Baku -->
            <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.75px; margin-bottom: 2px;">
                {{ $kampus->nama }}
            </div>
            
            <!-- Status Akreditasi: Dibuat seperti Sub-Label Elegan -->
            @if ($kampus->akreditasi)
                <div style="font-size: 8.5px; color: #0f766e; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;">
                    Terakreditasi Institusi: {{ $kampus->akreditasi }}
                </div>
            @endif
            
            <!-- Detail Alamat & Kontak: Teks Lebih Rapi & Muted -->
            <div style="font-size: 9px; color: #334155; margin-bottom: 2px; line-height: 1.3;">
                {{ $kampus->alamat }}
            </div>
            <div style="font-size: 8.5px; color: #64748b;">
                Telp: {{ $kampus->telepon }} <span style="color: #cbd5e1; padding: 0 3px;">•</span> 
                Email: <span style="color: #1e3a8a;">{{ $kampus->email }}</span> <span style="color: #cbd5e1; padding: 0 3px;">•</span> 
                Website: <span style="color: #1e3a8a;">{{ $kampus->website }}</span>
            </div>
        </td>
        
        <!-- Penyeimbang Kanan (Spacer) agar Teks Center Sempurna -->
        <td style="width: 80px; padding-bottom: 6px;"></td>
    </tr>
</table>

<!-- Garis Pembatas Kop Surat Ganda Formal (Tebal Khas Dokumen Negara) -->
<div style="border-top: 2.5px solid #1e3a8a; border-bottom: 0.75px solid #1e3a8a; height: 2px; margin-bottom: 12px; clear: both;"></div>

<!-- Judul Dokumen: Menghilangkan Underline Jadul, Diganti Teks Bersih Berjarak -->
@if (!empty($judulDokumen))
<div style="text-align: center; margin-bottom: 6px;">
    <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px;">
        {{ $judulDokumen }}
    </div>
</div>
@endif

<!-- Baris Informasi Filter: Dibungkus Box Badge semi-transparan untuk Kerapian Parameter Laporan -->
@if (!empty($infoBaris))
<div style="text-align: center; margin-bottom: 15px;">
    <div style="display: block; background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 4px; max-width: 100%;">
        <span style="font-size: 8.5px; color: #475569; font-weight: 500;">
            @foreach($infoBaris as $index => $item)
                <span style="color: #1e3a8a; font-weight: 600;">{{ $item }}</span>
                @if(!$loop->last)
                    <span style="color: #94a3b8; padding: 0 6px;">•</span>
                @endif
            @endforeach
        </span>
    </div>
</div>
@endif