<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Status Pembayaran</title>

    <style>
        body {
            font-family: sans-serif;
            background: #f8fafc;
        }

        .box {
            max-width: 450px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 5px 20px #0002;
        }

        .success {
            color: #16a34a;
        }

        .loading {
            color: #2563eb;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>

</head>

<body>


    <div class="box">

        <h2 id="judul" class="loading">
            ⏳ Memverifikasi pembayaran...
        </h2>


        <p id="pesan">
            Pembayaran berhasil diterima.
            Sistem sedang memperbarui status tagihan Anda.
        </p>


        <div id="loading">
            Mohon tunggu...
        </div>


        <a
            id="kembali"
            href="{{ url('/mahasiswa/tagihan-non-regulers') }}"
            class="btn"
            style="display:none">
            Kembali ke Tagihan
        </a>


    </div>


    <script>
        const orderId = "{{ $orderId }}";


        const cekStatus = setInterval(() => {

            fetch(`/pembayaran/midtrans/status/${orderId}`)
                .then(res => res.json())
                .then(data => {


                    if (
                        data.status === 'settlement' ||
                        data.status === 'capture'
                    ) {

                        document.getElementById('judul').innerHTML =
                            "✅ Pembayaran Berhasil";


                        document.getElementById('judul')
                            .className = "success";


                        document.getElementById('pesan').innerHTML =
                            "Pembayaran Anda sudah diverifikasi. Tagihan telah diperbarui.";


                        document.getElementById('loading')
                            .style.display = 'none';


                        document.getElementById('kembali')
                            .style.display = 'inline-block';


                        clearInterval(cekStatus);
                    }


                });


        }, 3000);
    </script>


</body>

</html>