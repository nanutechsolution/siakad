<div
    x-data="{
        open: false,
        selectedIndex: -1,

        init() {
            this.$watch('$wire.search', (value) => {
                this.open = value.length > 0
                this.selectedIndex = -1
            })

            window.addEventListener('keydown', (event) => {
                const target = event.target
                const isInput = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable

                if (
                    ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') ||
                    (event.key === '/' && !isInput)
                ) {
                    event.preventDefault()
                    this.open = true
                    this.$nextTick(() => {
                        this.$refs.search?.focus()
                    })
                }

                if (event.key === 'Escape' && this.open) {
                    this.open = false
                    $wire.clearSearch()
                }
            })
        },

        navigateResults(direction) {
            const itemsCount = {{ count($this->filteredItems) }}
            if (itemsCount === 0) return

            if (direction === 'down') {
                this.selectedIndex = (this.selectedIndex + 1) % itemsCount
            } else if (direction === 'up') {
                this.selectedIndex = (this.selectedIndex - 1 + itemsCount) % itemsCount
            }

            const activeItem = this.$refs.resultsList?.children[this.selectedIndex]
            if (activeItem) {
                activeItem.scrollIntoView({ block: 'nearest' })
            }
        },

        selectCurrent() {
            if (this.selectedIndex >= 0) {
                const activeItem = this.$refs.resultsList?.children[this.selectedIndex]
                if (activeItem) {
                    activeItem.click()
                }
            }
        }
    }"
    @click.outside="open = false"
    class="relative z-30 px-3 pb-3"
>
    {{-- SEARCH INPUT CONTAINER --}}
    <div
        @click="$refs.search?.focus()"
        class="group flex items-center gap-2.5 rounded-xl border border-gray-200 bg-white px-3 py-2.5 shadow-sm transition-all duration-200 hover:border-gray-300 hover:shadow focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-500/10 dark:border-white/10 dark:bg-gray-900 dark:hover:border-white/20 dark:focus-within:border-primary-400 dark:focus-within:ring-primary-400/10"
    >
        {{-- Search Icon --}}
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 transition-colors group-focus-within:bg-primary-50 group-focus-within:text-primary-600 dark:bg-white/10 dark:text-gray-400 dark:group-focus-within:bg-primary-500/20 dark:group-focus-within:text-primary-400">
            <x-filament::icon
                icon="heroicon-o-magnifying-glass"
                class="h-4 w-4"
            />
        </div>

        {{-- Input Field --}}
        <input
            x-ref="search"
            type="text"
            wire:model.live.debounce.250ms="search"
            @focus="open = search.length > 0"
            @keydown.arrow-down.prevent="navigateResults('down')"
            @keydown.arrow-up.prevent="navigateResults('up')"
            @keydown.enter.prevent="selectCurrent()"
            placeholder="Cari menu..."
            autocomplete="off"
            spellcheck="false"
            class="min-w-0 flex-1 border-none bg-transparent p-0 text-sm font-medium text-gray-900 outline-none ring-0 placeholder:font-normal placeholder:text-gray-400 focus:border-none focus:outline-none focus:ring-0 dark:text-white dark:placeholder:text-gray-500"
        />

        {{-- Clear / Shortcut Indicator --}}
        @if (filled($search))
            <button
                type="button"
                wire:click="clearSearch"
                @click="open = false"
                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"
                aria-label="Bersihkan pencarian"
            >
                <x-filament::icon
                    icon="heroicon-m-x-mark"
                    class="h-3.5 w-3.5"
                />
            </button>
        @else
            <kbd class="hidden shrink-0 items-center rounded-md border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-gray-400 sm:flex dark:border-white/10 dark:bg-white/10 dark:text-gray-400">
                ⌘K
            </kbd>
        @endif
    </div>

    {{-- SEARCH RESULTS POPUP --}}
    @if (filled($search))
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-x-3 top-full z-50 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl ring-1 ring-black/10 dark:border-gray-700 dark:bg-gray-900 dark:ring-white/10"
        >
            {{-- Result Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-3.5 py-2 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-400">
                    Hasil pencarian
                </div>

                @if (count($this->filteredItems) > 0)
                    <div class="text-[11px] font-medium text-gray-400 dark:text-gray-400">
                        {{ count($this->filteredItems) }} menu
                    </div>
                @endif
            </div>

            {{-- Results List --}}
            <div
                x-ref="resultsList"
                class="max-h-[min(24rem,calc(100vh-14rem))] overflow-y-auto bg-white p-1.5 dark:bg-gray-900"
            >
                @forelse ($this->filteredItems as $index => $item)
                    <a
                        href="{{ $item['url'] }}"
                        wire:navigate
                        @click="open = false; $wire.clearSearch();"
                        :class="{
                            'bg-gray-100 dark:bg-gray-800': selectedIndex === {{ $index }}
                        }"
                        class="group/item flex items-center gap-3 rounded-lg px-2.5 py-2.5 transition-colors duration-100 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        {{-- Icon --}}
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 transition-all group-hover/item:bg-primary-50 group-hover/item:text-primary-600 dark:bg-gray-800 dark:text-gray-300 dark:group-hover/item:bg-primary-500/20 dark:group-hover/item:text-primary-400">
                            <x-filament::icon
                                :icon="$item['icon'] ?? 'heroicon-o-squares-2x2'"
                                class="h-4.5 w-4.5"
                            />
                        </div>

                        {{-- Text --}}
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $item['label'] }}
                            </div>

                            @if (filled($item['group']))
                                <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['group'] }}
                                </div>
                            @endif
                        </div>

                        {{-- Arrow --}}
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 opacity-0 transition-all group-hover/item:bg-gray-200/60 group-hover/item:text-gray-600 group-hover/item:opacity-100 dark:group-hover/item:bg-gray-700 dark:group-hover/item:text-gray-200">
                            <x-filament::icon
                                icon="heroicon-m-arrow-up-right"
                                class="h-3.5 w-3.5"
                            />
                        </div>
                    </a>
                @empty
                    {{-- Empty State --}}
                    <div class="bg-white px-5 py-8 text-center dark:bg-gray-900">
                        <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-400">
                            <x-filament::icon
                                icon="heroicon-o-magnifying-glass"
                                class="h-5 w-5"
                            />
                        </div>

                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            Menu tidak ditemukan
                        </p>

                        <p class="mt-1 text-xs text-gray-400">
                            Tidak ada menu yang cocok dengan
                            <span class="font-medium text-gray-600 dark:text-gray-300">"{{ $search }}"</span>
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Footer Hints --}}
            @if (count($this->filteredItems) > 0)
                <div class="flex items-center gap-3 border-t border-gray-100 bg-gray-50/80 px-3.5 py-2 text-[10px] font-medium text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                        <kbd class="rounded border border-gray-200 bg-white px-1 py-0.5 font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">↑↓</kbd>
                        Pilih
                    </span>

                    <span class="flex items-center gap-1">
                        <kbd class="rounded border border-gray-200 bg-white px-1 py-0.5 font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">↵</kbd>
                        Buka
                    </span>

                    <span class="flex items-center gap-1">
                        <kbd class="rounded border border-gray-200 bg-white px-1 py-0.5 font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Esc</kbd>
                        Tutup
                    </span>
                </div>
            @endif
        </div>
    @endif
</div>