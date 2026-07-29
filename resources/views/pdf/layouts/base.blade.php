<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dokumen Resmi')</title>
    <style>
        @page {
            /* Margin standar dokumen resmi A4 */
            margin: 100px 40px 60px 40px;
        }

        body {
            /* Menggunakan font serif untuk kesan formal/akademik */
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.3;
        }

        /* HEADER FIXED */
        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
        }

        /* FOOTER FIXED */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 8pt;
            color: #555;
            border-top: 1px solid #999;
            padding-top: 5px;
            font-family: 'Helvetica', 'Arial', sans-serif;
            /* Font berbeda untuk footer sistem */
            text-align: center;
        }

        /* --- STRUKTUR KOP SURAT --- */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Kolom 1: Logo */
        .kop-table .logo-col {
            width: 15%;
            text-align: center;
            vertical-align: middle;
        }

        .kop-table .logo-col img {
            max-width: 70px;
            height: auto;
        }

        /* Kolom 2: Teks Utama */
        .kop-table .text-col {
            width: 70%;
            text-align: center;
            vertical-align: middle;
        }

        /* Kolom 3: Penyeimbang (Kosong agar kolom teks benar-benar di tengah) */
        .kop-table .dummy-col {
            width: 15%;
        }

        /* --- TIPOGRAFI KOP --- */
        .institusi {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0;
            letter-spacing: 0.5px;
        }

        .akreditasi {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .kontak {
            font-size: 9pt;
            margin: 0;
        }

        /* Garis ganda (Tebal atas, tipis bawah) lebih stabil di render PDF */
        .garis-ganda {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        /* --- STYLING TAMBAHAN --- */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10pt;
        }

        table.data th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    @php
    $kopSurat = app(\App\Services\Pdf\KopSuratResolver::class)->resolve();
    @endphp

    <header>
        <table class="kop-table">
            <tr>
                <td class="logo-col">
                    @if(!empty($kopSurat['logoAbsolutePath']) && file_exists($kopSurat['logoAbsolutePath']))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($kopSurat['logoAbsolutePath'])) }}" alt="Logo">
                    @endif
                </td>

                <td class="text-col">
                    <div class="institusi">{{ $kopSurat['nama'] ?? 'NAMA INSTITUSI' }}</div>

                    @if(!empty($kopSurat['akreditasi']))
                    <div class="akreditasi">
                        Terakreditasi "{{ $kopSurat['akreditasi'] }}"
                        @if(!empty($kopSurat['nomorAkreditasi']))
                        | SK: {{ $kopSurat['nomorAkreditasi'] }}
                        @endif
                    </div>
                    @endif

                    <div class="kontak">
                        {{ $kopSurat['alamat'] ?? '' }}
                    </div>
                    <div class="kontak">
                        Telp: {{ $kopSurat['telepon'] ?? '-' }} | Surel: {{ $kopSurat['email'] ?? '-' }} | Laman: {{ $kopSurat['website'] ?? '-' }}
                    </div>
                </td>

                <td class="dummy-col"></td>
            </tr>
        </table>

        <!-- Garis pembatas dipisah dari tabel agar merentang penuh -->
        <div class="garis-ganda"></div>
    </header>

    <footer>
        Dicetak melalui SIAKAD pada {{ now()->translatedFormat('d F Y H:i') }} WITA<br>
        Dokumen ini sah tanpa tanda tangan basah selama dicetak dari sistem resmi.
    </footer>

    <!-- Gunakan tag main dengan margin-top agar konten tidak tertutup header fixed -->
    <main style="padding-top: 70px;">
        @yield('content')
    </main>
</body>

</html>