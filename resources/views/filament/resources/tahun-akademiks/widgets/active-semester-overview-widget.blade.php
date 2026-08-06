<x-filament-widgets::widget>
    <x-filament::section>
        @php $semester = $this->getSemester(); @endphp

        <div class="relative overflow-hidden rounded-2xl border border-gray-950/5 dark:border-white/10
                bg-white dark:bg-gray-900 shadow-sm">
            {{-- subtle gradient accent --}}
            <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-br from-primary-500/10 via-transparent to-transparent pointer-events-none"></div>

            <div class="relative p-6">
                @if (!$semester)
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <x-heroicon-o-calendar-days class="h-10 w-10 text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Belum ada semester aktif</p>
                    <p class="text-xs text-gray-400 mt-1">Buat draft semester baru untuk memulai siklus akademik.</p>
                </div>
                @else
                <div class="flex flex-col gap-y-5">
                    <div class="flex items-start justify-between gap-x-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Semester Aktif</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                {{ $semester->kode_tahun }}
                                <span class="font-normal text-gray-400">·</span>
                                {{ $semester->nama_tahun }}
                            </h2>
                        </div>

                        <x-filament::badge :color="$semester->status->getColor()" size="lg">
                            <div class="flex items-center gap-x-1">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-current opacity-60"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-current"></span>
                                </span>
                                {{ $semester->status->getLabel() }}
                            </div>
                        </x-filament::badge>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                            <span>Progress Akademik</span>
                            <span class="font-medium tabular-nums">{{ $semester->status->progressPercent() }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-400 transition-all duration-500"
                                style="width: {{ $semester->status->progressPercent() }}%"></div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach (\App\Enums\TahunAkademikStatus::cases() as $step)
                        @php
                        $reached = $step->progressPercent() <= $semester->status->progressPercent();
                            $isCurrent = $step === $semester->status;
                            @endphp
                            <div class="flex items-center gap-x-1.5 text-xs {{ $isCurrent ? 'font-semibold text-primary-600 dark:text-primary-400' : ($reached ? 'text-gray-600 dark:text-gray-300' : 'text-gray-350 dark:text-gray-600') }}">
                                @if ($reached && ! $isCurrent)
                                <x-heroicon-s-check-circle class="h-3.5 w-3.5 text-success-500" />
                                @elseif ($isCurrent)
                                <span class="h-2 w-2 rounded-full bg-primary-500"></span>
                                @else
                                <span class="h-2 w-2 rounded-full border border-gray-300 dark:border-gray-600"></span>
                                @endif
                                {{ $step->getLabel() }}
                            </div>
                            @endforeach
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <p class="text-xs text-gray-400">
                            {{ $semester->status->description() }}
                        </p>
                        <x-filament::button
                            tag="a"
                            
                            :href="route('filament.admin.resources.tahun-akademiks.view', $semester)"
                            icon="heroicon-o-arrow-right"
                            icon-position="after">
                            Kelola Semester
                        </x-filament::button>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>