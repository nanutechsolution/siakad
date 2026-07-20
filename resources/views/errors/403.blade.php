<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>

    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        }

        .float {
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-zinc-950 via-indigo-950 to-zinc-900 flex items-center justify-center px-6">

    <div class="max-w-xl w-full">

        <div class="
        backdrop-blur-xl
        bg-white/10
        border border-white/20
        rounded-3xl
        shadow-2xl
        p-10
        text-center
    ">

            <!-- Icon -->
            <div class="float mx-auto mb-8 flex items-center justify-center
                    w-28 h-28 rounded-full
                    bg-red-500/20
                    border border-red-400/30">

                <svg class="w-14 h-14 text-red-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M12 15v2m0-8v2m-7.5 9h15a2 2 0 001.8-2.8l-7.5-15a2 2 0 00-3.6 0l-7.5 15A2 2 0 004.5 20z" />
                </svg>

            </div>


            <!-- Code -->
            <h1 class="
            text-8xl
            font-black
            tracking-tight
            text-white
            mb-4
        ">
                403
            </h1>


            <h2 class="
            text-2xl
            font-bold
            text-white
            mb-3
        ">
                Akses Tidak Diizinkan
            </h2>


            <p class="
            text-zinc-300
            leading-relaxed
            mb-8
        ">
                Anda tidak memiliki hak akses untuk membuka halaman ini.
                Silakan hubungi administrator sistem jika Anda merasa ini adalah kesalahan.
            </p>


            @if(config('app.debug') && $exception->getMessage())

            <div class="
                mb-8
                text-left
                bg-black/30
                border border-white/10
                rounded-xl
                p-4
                text-sm
                text-zinc-300
            ">
                <strong class="text-red-300">
                    Detail:
                </strong>

                <div class="mt-2">
                    {{ $exception->getMessage() }}
                </div>
            </div>

            @endif


            <div class="flex flex-col sm:flex-row gap-3 justify-center">


                <a href="{{ url('/') }}"
                    class="
                px-6 py-3
                rounded-xl
                bg-indigo-500
                hover:bg-indigo-400
                text-white
                font-semibold
                transition
                shadow-lg
               ">
                    Kembali ke Dashboard
                </a>


                <button onclick="history.back()"
                    class="
                    px-6 py-3
                    rounded-xl
                    bg-white/10
                    hover:bg-white/20
                    border border-white/20
                    text-white
                    font-semibold
                    transition
                    ">
                    Halaman Sebelumnya
                </button>


            </div>


            <div class="mt-10 text-xs text-zinc-500">
                © {{ date('Y') }} {{ config('app.name') }}
                <br>
                Secure Academic Information System
            </div>


        </div>

    </div>

</body>

</html>