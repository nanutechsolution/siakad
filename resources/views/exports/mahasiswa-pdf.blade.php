@extends('pdf.layouts.base')

@section('title', 'Daftar Data Mahasiswa')

@section('content')
<style>
    .tabel-data {
        width: 100%;
        border-collapse: collapse;
        font-size: 8px;
    }

    .tabel-data thead {
        display: table-header-group;
    }

    .tabel-data tr {
        page-break-inside: avoid;
    }

    .tabel-data th {
        background: #4F46E5;
        color: #ffffff;
        font-weight: bold;
        text-align: left;
        padding: 4px 3px;
        border: 1px solid #4338CA;
    }

    .tabel-data td {
        padding: 3px;
        border: 1px solid #D1D5DB;
        vertical-align: top;
    }

    .tabel-data tbody tr:nth-child(even) td {
        background: #F9FAFB;
    }

    .tabel-data .kolom-no {
        text-align: center;
        width: 4%;
    }
</style>

@include('pdf.partials.kop-header')

<table class="tabel-data">
    <thead>
        <tr>
            <th class="kolom-no">No</th>
            @foreach ($headings as $heading)
            <th>{{ $heading }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $index => $row)
        <tr>
            <td class="kolom-no">{{ $index + 1 }}</td>
            @foreach ($row as $cell)
            <td>{{ $cell }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@endsection