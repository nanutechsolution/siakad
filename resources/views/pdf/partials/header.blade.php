@php
$kampus = app(\App\Settings\KampusSettings::class);
$logoFullPath = $kampus->logo_path ? storage_path('app/public/'.$kampus->logo_path) : null;
@endphp

<table style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
    <tr>
        <td style="width: 70px; vertical-align: middle;">
            @if ($logoFullPath && file_exists($logoFullPath))
            <img src="{{ $logoFullPath }}" style="width: 60px; height: 60px;">
            @endif
        </td>
        <td style="vertical-align: middle; text-align: center;">
            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase;">
                {{ $kampus->nama }}
            </div>
            @if ($kampus->akreditasi)
            <div style="font-size: 9px; color: #4b5563;">Terakreditasi {{ $kampus->akreditasi }}</div>
            @endif
            <div style="font-size: 9px; color: #4b5563;">{{ $kampus->alamat }}</div>
            <div style="font-size: 9px; color: #4b5563;">
                Telp: {{ $kampus->telepon }} | Email: {{ $kampus->email }} | {{ $kampus->website }}
            </div>
        </td>
        <td style="width: 70px;"></td>
    </tr>
</table>

<div style="border-top: 2.5px solid #1f2937; border-bottom: 0.75px solid #1f2937; height: 3px; margin-bottom: 10px;"></div>

@if (!empty($judulDokumen))
<div style="text-align: center; margin-bottom: 12px;">
    <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; text-decoration: underline;">
        {{ $judulDokumen }}
    </div>
</div>
@endif

@if (!empty($infoBaris))
<div style="text-align: center; margin-bottom: 12px; font-size: 9px; color: #4b5563;">
    {{ implode(' &nbsp;|&nbsp; ', $infoBaris) }}
</div>
@endif