<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\WilayahSourceInterface;
use App\Services\Wilayah\WilayahImporter;
use Illuminate\Console\Command;
use Throwable;

final class WilayahImportCommand extends Command
{
    protected $signature = 'wilayah:import';

    protected $description = 'Import master wilayah Indonesia tanpa menghapus data yang ada';

    public function handle(WilayahSourceInterface $source, WilayahImporter $importer): int
    {
        $this->components->info('UNMARIS WILAYAH IMPORT');
        $this->line('Source: ' . (string) config('wilayah.source_url'));
        $this->newLine();

        try {
            $result = $importer->import($source);
        } catch (Throwable $exception) {
            $this->components->error('Import gagal: ' . $exception->getMessage());
            return self::FAILURE;
        }

        foreach ($result['summary'] as $level => $counts) {
            $this->line(ucfirst($level) . ':');
            $this->line('  Inserted: ' . $counts['inserted']);
            $this->line('  Updated: ' . $counts['updated']);
        }

        $this->newLine();
        $this->line('Errors: ' . count($result['errors']));
        $this->line('Status: SUCCESS');

        return self::SUCCESS;
    }
}
