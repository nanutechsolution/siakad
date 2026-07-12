<x-filament-widgets::widget>
    <x-filament::section>

        <x-slot name="heading">
            Akses Portal
        </x-slot>


        <x-slot name="description">
            Pilih layanan yang sesuai dengan kewenangan akun Anda.
        </x-slot>


        <div class="grid gap-4 md:grid-cols-2">

            @foreach($portals as $portal)

            <a href="{{ $portal['url'] }}"
                class="group rounded-xl border p-5 transition hover:shadow-lg hover:border-primary-500">


                <div class="flex items-start gap-4">

                    <div class="rounded-lg bg-primary-50 p-3">

                        <x-dynamic-component
                            :component="$portal['icon']"
                            class="h-7 w-7 text-primary-600" />

                    </div>


                    <div>

                        <h3 class="font-bold text-lg">
                            {{ $portal['name'] }}
                        </h3>


                        <p class="text-sm text-gray-500 mt-1">
                            {{ $portal['description'] }}
                        </p>


                        <div class="mt-4 text-primary-600 text-sm font-medium">
                            Masuk Portal →
                        </div>

                    </div>

                </div>


            </a>

            @endforeach


        </div>


    </x-filament::section>
</x-filament-widgets::widget>