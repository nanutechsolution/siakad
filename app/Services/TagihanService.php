<?php

namespace App\Services;

use App\Jobs\GenerateTagihanJob;
use Illuminate\Support\Facades\Auth;

class TagihanService
{
    /**
     * Memasukkan proses generate tagihan ke dalam antrean Laravel Queue
     */
    public function generate(array $data): array
    {
        try {
            $userId = Auth::id();

            // Melempar proses berat ke background (Queue)
            GenerateTagihanJob::dispatch($data, $userId);

            return [
                'status' => 'success',
                'mode'   => 'queue',
                'message' => 'Proses pembuatan tagihan telah dimasukkan ke antrean sistem. Silakan tunggu beberapa saat.'
            ];
            

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Gagal memulai antrean sistem: ' . $e->getMessage()
            ];
        }
    }
}