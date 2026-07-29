<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">

    <title>@yield('title', 'Dokumen Resmi')</title>

    <style>
        @page {
            margin: 125px 45px 65px 45px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #111;
        }


        /*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

        header {
            position: fixed;
            top: -105px;
            left: 0;
            right: 0;
            height: 95px;
        }


        .kop {
            width: 100%;
            border-bottom: 4px double #000;
            padding-bottom: 8px;
        }


        .kop table {
            width: 100%;
            border-collapse: collapse;
        }


        .logo {
            width: 90px;
            vertical-align: middle;
        }


        .logo img {
            width: 70px;
        }


        .institusi {
            text-align: center;
            vertical-align: middle;
        }


        .institusi h1 {

            font-size: 17px;
            font-weight: bold;

            letter-spacing: .5px;

            margin: 0;

            text-transform: uppercase;

        }


        .institusi h2 {

            font-size: 12px;

            margin: 2px 0;

            font-weight: bold;

        }


        .akreditasi {

            font-size: 9px;

            margin-top: 4px;

        }


        .alamat {

            font-size: 9px;

            line-height: 14px;

        }


        .kontak {

            font-size: 8.5px;

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

            height: 35px;

            border-top: 1px solid #aaa;

            padding-top: 5px;

            font-size: 8px;

            color: #555;

        }


        .footer-left {

            float: left;

        }


        .footer-right {

            float: right;

        }



        /*
|--------------------------------------------------------------------------
| CONTENT
|--------------------------------------------------------------------------
*/


        .content {

            margin-top: 20px;

        }


        table.data {

            width: 100%;

            border-collapse: collapse;

        }


        table.data th {

            background: #f0f0f0;

            font-weight: bold;

        }


        table.data th,
        table.data td {

            border: 1px solid #999;

            padding: 5px;

            font-size: 10px;

        }



        .text-center {
            text-align: center;
        }


        .text-right {
            text-align: right;
        }


        .page-break {
            page-break-after: always;
        }
    </style>

</head>


<body>


    @php

    $kopSurat = app(
    \App\Services\Pdf\KopSuratResolver::class
    )->resolve();

    @endphp



    <header>

        <div class="kop">

            <table>

                <tr>


                    <td class="logo">

                        @if(
                        isset($kopSurat['logoAbsolutePath'])
                        &&
                        file_exists($kopSurat['logoAbsolutePath'])
                        )

                        <img src="data:image/png;base64,
{{base64_encode(file_get_contents($kopSurat['logoAbsolutePath']))}}">


                        @endif

                    </td>



                    <td class="institusi">


                        <h1>
                            {{ strtoupper($kopSurat['nama']) }}
                        </h1>


                        <h2>
                            SISTEM INFORMASI AKADEMIK
                        </h2>



                        <div class="akreditasi">

                            Terakreditasi
                            {{ $kopSurat['akreditasi'] }}

                            @if(!empty($kopSurat['nomorAkreditasi']))

                            <br>

                            Nomor SK :
                            {{ $kopSurat['nomorAkreditasi'] }}

                            @endif

                        </div>



                        <div class="alamat">

                            {{ $kopSurat['alamat'] }}

                        </div>



                        <div class="kontak">

                            Telp:
                            {{ $kopSurat['telepon'] }}

                            |

                            Email:
                            {{ $kopSurat['email'] }}

                            |

                            {{ $kopSurat['website'] }}

                        </div>


                    </td>


                    <td width="40px"></td>


                </tr>

            </table>

        </div>


    </header>




    <footer>


        <div class="footer-left">

            SIAKAD UNMARIS

        </div>


        <div class="footer-right">

            Dicetak:
            {{ now()->translatedFormat('d F Y H:i') }}

        </div>


    </footer>





    <div class="content">


        @yield('content')


    </div>


</body>

</html>