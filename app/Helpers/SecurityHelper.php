<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Str;

class SecurityHelper
{
    /**
     * Generate password kuat (Min 12 char, huruf besar, kecil, angka, simbol).
     */
    public static function generateStrongPassword(): string
    {
        return Str::password(length: 12, letters: true, numbers: true, symbols: true, spaces: false);
    }
}
