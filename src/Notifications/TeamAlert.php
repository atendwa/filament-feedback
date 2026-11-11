<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Notifications;

use Atendwa\Feedback\Filament\Resources\FeedbackResource;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Support\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TeamAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public Feedback $feedback, public string $headline) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->headline)
            ->greeting($this->headline)
            ->line($this->feedback->category !== null ? 'Topic: ' . $this->feedback->category : 'No topic chosen')
            ->line('"' . Str::limit($this->feedback->message, 300) . '"')
            ->line($this->feedback->wants_replies ? 'The submitter wants replies.' : 'The submitter opted out of replies.')
            ->action('Open the feedback', FeedbackResource::getUrl('view', ['record' => $this->feedback], tenant: Tenant::resolve()));
    }
}
