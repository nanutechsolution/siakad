<script>
    // Registrasi service worker. Inline dan idempoten: berjalan di ketiga
    // panel serta halaman publik tanpa bergantung pada build Vite.
    // Dilompati bila konteks tidak aman, SW tidak didukung, atau sedang
    // mode dev Vite (laravel-vite-plugin menanam script client dari public/hot).
    (function () {
        if (!('serviceWorker' in navigator) || !window.isSecureContext) return;

        // Laravel Vite dev server menyuntikkan script client di halaman.
        if (document.querySelector('script[src*="vite/client"]')) return;

        window.addEventListener('load', function () {
            navigator.serviceWorker
                .register('/sw.js', { scope: '/' })
                .catch(function () {
                    // Gagal registrasi tidak boleh mengganggu aplikasi.
                });
        });
    })();
</script>
