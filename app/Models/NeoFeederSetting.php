<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan koneksi Neo Feeder (satu baris). Password & token terenkripsi.
 *
 * @property string|null $url
 * @property string|null $username
 * @property string|null $password
 * @property string|null $token
 * @property bool|null $verify_ssl
 * @property int|null $timeout
 * @property int|null $connect_timeout
 * @property \Illuminate\Support\Carbon|null $token_obtained_at
 */
class NeoFeederSetting extends Model
{
    protected $table = 'neo_feeder_settings';

    protected $fillable = [
        'url',
        'username',
        'password',
        'token',
        'verify_ssl',
        'timeout',
        'connect_timeout',
        'token_obtained_at',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'token' => 'encrypted',
        'verify_ssl' => 'boolean',
        'timeout' => 'integer',
        'connect_timeout' => 'integer',
        'token_obtained_at' => 'datetime',
    ];
}
