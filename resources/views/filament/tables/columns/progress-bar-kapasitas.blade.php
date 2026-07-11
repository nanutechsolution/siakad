@php
    $jadwal = $getRecord()->jadwalKuliah;
    $isi = $jadwal ? $jadwal->isi_kelas : 0;
    $kuota = $jadwal ? $jadwal->kuota_kelas : 1;
    $percent = min(100, ($isi / $kuota) * 100);
    $color = $percent >= 100 ? 'bg-danger-600' : ($percent >= 80 ? 'bg-warning-500' : 'bg-primary-600');
@endphp

@if($jadwal)
    <div class="flex items-center space-x-2">
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 max-w-[100px]">
            <div class="{{ $color }} h-2.5 rounded-full" style="width: {{ $percent }}%"></div>
        </div>
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ $isi }}/{{ $kuota }}
        </span>
    </div>
@else
    <span class="text-xs text-gray-500">-</span>
@endif