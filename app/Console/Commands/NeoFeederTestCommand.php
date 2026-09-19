<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\NeoFeeder\NeoFeederAuthenticator;
use App\Services\NeoFeeder\NeoFeederConnectionTester;
use Illuminate\Console\Command;

final class NeoFeederTestCommand extends Command
{
    protected $signature = 'neo-feeder:test
                            {--login : Ambil token baru lewat GetToken (dan simpan) sebelum test}';

    protected $description = 'Uji koneksi & autentikasi ke Neo Feeder (GetProdi limit 1, tanpa menyimpan data)';

    public function handle(NeoFeederAuthenticator $authenticator): int
    {
        if ($this->option('login')) {
            $this->components->info('Mengambil token (GetToken)...');

            $login = $authenticator->refreshToken();

            if (! $login->isConnected()) {
                $this->components->error($login->message());

                if ($login->technicalDetail !== null) {
                    $this->line('Detail teknis (hanya tampil di CLI): ' . $login->technicalDetail);
                }

                $this->line('Detail lengkap: storage/logs/laravel.log (cari "Neo Feeder")');

                return self::FAILURE;
            }

            $this->components->info('Token berhasil diambil dan disimpan (nilai token tidak ditampilkan).');
            $this->newLine();
        }

        // Di-resolve SETELAH login supaya memakai token terbaru.
        $tester = $this->laravel->make(NeoFeederConnectionTester::class);

        $this->components->info('Menguji koneksi ke Neo Feeder (GetProdi, limit 1, offset 0)...');

        $result = $tester->test();

        if ($result->isConnected()) {
            $this->components->info($result->message());
            $this->newLine();
            $this->line('Diagnostik response (tanpa isi data dan tanpa token):');

            foreach ($result->diagnostics as $key => $value) {
                $this->line(sprintf(
                    '  %-22s %s',
                    $key,
                    json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ));
            }

            return self::SUCCESS;
        }

        $this->components->error($result->message());

        if ($result->technicalDetail !== null) {
            $this->line('Detail teknis (hanya tampil di CLI): ' . $result->technicalDetail);
        }

        $this->line('Detail lengkap: storage/logs/laravel.log (cari "Neo Feeder")');

        return self::FAILURE;
    }
}
