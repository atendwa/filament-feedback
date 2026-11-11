<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Actions;

use Atendwa\Feedback\Enums\Status;
use Atendwa\Feedback\Models\Feedback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class ChangeStatus
{
    public function resolve(Feedback $feedback, string $comment, Model $staff): void
    {
        DB::transaction(function () use ($feedback, $comment, $staff): void {
            app(AddComment::class)->handle($feedback, $comment, $staff, internal: ! $feedback->wants_replies, resolving: true);

            $feedback->update(['status' => Status::Resolved, 'closed_at' => now()]);
            $feedback->touchActivity();
        });
    }

    public function reopen(Feedback $feedback): void
    {
        $feedback->update(['status' => Status::Open, 'closed_at' => null]);
        $feedback->touchActivity();
    }
}
