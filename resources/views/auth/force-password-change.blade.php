<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Password</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1rem; background: #f8fafc; font: 16px system-ui, sans-serif; color: #0f172a; }
        main { width: min(100%, 28rem); padding: 2rem; background: #fff; border: 1px solid #e2e8f0; border-radius: .75rem; box-shadow: 0 10px 25px #0f172a12; }
        label { display: block; margin-top: 1rem; font-weight: 600; }
        input { display: block; width: 100%; box-sizing: border-box; margin-top: .4rem; padding: .7rem; border: 1px solid #94a3b8; border-radius: .4rem; font: inherit; }
        button { width: 100%; margin-top: 1.5rem; padding: .75rem; border: 0; border-radius: .4rem; background: #1d4ed8; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        .error { margin-top: .75rem; color: #b91c1c; }
    </style>
</head>
<body>
<main>
    <h1>Ganti password</h1>
    <p>Password Anda direset oleh administrator. Buat password baru untuk melanjutkan.</p>
    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('password.force-change.store') }}">
        @csrf
        <label for="password">Password baru</label>
        <input id="password" name="password" type="password" minlength="12" required autocomplete="new-password">
        <label for="password_confirmation">Konfirmasi password baru</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" required autocomplete="new-password">
        <button type="submit">Simpan password</button>
    </form>
</main>
</body>
</html>
