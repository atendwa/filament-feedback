<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use RuntimeException;
use Throwable;

final class Toast
{
    public static function success(string $title): void
    {
        Notification::make()->title($title)->success()->send();
    }

    public static function error(Throwable $throwable): void
    {
        $readable = $throwable::class === RuntimeException::class || $throwable instanceof AuthorizationException;

        if (! $readable) {
            report($throwable);
        }

        Notification::make()
            ->title('Something went wrong')
            ->body($readable ? $throwable->getMessage() : 'Please try again, and contact support if the problem continues.')
            ->danger()
            ->persistent()
            ->send();
    }
}
