<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as Notifier;

final class TeamInbox
{
    /**
     * @return list<string>
     */
    public static function addresses(): array
    {
        $configured = config('feedback.notify_email');

        return array_values(collect(explode(',', is_string($configured) ? $configured : ''))
            ->map(fn (string $address): string => mb_trim($address))
            ->filter(fn (string $address): bool => filter_var($address, FILTER_VALIDATE_EMAIL) !== false)
            ->all());
    }

    public static function notify(Notification $notification): void
    {
        if (self::addresses() !== []) {
            Notifier::route('mail', self::addresses())->notify($notification);
        }
    }
}
