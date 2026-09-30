<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Izinkan akses — SIAKAD UNMARIS</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
            width: 100%;
            max-width: 460px;
            padding: 28px;
        }

        h1 {
            font-size: 1.15rem;
            margin: 0 0 6px;
        }

        p {
            color: #475569;
            font-size: .92rem;
            line-height: 1.55;
            margin: 0 0 14px;
        }

        ul {
            margin: 0 0 18px;
            padding-left: 20px;
            color: #334155;
            font-size: .9rem;
        }

        li {
            margin-bottom: 4px;
        }

        .user {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: .85rem;
            margin-bottom: 18px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        button {
            flex: 1;
            font: inherit;
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
        }

        button.primary {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }

        .foot {
            margin-top: 16px;
            font-size: .78rem;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Izinkan akses ke {{ $client->name }}</h1>
        <p><strong>{{ $user->name ?? $user->username }}</strong> masuk dengan akun SIAKAD. Aplikasi
            <strong>{{ $client->name }}</strong> meminta izin berikut:</p>

        <ul>
            @foreach ($scopes as $scope)
                <li>{{ $scope->description }} <code>({{ $scope->id }})</code></li>
            @endforeach
        </ul>

        <div class="user">
            Akun: {{ $user->username ?? $user->name }}<br>
            Nama: {{ $user->name }}
        </div>

        <form method="POST" action="{{ route('oauth.approve') }}">
            @csrf
            <input type="hidden" name="auth_token" value="{{ $authToken }}">

            <div class="actions">
                <button type="submit" class="primary">Setujui &amp; lanjutkan</button>
            </div>
        </form>

        <form method="POST" action="{{ route('oauth.deny') }}" style="margin-top:10px">
            @csrf
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <div class="actions">
                <button type="submit">Tolak</button>
            </div>
        </form>

        <p class="foot">SIAKAD tidak pernah menampilkan atau meneruskan password Anda ke aplikasi ini.
            Anda dapat mencabut akses kapan saja dari halaman pengaturan SSO admin.</p>
    </div>
</body>

</html>
