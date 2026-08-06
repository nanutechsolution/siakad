<x-filament-widgets::widget>
    <x-filament::section>
        @php
        $record = $this->getRecord();
        $steps = \App\Enums\TahunAkademikStatus::cases();
        $currentIndex = array_search($record->status, $steps, true);
        @endphp

        <div class="rounded-2xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm p-6">
            {{-- Horizontal stepper --}}
            <ol class="flex items-center w-full mb-6">
                @foreach ($steps as $i => $step)
                @php
                $done = $i < $currentIndex;
                    $active=$i===$currentIndex;
                    @endphp
                    <li class="flex items-center {{ ! $loop->last ? 'flex-1' : '' }}">
                    <div class="flex flex-col items-center gap-y-1.5">
                        <div @class([ 'flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold shrink-0 transition-colors' , 'bg-success-500 text-white'=> $done,
                            'bg-primary-500 text-white ring-4 ring-primary-500/20' => $active,
                            'bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500' => ! $done && ! $active,
                            ])>
                            @if ($done)
                            <x-heroicon-s-check class="h-4 w-4" />
                            @else
                            {{ $i + 1 }}
                            @endif
                        </div>
                        <span @class([ 'text-[11px] font-medium text-center whitespace-nowrap' , 'text-gray-950 dark:text-white'=> $active,
                            'text-gray-500 dark:text-gray-400' => ! $active,
                            ])>
                            {{ $step->getLabel() }}
                        </span>
                    </div>

                    @if (! $loop->last)
                    <div @class([ 'h-0.5 flex-1 mx-2 -mt-5 rounded-full' , 'bg-success-500'=> $done,
                        'bg-gray-100 dark:bg-white/10' => ! $done,
                        ])></div>
                    @endif
                    </li>
                    @endforeach
            </ol>

            {{-- Current stage panel --}}
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-4">
                <div class="flex items-start justify-between gap-x-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-primary-600 dark:text-primary-400">
                            Tahap Saat Ini
                        </p>
                        <p class="mt-0.5 text-lg font-semibold text-gray-950 dark:text-white">
                            {{ $record->status->getLabel() }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $record->status->description() }}
                        </p>
                    </div>
                    <x-filament::badge :color="$record->status->getColor()">
                        {{ $record->status->progressPercent() }}%
                    </x-filament::badge>
                </div>

                <p class="text-xs text-gray-400 mt-3">
                    Gunakan tombol aksi di bagian atas halaman untuk melanjutkan ke tahap berikutnya.
                </p>
            </div>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>