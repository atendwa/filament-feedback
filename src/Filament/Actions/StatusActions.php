<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Actions;

use Atendwa\Feedback\Actions\ChangeStatus;
use Atendwa\Feedback\Enums\Status;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Support\Toast;
use Atendwa\Feedback\Support\Users;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use LogicException;
use Throwable;

final class StatusActions
{
    /**
     * @param  Closure(): Feedback  $feedback
     */
    public static function resolve(Closure $feedback): Action
    {
        return Action::make('resolve')->label('Resolve')->icon('heroicon-o-check-circle')->color('success')
            ->visible(fn (): bool => Gate::allows('respond', Feedback::class) && $feedback()->status === Status::Open)
            ->modalDescription(fn (): string => $feedback()->wants_replies ? 'Your comment is sent to the submitter as the last reply.' : 'The submitter opted out of replies, so your comment is kept as an internal note.')
            ->schema([Textarea::make('comment')->label('Resolution comment')->required()->rows(6)->maxLength(5000)])
            ->action(function (array $data) use ($feedback): void {
                try {
                    $staff = Users::current();

                    throw_if(! $staff instanceof Model, LogicException::class);

                    app(ChangeStatus::class)->resolve($feedback(), Arr::string($data, 'comment'), $staff);
                    $feedback()->unsetRelation('comments');
                    Toast::success('Marked as resolved.');
                } catch (Throwable $throwable) {
                    Toast::error($throwable);
                }
            });
    }

    /**
     * @param  Closure(): Feedback  $feedback
     */
    public static function reopen(Closure $feedback): Action
    {
        return Action::make('reopen')->label('Reopen')->icon('heroicon-o-arrow-path')->color('gray')->requiresConfirmation()
            ->visible(fn (): bool => Gate::allows('respond', Feedback::class) && $feedback()->status === Status::Resolved)
            ->action(function () use ($feedback): void {
                app(ChangeStatus::class)->reopen($feedback());
                Toast::success('Reopened.');
            });
    }
}
