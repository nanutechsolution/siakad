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
        console.log("SNAP TOKEN:", "{{ $snapToken }}");
        console.log("KEMBALI URL:", "{{ $kembaliUrl }}");

        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) {
                console.log("SUCCESS", result);
                window.location.href = '{{ $kembaliUrl }}';
            },

            onPending: function(result) {
                console.log("PENDING", result);
                window.location.href = '{{ $kembaliUrl }}';
            },

            onError: function(result) {
                console.log("ERROR", result);
                window.location.href = '{{ $kembaliUrl }}';
            },

            onClose: function() {
                console.log("CLOSED");
            },
        });
    </script>
</body>

</html>