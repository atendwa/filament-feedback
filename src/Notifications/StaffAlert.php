<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Notifications;

use Atendwa\Feedback\Models\Feedback;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class StaffAlert extends Notification
{
    public function __construct(public Feedback $feedback, public string $title) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title($this->title)
            ->body(Str::limit($this->feedback->message, 120))
            ->getDatabaseMessage();
    }
}
