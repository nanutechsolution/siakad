<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Status Pembayaran</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family:
                Inter,
                system-ui,
                sans-serif;

            background:
                linear-gradient(135deg,
                    #eff6ff,
                    #f8fafc,
                    #ecfeff);
        }


        .card {

            width: 420px;
            max-width: 90%;

            background: white;

            border-radius: 24px;

            padding: 40px 32px;

            text-align: center;

            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, .15);

            animation:
                fade .5s ease;

        }


        @keyframes fade {

            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }

        }



        .icon {

            width: 90px;
            height: 90px;

            margin: auto;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 42px;

            background: #dbeafe;

            animation: pulse 2s infinite;

        }



        @keyframes pulse {

            50% {
                transform: scale(1.08);
            }

        }



        h2 {

            margin-top: 25px;

            color: #0f172a;

        }



        .desc {

            color: #64748b;

            line-height: 1.6;

        }



        .steps {

            margin-top: 30px;

            text-align: left;

        }



        .step {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px;

            margin-bottom: 10px;

            border-radius: 12px;

            background: #f8fafc;

            color: #64748b;

            font-size: 14px;

        }



        .dot {

            width: 22px;
            height: 22px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #cbd5e1;

            color: white;

            font-size: 12px;

        }



        .active .dot {

            background: #2563eb;

        }



        .done .dot {

            background: #16a34a;

        }



        .success-box {

            background: #dcfce7;

            color: #166534;

            padding: 15px;

            border-radius: 14px;

            margin-top: 25px;

        }



        .btn {

            display: block;

            margin-top: 25px;

            padding: 14px;

            border-radius: 12px;

            background: #2563eb;

            color: white;

            text-decoration: none;

            font-weight: 600;

        }


        .small {

            margin-top: 15px;

            color: #94a3b8;

            font-size: 13px;

        }



        .spinner {

            width: 40px;

            height: 40px;

            margin: 25px auto;

            border-radius: 50%;

            border:

                4px solid #dbeafe;

            border-top-color: #2563eb;

            animation: spin 1s linear infinite;

        }


        @keyframes spin {

            to {
                transform: rotate(360deg);
            }

        }
    </style>

</head>


<body>


    <div class="card">


        <div id="icon" class="icon">
            ⏳
        </div>


        <h2 id="judul">
            Memverifikasi Pembayaran
        </h2>


        <p id="pesan" class="desc">

            Pembayaran Anda sudah diterima.
            Kami sedang melakukan pengecekan otomatis.

        </p>



        <div class="spinner" id="spinner"></div>



        <div class="steps">


            <div class="step done">

                <div class="dot">
                    ✓
                </div>

                Pembayaran diterima

            </div>



            <div class="step active" id="stepVerifikasi">

                <div class="dot">
                    2
                </div>

                Verifikasi Midtrans

            </div>



            <div class="step" id="stepUpdate">

                <div class="dot">
                    3
                </div>

                Update status tagihan

            </div>



        </div>



        <div
            id="success"
            class="success-box"
            style="display:none">

            ✅ Pembayaran berhasil diverifikasi.
            Tagihan Anda sudah diperbarui.

        </div>



        <a
            id="kembali"
            href="{{ url('/mahasiswa/tagihan-non-regulers') }}"
            class="btn"
            style="display:none">

            Kembali ke Tagihan

        </a>



        <div class="small">

            Jangan tutup halaman ini.

        </div>


    </div>



    <script>
        const orderId = @json($orderId);



        const cekStatus = setInterval(() => {


            fetch(`/pembayaran/midtrans/status/${orderId}`)

                .then(res => res.json())

                .then(data => {


                    if (
                        data.status === 'settlement' ||
                        data.status === 'capture'
                    ) {


                        clearInterval(cekStatus);



                        document.getElementById('icon')
                            .innerHTML = "✅";


                        document.getElementById('icon')
                            .style.background = "#dcfce7";



                        document.getElementById('judul')
                            .innerHTML = "Pembayaran Berhasil";



                        document.getElementById('pesan')
                            .innerHTML =
                            "Pembayaran telah diverifikasi oleh sistem.";



                        document.getElementById('spinner')
                            .style.display = "none";



                        document.getElementById('stepVerifikasi')
                            .className = "step done";


                        document.getElementById('stepVerifikasi')
                            .innerHTML =
                            '<div class="dot">✓</div> Verifikasi Midtrans';



                        document.getElementById('stepUpdate')
                            .className = "step done";


                        document.getElementById('stepUpdate')
                            .innerHTML =
                            '<div class="dot">✓</div> Status tagihan diperbarui';



                        document.getElementById('success')
                            .style.display = "block";



                        document.getElementById('kembali')
                            .style.display = "block";


                    }


                });



        }, 3000);
    </script>


</body>

</html>