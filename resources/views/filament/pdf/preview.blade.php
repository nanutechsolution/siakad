@if (! empty($error))
<div style="padding:16px;border:1px solid #fbbf24;background:#fffbeb;color:#92400e;border-radius:8px;">
    {{ $error }}
</div>
@else
<div
    data-preview-url="{{ $previewUrl }}"
    x-data="{
            cetak() {
                try {
                    this.$refs.frame.contentWindow.focus();
                    this.$refs.frame.contentWindow.print();
                } catch (e) {
                    window.open(this.$root.dataset.previewUrl, '_blank');
                }
            },
        }"
    style="display:flex;flex-direction:column;gap:12px;">
    <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;">
        <x-filament::button type="button" color="primary" icon="heroicon-o-printer" x-on:click="cetak()">
            Cetak
        </x-filament::button>

        <x-filament::button tag="a" :href="$downloadUrl" color="gray" icon="heroicon-o-arrow-down-tray">
            Unduh PDF
        </x-filament::button>
    </div>

    <iframe
        x-ref="frame"
        src="{{ $previewUrl }}#toolbar=0&navpanes=0&view=FitH"
        title="Pratinjau {{ $judul }}"
        style="width:100%;height:75vh;border:1px solid #d1d5db;border-radius:8px;background:#fff;"></iframe>
</div>
@endif