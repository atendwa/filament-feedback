<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Actions;

use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Notifications\FeedbackReceived;
use Atendwa\Feedback\Notifications\StaffAlert;
use Atendwa\Feedback\Notifications\TeamAlert;
use Atendwa\Feedback\Support\ContactEmail;
use Atendwa\Feedback\Support\Fields;
use Atendwa\Feedback\Support\Responders;
use Atendwa\Feedback\Support\TeamInbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;

final class SubmitFeedback
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public function handle(array $data, ?Model $user = null): Feedback
    {
        $anonymous = (bool) ($data['is_anonymous'] ?? false);
        $wantsReplies = (bool) ($data['wants_replies'] ?? false);
        $email = ContactEmail::for($data, $user);

        $feedback = Feedback::query()->create([
            'category' => Fields::text($data, 'category'),
            'title' => Arr::string($data, 'title'),
            'message' => Arr::string($data, 'message'),
            'user_id' => $user?->getKey(),
            'is_anonymous' => $anonymous,
            'name' => $anonymous || $user instanceof Model ? null : Fields::text($data, 'name'),
            'wants_replies' => $wantsReplies,
            'contact_email' => $email,
        ]);

        if (filled($email)) {
            Notification::route('mail', $email)->notify(new FeedbackReceived($feedback));
        }

        Notification::send(Responders::all(), new StaffAlert($feedback, 'New feedback: ' . $feedback->title));
        TeamInbox::notify(new TeamAlert($feedback, 'New feedback ' . $feedback->reference() . ': ' . $feedback->title));

        return $feedback;
    }
}
