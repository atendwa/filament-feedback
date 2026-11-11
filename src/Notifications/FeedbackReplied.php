<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Notifications;

use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Models\FeedbackComment;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class FeedbackReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public Feedback $feedback, public FeedbackComment $comment, public bool $resolved = false) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof Model ? ['mail', 'database'] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(($this->resolved ? 'Your feedback was resolved (' : 'A reply to your feedback (') . $this->feedback->reference() . ')')
            ->greeting(($this->resolved ? 'We have resolved "' : 'We have replied to "') . $this->feedback->title . '"')
            ->line(Str::limit($this->comment->body, 300))
            ->action('Read and reply', $this->feedback->threadUrl());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(($this->resolved ? 'Resolved: your feedback ' : 'Reply to your feedback ') . $this->feedback->reference())
            ->body(Str::limit($this->comment->body, 120))
            ->getDatabaseMessage();
    }
}
