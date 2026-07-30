<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pembayaran Aman - Midtrans</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script
        src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ $clientKey }}">
    </script>

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
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
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

            animation: fade .5s ease;
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


        .logo {
            width: 72px;
            height: 72px;

            margin: auto;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(135deg,
                    #2563eb,
                    #06b6d4);

            color: white;
            font-size: 32px;

            animation: pulse 2s infinite;
        }


        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.08);
            }

        }


        h2 {
            margin-top: 24px;
            color: #0f172a;
            font-size: 24px;
        }


        p {
            color: #64748b;
            line-height: 1.6;
        }


        .loader {
            margin: 30px auto;
            width: 48px;
            height: 48px;

            border-radius: 50%;

            border:
                5px solid #e2e8f0;

            border-top-color: #2563eb;

            animation:
                spin 1s linear infinite;
        }


        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }


        .secure {
            margin-top: 25px;

            padding: 12px;

            border-radius: 12px;

            background: #f1f5f9;

            color: #475569;

            font-size: 14px;
        }


        .secure span {
            color: #16a34a;
            font-weight: 600;
        }


        .cancel {
            margin-top: 15px;
            font-size: 13px;
            color: #94a3b8;
        }
    </style>

</head>


<body>


    <div class="card">


        <div class="logo">
            💳
        </div>


        <h2>
            Menyiapkan Pembayaran
        </h2>


        <p>
            Anda akan diarahkan ke halaman pembayaran aman Midtrans.
        </p>


        <div class="loader"></div>


        <div class="secure">
            🔒 Pembayaran aman melalui
            <span>Midtrans</span>
        </div>


        <div class="cancel">
            Jangan tutup halaman ini sampai popup pembayaran muncul.
        </div>


    </div>



    <script>
        const snapToken = @json($snapToken);
        const kembaliUrl = @json($kembaliUrl);


        setTimeout(() => {

            window.snap.pay(snapToken, {

                onSuccess: function(result) {

                    window.location.href = kembaliUrl;

                },


                onPending: function(result) {

                    window.location.href = kembaliUrl;

                },


                onError: function(result) {

                    window.location.href = kembaliUrl;

                },


                onClose: function() {

                    document.querySelector('.logo').innerHTML = "⚠️";

                    document.querySelector('h2').innerHTML =
                        "Pembayaran dibatalkan";

                    document.querySelector('p').innerHTML =
                        "Anda menutup halaman pembayaran sebelum selesai.";

                    document.querySelector('.loader').style.display = "none";

                    document.querySelector('.cancel').innerHTML =
                        `
        <a href="/mahasiswa/tagihan-non-regulers"
           style="
            display:inline-block;
            margin-top:15px;
            padding:12px 20px;
            background:#2563eb;
            color:white;
            border-radius:10px;
            text-decoration:none;
           ">
           Kembali ke Tagihan
        </a>
        `;
                }

            });


        }, 700);
    </script>


</body>

</html>