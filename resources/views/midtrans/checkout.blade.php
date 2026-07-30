<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Midtrans</title>
    <script
        src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ $clientKey }}"></script>
</head>

<body>
    <p style="font-family: sans-serif; text-align: center; margin-top: 3rem;">
        Membuka halaman pembayaran...
    </p>

    <script>
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function() {
                window.location.href = '{{ $kembaliUrl }}';
            },
            onPending: function() {
                window.location.href = '{{ $kembaliUrl }}';
            },
            onError: function() {
                window.location.href = '{{ $kembaliUrl }}';
            },
            onClose: function() {
                // Mahasiswa menutup popup tanpa menyelesaikan pembayaran —
                // tidak ada apa pun yang perlu dibatalkan di sisi kita,
                // karena belum ada baris pembayaran tercatat sampai
                // webhook pertama masuk (lihat MidtransWebhookController).
                window.location.href = '{{ $kembaliUrl }}';
            },
        });
    </script>
</body>

</html>