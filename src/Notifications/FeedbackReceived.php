<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Notifications;

use Atendwa\Feedback\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public Feedback $feedback) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We received your feedback (' . $this->feedback->reference() . ')')
            ->greeting('Thank you for telling us what you think')
            ->line('"' . $this->feedback->title . '"')
            ->line('Use the button below to read our replies and to write back. Keep this email: the link is private to you and does not expire.')
            ->action('Open your conversation', $this->feedback->threadUrl())
            ->line($this->feedback->is_anonymous
                ? 'Our staff only see the nickname "' . $this->feedback->alias . '", never your name.'
                : 'Our staff can see your name on this feedback.');
    }
}
