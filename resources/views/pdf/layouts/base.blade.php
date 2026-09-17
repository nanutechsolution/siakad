<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">

    <title>@yield('title', 'Dokumen Resmi')</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        |
        | Ruang atas sengaja dibuat cukup besar untuk menampung kop surat.
        | Jangan menggunakan padding-top pada <main>.
        |
        */

        @page {
            size: A4 landscape;
            margin: 145px 40px 65px 40px;
        }

        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        body {
            margin: 0;
            padding: 0;

            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;

            line-height: 1.3;
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER / KOP SURAT
        |--------------------------------------------------------------------------
        |
        | Header fixed akan dicetak ulang DomPDF pada setiap halaman.
        |
        | top negatif membuat header masuk ke area margin atas.
        | Konten tetap berada di bawah karena @page margin-top.
        |
        */

        header {
            position: fixed;

            top: -115px;
            left: 0;
            right: 0;

            width: 100%;
        }

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        footer {
            position: fixed;

            bottom: -45px;
            left: 0;
            right: 0;

            height: 30px;

            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;

            color: #555;

            text-align: center;

            border-top: 1px solid #999;

            padding-top: 5px;
        }

        /*
        |--------------------------------------------------------------------------
        | KOP SURAT
        |--------------------------------------------------------------------------
        */

        .kop-table {
            width: 100%;

            border-collapse: collapse;

            margin: 0;
            padding: 0;
        }

        /*
        | Logo
        */

        .kop-table .logo-col {
            width: 15%;

            text-align: center;
            vertical-align: middle;

            padding: 0;
        }

        .kop-table .logo-col img {
            display: block;

            max-width: 70px;
            height: auto;

            margin: 0 auto;
        }

        /*
        | Teks utama
        */

        .kop-table .text-col {
            width: 70%;

            text-align: center;
            vertical-align: middle;

            padding: 0;
        }

        /*
        | Kolom penyeimbang
        */

        .kop-table .dummy-col {
            width: 15%;

            padding: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | TYPOGRAPHY KOP
        |--------------------------------------------------------------------------
        */

        .institusi {
            font-size: 16pt;

            font-weight: bold;

            text-transform: uppercase;

            margin: 0 0 2px 0;

            letter-spacing: 0.5px;
        }

        .akreditasi {
            font-size: 10pt;

            font-weight: bold;

            margin: 0 0 4px 0;
        }

        .kontak {
            font-size: 9pt;

            margin: 0;

            padding: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | GARIS KOP
        |--------------------------------------------------------------------------
        */

        .garis-ganda {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;

            height: 2px;

            margin-top: 10px;
            margin-bottom: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | MAIN CONTENT
        |--------------------------------------------------------------------------
        |
        | TIDAK menggunakan padding-top.
        |
        | Jarak dengan header dikendalikan sepenuhnya oleh @page margin-top.
        |
        */

        main {
            margin: 0;
            padding: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | DATA TABLE
        |--------------------------------------------------------------------------
        */

        table.data {
            width: 100%;

            border-collapse: collapse;

            margin-top: 10px;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;

            padding: 5px 8px;

            font-size: 10pt;

            vertical-align: middle;
        }

        table.data th {
            background-color: #f2f2f2;

            font-weight: bold;

            text-align: center;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE PAGINATION
        |--------------------------------------------------------------------------
        |
        | Header tabel akan diulang pada halaman berikutnya.
        |
        */

        table.data thead {
            display: table-header-group;
        }

        table.data tbody {
            display: table-row-group;
        }

        /*
        | Hindari satu baris tabel terpotong menjadi dua halaman.
        */

        table.data tr {
            page-break-inside: avoid;
        }

        /*
        |--------------------------------------------------------------------------
        | DAY GROUP
        |--------------------------------------------------------------------------
        */

        .hari-row {
            page-break-after: avoid;
            page-break-before: auto;
        }

        /*
        |--------------------------------------------------------------------------
        | TEXT
        |--------------------------------------------------------------------------
        */

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /*
        |--------------------------------------------------------------------------
        | MARGIN
        |--------------------------------------------------------------------------
        */

        .mt-10 {
            margin-top: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        /*
        |--------------------------------------------------------------------------
        | UTILITY
        |--------------------------------------------------------------------------
        */

        .no-border {
            border: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */

        .page-break {
            page-break-after: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    @php
    $kopSurat = app(\App\Services\Pdf\KopSuratResolver::class)->resolve();
    @endphp


    {{-- ================================================================
         HEADER / KOP
         ================================================================ --}}

    <header>

        <table class="kop-table">

            <tr>

                {{-- LOGO --}}

                <td class="logo-col">

                    @if(
                    !empty($kopSurat['logoAbsolutePath'])
                    && file_exists($kopSurat['logoAbsolutePath'])
                    )

                    <img
                        src="data:image/png;base64,{{ base64_encode(
                                file_get_contents($kopSurat['logoAbsolutePath'])
                            ) }}"
                        alt="Logo">

                    @endif

                </td>


                {{-- INFORMASI INSTITUSI --}}

                <td class="text-col">

                    <div class="institusi">

                        {{ $kopSurat['nama'] ?? 'NAMA INSTITUSI' }}

                    </div>


                    @if(!empty($kopSurat['akreditasi']))

                    <div class="akreditasi">

                        "{{ $kopSurat['akreditasi'] }}"

                        @if(!empty($kopSurat['nomorAkreditasi']))

                        | SK: {{ $kopSurat['nomorAkreditasi'] }}

                        @endif

                    </div>

                    @endif


                    <div class="kontak">

                        {{ $kopSurat['alamat'] ?? '' }}

                    </div>


                    <div class="kontak">

                        Telp:
                        {{ $kopSurat['telepon'] ?? '-' }}

                        |

                        Surel:
                        {{ $kopSurat['email'] ?? '-' }}

                        |

                        Laman:
                        {{ $kopSurat['website'] ?? '-' }}

                    </div>

                </td>


                {{-- KOLOM PENYEIMBANG --}}

                <td class="dummy-col"></td>

            </tr>

        </table>


        {{-- GARIS KOP --}}

        <div class="garis-ganda"></div>

    </header>


    {{-- ================================================================
         FOOTER
         ================================================================ --}}

    <footer>

        Dicetak melalui SIAKAD pada
        {{ now()->translatedFormat('d F Y H:i') }}
        WITA

    </footer>


    {{-- ================================================================
         CONTENT
         ================================================================ --}}

    <main>

        @yield('content')

    </main>

</body>

</html>