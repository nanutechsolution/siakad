@php
use Illuminate\Support\Facades\Auth;

$settings = app(\App\Settings\KampusSettings::class);

$prioritas = [
'Rektor',
'Wakil Rektor',
'Dekan',
'Kaprodi',
'BAAK',
'Admin Akademik',
'Admin Fakultas',
'Admin Prodi',
'Admin PMB',
'Admin Keuangan',
'Kasir',
'Verifikator Pembayaran',
'Admin SDM',
'Admin LPM',
'Admin LPPM',
'Pustakawan',
'Dosen',
'Mahasiswa',
];

$roles = Auth::user()?->getRoleNames()->toArray() ?? [];

$role = collect($prioritas)
->first(fn ($item) => in_array($item, $roles))
?? 'Administrator';
@endphp
<div class="flex items-center gap-2.5">
    <img
        src="{{ $settings->logo_path
            ? Storage::url($settings->logo_path)
            : asset('images/logo-unmaris.png') }}"
        class="h-9 w-9 object-contain">

    <div class="flex flex-col leading-tight">
        <span class="font-display text-base font-semibold">
            {{ strtoupper($role) }}
        </span>

        <span class="text-[10px] uppercase tracking-widest text-horizon-600">
            {{ strtoupper($settings->nama_singkat) }}
        </span>
    </div>
</div>