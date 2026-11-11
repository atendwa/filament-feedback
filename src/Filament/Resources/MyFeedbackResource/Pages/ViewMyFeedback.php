<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Resources\MyFeedbackResource\Pages;

use Atendwa\Feedback\Actions\AddComment;
use Atendwa\Feedback\Filament\Resources\MyFeedbackResource;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Support\Toast;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Arr;
use LogicException;
use Throwable;

class ViewMyFeedback extends ViewRecord
{
    protected static string $resource = MyFeedbackResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')->label('Reply')->icon('heroicon-o-paper-airplane')->slideOver()
                ->visible(fn (): bool => $this->feedback()->canBeRepliedTo())
                ->schema([Textarea::make('body')->label('Your reply')->required()->rows(6)->maxLength(5000)])
                ->action(function (array $data): void {
                    try {
                        app(AddComment::class)->handle($this->feedback(), Arr::string($data, 'body'));
                        $this->feedback()->unsetRelation('comments');
                        Toast::success('Reply sent.');
                    } catch (Throwable $throwable) {
                        Toast::error($throwable);
                    }
                }),
        ];
    }

    private function feedback(): Feedback
    {
        $record = $this->getRecord();

        throw_if(! $record instanceof Feedback, LogicException::class);

        return $record;
    }
}
