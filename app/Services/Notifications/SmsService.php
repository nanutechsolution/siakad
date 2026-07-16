<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS to phone number.
     * This is a simple adapter that currently logs the message.
     * Replace implementation with provider (Twilio, Nexmo, etc.) as needed.
     */
    public function send(string $phone, string $message): bool
    {
        // Basic validation
        if (empty($phone)) {
            Log::warning('SMS not sent: empty phone number', ['message' => $message]);
            return false;
        }

        // TODO: integrate real SMS gateway here. For now, log the SMS.
        Log::info('SMS sent (logged)', [
            'to' => $phone,
            'message' => $message,
        ]);

        return true;
    }
}
