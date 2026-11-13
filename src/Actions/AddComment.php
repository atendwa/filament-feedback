<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Actions;

use Atendwa\Feedback\Enums\Status;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Models\FeedbackComment;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class AddComment
{
    /**
     * @throws RuntimeException
     */
    public function handle(
        Feedback $feedback,
        string $body,
        ?Model $staff = null,
        bool $internal = false,
        bool $resolving = false,
    ): FeedbackComment {
        $fromSubmitter = ! $staff instanceof Model;

        throw_if(
            $fromSubmitter && ! $feedback->canBeRepliedTo(),
            'This conversation is not open for replies.',
        );
        throw_if(
            ! $fromSubmitter && ! $internal && ! $feedback->wants_replies,
            'The submitter chose not to receive replies, so only internal notes can be added.',
        );

        $comment = $feedback->comments()->create([
            'user_id' => $staff?->getKey(),
            'is_from_submitter' => $fromSubmitter,
            'is_internal' => $internal,
            'body' => $body,
        ]);

        $this->reopenIfFinished($feedback, $fromSubmitter);
        $feedback->touchActivity();

        (new AnnounceComment)->handle($feedback, $comment, $fromSubmitter, $internal, $resolving);

        return $comment;
    }

    private function reopenIfFinished(Feedback $feedback, bool $fromSubmitter): void
    {
        if ($fromSubmitter && $feedback->status->isFinished()) {
            $feedback->update(['status' => Status::Open, 'closed_at' => null]);
        }
    }
}
