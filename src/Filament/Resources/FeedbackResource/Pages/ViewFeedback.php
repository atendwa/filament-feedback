<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Resources\FeedbackResource\Pages;

use Atendwa\Feedback\Actions\AddComment;
use Atendwa\Feedback\Filament\Actions\StatusActions;
use Atendwa\Feedback\Filament\Resources\FeedbackResource;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Support\Toast;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use LogicException;
use Throwable;

class ViewFeedback extends ViewRecord
{
    protected static string $resource = FeedbackResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label('Back')->icon('heroicon-o-arrow-left')->color('gray')
                ->url(FeedbackResource::getUrl('index')),
            $this->commentAction('reply', 'Reply', 'heroicon-o-paper-airplane', internal: false)
                ->visible(fn (): bool => Gate::allows('respond', Feedback::class) && $this->feedback()->canBeRepliedTo())
                ->modalDescription('The submitter is told by email, or in the system if they gave their name.'),
            $this->commentAction('note', 'Internal note', 'heroicon-o-lock-closed', internal: true)
                ->color('gray')
                ->visible(fn (): bool => Gate::allows('respond', Feedback::class))
                ->modalDescription('Only staff can read notes.'),
            StatusActions::resolve($this->feedback(...)),
            StatusActions::reopen($this->feedback(...)),
        ];
    }

    private function feedback(): Feedback
    {
        $record = $this->getRecord();

        throw_if(! $record instanceof Feedback, LogicException::class);

        return $record;
    }

    private function commentAction(string $name, string $label, string $icon, bool $internal): Action
    {
        return Action::make($name)->label($label)->icon($icon)->slideOver()
            ->schema([Textarea::make('body')->label($internal ? 'Note' : 'Your reply')->required()->rows(6)->maxLength(5000)])
            ->action(function (array $data) use ($internal): void {
                try {
                    app(AddComment::class)->handle($this->feedback(), Arr::string($data, 'body'), auth()->user(), $internal);
                    $this->feedback()->unsetRelation('comments');
                    Toast::success($internal ? 'Note added.' : 'Reply sent.');
                } catch (Throwable $throwable) {
                    Toast::error($throwable);
                }
            });
    }
}
