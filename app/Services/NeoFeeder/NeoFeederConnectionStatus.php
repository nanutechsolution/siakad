<?php

declare(strict_types=1);

namespace App\Services\NeoFeeder;

use App\Services\NeoFeeder\Exceptions\NeoFeederApiException;
use App\Services\NeoFeeder\Exceptions\NeoFeederConfigurationException;
use App\Services\NeoFeeder\Exceptions\NeoFeederHttpException;
use Throwable;

enum NeoFeederConnectionStatus: string
{
    case Connected = 'connected';
    case Unreachable = 'unreachable';
    case Rejected = 'rejected';
    case NotConfigured = 'not_configured';

    public function message(): string
    {
        return match ($this) {
            self::Connected => 'Neo Feeder berhasil terhubung.',
            self::Unreachable => 'Neo Feeder tidak dapat dihubungi.',
            self::Rejected => 'Neo Feeder menolak request.',
            self::NotConfigured => 'Pengaturan Neo Feeder belum lengkap.',
        };
    }

    public static function fromException(Throwable $e): self
    {
        return match (true) {
            $e instanceof NeoFeederConfigurationException => self::NotConfigured,
            $e instanceof NeoFeederApiException => self::Rejected,
            $e instanceof NeoFeederHttpException && $e->isAuthRelated() => self::Rejected,
            default => self::Unreachable,
        };
    }
}
