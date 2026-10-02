<?php

namespace App\Services\Pdf;

use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Renderer Chromium dengan kontrak yang sama seperti DomPdf ->output(),
 * sehingga PdfService tidak perlu diubah.
 */
class ChromiumPdf
{
    public function __construct(
        protected string $html,
        protected array $definition = [],
    ) {}

    public function output(): string
    {
        $config = config('pdf.chromium', []);

        $profileDir = sys_get_temp_dir() . '/sia-chromium-' . (function_exists('posix_geteuid') ? posix_geteuid() : 'x');
        File::ensureDirectoryExists($profileDir);

        $shot = Browsershot::html($this->html)
            ->showBackground()
            ->preferCssPageSize()
            ->format(strtoupper($this->definition['paper'] ?? 'a4'))
            ->timeout((int) ($config['timeout'] ?? 60))
            ->noSandbox()
            ->addChromiumArguments(['disable-dev-shm-usage', 'disable-gpu'])
            ->setEnvironmentOptions([
                'HOME' => $profileDir,
                'XDG_CONFIG_HOME' => $profileDir,
                'XDG_CACHE_HOME' => $profileDir,
            ]);

        if (($this->definition['orientation'] ?? 'portrait') === 'landscape') {
            $shot->landscape();
        }

        if (! empty($config['node_binary'])) {
            $shot->setNodeBinary($config['node_binary']);
        }

        if (! empty($config['npm_binary'])) {
            $shot->setNpmBinary($config['npm_binary']);
        }

        if (! empty($config['node_module_path'])) {
            $shot->setNodeModulePath($config['node_module_path']);
        }

        if (! empty($config['chrome_path'])) {
            $shot->setChromePath($config['chrome_path']);
        }

        return $shot->pdf();
    }
}
