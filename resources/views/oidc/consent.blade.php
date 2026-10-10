<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Izinkan akses ke {{ $client->name }} — SIAKAD UNMARIS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            color-scheme: light;

            /* Palet identitas UNMARIS — sama dengan portal / (welcome) */
            --brand: #1e1b4b;
            /* indigo-950: biru tua */
            --brand-soft: #eef2ff;
            /* indigo-50 */
            --accent: #facc15;
            /* yellow-400 */
            --bg: #f1f5f9;
            /* slate-100 */
            --surface: #ffffff;
            --border: #e2e8f0;
            /* slate-200 */
            --text: #0f172a;
            /* slate-900 */
            --muted: #475569;
            /* slate-600 */
            --subtle: #64748b;
            /* slate-500 */
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        main {
            width: 100%;
            max-width: 480px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .08);
            padding: 32px;
            position: relative;
            overflow: hidden;
        }

        /* Aksen kuning tipis di atas kartu — identitas UNMARIS */
        .card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand), var(--accent));
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
        }

        .brand img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .brand span {
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .06em;
            color: var(--brand);
            text-transform: uppercase;
        }

        /* Identitas akun yang sedang login */
        .user {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--brand-soft);
            border: 1px solid #e0e7ff;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
        }

        .user .avatar {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--brand);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: .85rem;
        }

        .user .who {
            min-width: 0;
        }

        .user .who strong {
            display: block;
            font-size: .92rem;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user .who small {
            font-size: .78rem;
            color: var(--muted);
        }

        h1 {
            font-size: 1.25rem;
            line-height: 1.35;
            margin: 0 0 8px;
            color: var(--text);
        }

        h1 .app {
            color: var(--brand);
        }

        .lead {
            color: var(--muted);
            font-size: .92rem;
            line-height: 1.6;
            margin: 0 0 18px;
        }

        /* Daftar izin yang diminta */
        .scope-list {
            list-style: none;
            margin: 0 0 22px;
            padding: 0;
            display: grid;
            gap: 8px;
        }

        .scope-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
        }

        .scope-list .icon {
            flex-shrink: 0;
            margin-top: 1px;
            width: 18px;
            height: 18px;
            color: var(--brand);
        }

        .scope-list strong {
            display: block;
            font-size: .88rem;
            color: var(--text);
        }

        .scope-list span {
            font-size: .8rem;
            color: var(--subtle);
            line-height: 1.5;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions form {
            flex: 1;
        }

        button {
            width: 100%;
            font: inherit;
            font-weight: 600;
            font-size: .92rem;
            border-radius: 10px;
            padding: 12px 16px;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        button.primary {
            background: var(--brand);
            border: 1px solid var(--brand);
            color: #fff;
        }

        button.primary:hover {
            background: #312e81;
        }

        button.primary:active {
            background: #26235e;
        }

        button.secondary {
            background: var(--surface);
            border: 1px solid #cbd5e1;
            color: var(--text);
        }

        button.secondary:hover {
            background: #f8fafc;
            border-color: var(--subtle);
        }

        button:focus-visible {
            outline: 2px solid var(--brand);
            outline-offset: 2px;
        }

        .security {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            font-size: .76rem;
            line-height: 1.6;
            color: var(--subtle);
        }

        .security .icon {
            flex-shrink: 0;
            width: 15px;
            height: 15px;
            margin-top: 2px;
            color: var(--brand);
        }

        /* Mobile: tombol menumpuk penuh lebar */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 24px 20px;
            }

            .actions {
                flex-direction: column;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            button {
                transition: none;
            }
        }
    </style>
</head>

<body>
    <main>
        <section class="card" aria-labelledby="consent-heading">

            {{-- Kepala kartu: identitas resmi SIAKAD UNMARIS --}}
            <div class="brand">
                <img src="{{ asset('images/logo-unmaris.png') }}" alt="Logo UNMARIS" width="34" height="34">
                <span>SIAKAD UNMARIS</span>
            </div>

            {{-- Siapa yang sedang login (hanya identitas tampilan, tanpa data sensitif) --}}
            <div class="user">
                <div class="avatar" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr(trim($user->name ?? $user->username), 0, 1)) }}
                </div>
                <div class="who">
                    <strong>{{ $user->name ?? $user->username }}</strong>
                    <small>{{ $user->username ?? '' }}</small>
                </div>
            </div>

            <h1 id="consent-heading">Izinkan akses ke <span class="app">{{ $client->name }}</span>?</h1>

            <p class="lead">
                Anda masuk menggunakan akun SIAKAD UNMARIS.
                <strong>{{ $client->name }}</strong> meminta izin untuk menerima
                informasi akun Anda agar dapat mengenali pengguna dan
                menyediakan layanan yang Anda butuhkan.
            </p>

            {{-- Izin yang diminta — hanya scope yang benar-benar diminta client,
                 dipetakan ke label manusiawi; scope tak dikenal tetap tampil aman. --}}
            @php
                $scopeMap = [
                    'openid' => [
                        'label' => 'Autentikasi akun SIAKAD',
                        'desc' => 'Aplikasi mengenali Anda sebagai pengguna SIAKAD UNMARIS.',
                    ],
                    'profile' => [
                        'label' => 'Informasi profil',
                        'desc' => 'Nama lengkap Anda.',
                    ],
                    'email' => [
                        'label' => 'Alamat email',
                        'desc' => 'Email akun SIAKAD dan status verifikasinya.',
                    ],
                    'siakad_identity' => [
                        'label' => 'Identitas institusi',
                        'desc' => 'NIM, NIDN, NUPTK, atau NIP sesuai yang tersedia pada akun Anda.',
                    ],
                    'account_status' => [
                        'label' => 'Status akun',
                        'desc' => 'Status aktif/tidaknya akun Anda di SIAKAD.',
                    ],
                ];
            @endphp

            <ul class="scope-list">
                @foreach ($scopes as $scope)
                    @php
                        $known = $scopeMap[$scope->id] ?? null;
                        $label = $known['label'] ?? $scope->id;
                        $desc = $known['desc'] ?? $scope->description;
                    @endphp
                    <li>
                        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <div>
                            <strong>{{ $label }}</strong>
                            <span>{{ $desc }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Aksi: dua form POST terpisah — mekanisme persetujuan Passport
                 (CSRF + auth_token sesi) dipertahankan apa adanya. --}}
            <div class="actions">
                <form method="POST" action="{{ route('oauth.approve') }}">
                    @csrf
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="primary">Izinkan dan Lanjutkan</button>
                </form>

                <form method="POST" action="{{ route('oauth.deny') }}">
                    @csrf
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="secondary">Tolak</button>
                </form>
            </div>

            <p class="security">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3 5 6v5c0 4.6 3 8.4 7 9.7 4-1.3 7-5.1 7-9.7V6l-7-3Z" />
                </svg>
                <span>
                    Kata sandi SIAKAD Anda tidak dibagikan kepada aplikasi ini.
                    Izin akses yang telah diberikan dapat dikelola melalui
                    pengaturan SSO apabila fitur tersebut tersedia.
                </span>
            </p>
        </section>
    </main>
</body>

</html>
