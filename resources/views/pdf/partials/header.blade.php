@php
$kampus = app(\App\Settings\KampusSettings::class);
$logoFullPath = $kampus->logo_path ? storage_path('app/public/'.$kampus->logo_path) : null;
@endphp

<!-- Container Kop Surat -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
    <tr>
        <!-- Kolom Logo (Presisi 15%) -->
        <td style="width: 15%; text-align: left; vertical-align: middle; padding-right: 15px;">
            @if ($logoFullPath && file_exists($logoFullPath))
            <img src="{{ $logoFullPath }}" style="width: 85px; max-height: 85px; object-fit: contain;">
            @else
            <div style="width: 80px; height: 80px; border: 1px solid #94a3b8; text-align: center; line-height: 80px; font-size: 10px; color: #64748b; background-color: #f8fafc;">
                Logo Absen
            </div>
            @endif
        </td>

        <!-- Kolom Informasi Institusi (Pusat) -->
        <td style="width: 70%; text-align: center; vertical-align: middle;">
            <!-- Nama Kampus: Font Serif Klasik untuk Kesan Formal -->
            <div style="font-family: 'Times New Roman', Times, serif; font-size: 22px; font-weight: bold; color: #000000; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">
                {{ $kampus->nama }}
            </div>

            <!-- Status Akreditasi -->
            @if ($kampus->akreditasi)
            <div style="font-family: Arial, sans-serif; font-size: 11px; font-weight: bold; color: #333333; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Terakreditasi: {{ $kampus->akreditasi }}
            </div>
            @endif

            <!-- Detail Alamat & Kontak -->
            <div style="font-family: Arial, sans-serif; font-size: 10px; color: #111827; line-height: 1.4; margin-bottom: 2px;">
                {{ $kampus->alamat }}
            </div>
            <div style="font-family: Arial, sans-serif; font-size: 10px; color: #111827;">
                Telepon: {{ $kampus->telepon }} &nbsp;|&nbsp;
                Email: {{ $kampus->email }} &nbsp;|&nbsp;
                Website: {{ $kampus->website }}
            </div>
        </td>

        <!-- Penyeimbang Kanan (Spacer) Presisi 15% -->
        <td style="width: 15%;"></td>
    </tr>
</table>

<!-- Garis Pembatas Ganda Standar Dokumen Resmi (Tebal & Tipis) -->
<div style="border-bottom: 3px solid #000000; margin-bottom: 2px; clear: both;"></div>
<div style="border-bottom: 1px solid #000000; margin-bottom: 15px;"></div>

<!-- Judul Dokumen -->
@if (!empty($judulDokumen))
<div style="text-align: center; margin-bottom: 8px;">
    <div style="font-family: Arial, sans-serif; font-size: 14px; font-weight: bold; color: #000000; text-transform: uppercase; letter-spacing: 1px;">
        {{ $judulDokumen }}
    </div>
</div>
@endif

<!-- Baris Informasi / Filter Parameter Dokumen -->
@if (!empty($infoBaris))
<div style="text-align: center; margin-bottom: 20px;">
    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #475569;">
        @foreach($infoBaris as $index => $item)
        <span style="font-weight: 600; color: #1e293b;">{{ $item }}</span>
        @if(!$loop->last)
        <span style="margin: 0 8px; color: #94a3b8;">|</span>
        @endif
        @endforeach
    </div>
</div>
@endif