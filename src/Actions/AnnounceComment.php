<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Actions;

use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Models\FeedbackComment;
use Atendwa\Feedback\Notifications\FeedbackReplied;
use Atendwa\Feedback\Notifications\StaffAlert;
use Atendwa\Feedback\Notifications\TeamAlert;
use Atendwa\Feedback\Support\Responders;
use Atendwa\Feedback\Support\TeamInbox;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use RuntimeException;

final class AnnounceComment
{
    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function handle(Feedback $feedback, FeedbackComment $comment, bool $fromSubmitter, bool $internal, bool $resolving): void
    {
        if ($fromSubmitter) {
            Notification::send(Responders::all(), new StaffAlert($feedback, 'New reply on ' . $feedback->reference()));
            TeamInbox::notify(new TeamAlert($feedback, 'New reply on ' . $feedback->reference()));
        } elseif (! $internal) {
            $this->tellSubmitter($feedback, $comment, $resolving);
        }
    }

    private function tellSubmitter(Feedback $feedback, FeedbackComment $comment, bool $resolving): void
    {
        $notification = new FeedbackReplied($feedback, $comment, $resolving);

        if (! $feedback->is_anonymous && $feedback->user !== null && method_exists($feedback->user, 'notify')) {
            $feedback->user->notify($notification);
        } elseif (filled($feedback->contact_email)) {
            Notification::route('mail', $feedback->contact_email)->notify($notification);
        }
    }
}
