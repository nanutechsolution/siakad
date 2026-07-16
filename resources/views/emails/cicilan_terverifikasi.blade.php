<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cicilan Diverifikasi</title>
</head>
<body>
    <p>Halo {{ $mahasiswa->person->nama_lengkap ?? 'Mahasiswa' }},</p>

    <p>{!! nl2br(e($body)) !!}</p>

    <p>Silakan kunjungi halaman tagihan untuk melihat detail dan instruksi pembayaran.</p>

    <p>Salam,<br/>Bagian Keuangan</p>
</body>
</html>
