<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Actions;

use Atendwa\Feedback\Actions\SubmitFeedback;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Support\Toast;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Throwable;

final class GiveFeedback extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-chat-bubble-left-right')
            ->label('Give Feedback')
            ->slideOver()
            ->color('gray')
            ->modalHeading('Give feedback')
            ->modalDescription('Tell us what works, what does not, and what you would like to see.')
            ->modalSubmitActionLabel('Send feedback')
            ->schema($this->fields())
            ->action(fn (array $data) => $this->sendFeedback($data));
    }

    public static function getDefaultName(): string
    {
        return 'feedback';
    }

    /**
     * @return array<int, Component|Select|TextInput|Textarea|Radio|Toggle>
     */
    private function fields(): array
    {
        $anonymous = fn (Get $get): bool => (bool) $get('is_anonymous');

        return [
            Select::make('category')->options(Feedback::categories())->native(condition: false),
            TextInput::make('title')->label('Subject')->required()->maxLength(120),
            Textarea::make('message')->label('Your feedback')->required()->minLength(10)->maxLength(5000)->rows(6),
            $this->visibilityField(),
            $this->anonymityNotice()->visible($anonymous),
            Toggle::make('wants_replies')
                ->label('I want to read replies and follow up')
                ->live()
                ->helperText('Turn this off and the team cannot answer you.'),
            TextInput::make('contact_email')
                ->label('Email me the private link')
                ->email()
                ->helperText('Optional. Without it the link is shown once after you send.')
                ->visible(fn (Get $get): bool => $anonymous($get) && (bool) $get('wants_replies')),
        ];
    }

    private function anonymityNotice(): Callout
    {
        return Callout::make('Anonymous is not untraceable')
            ->description('Staff will never see your name, but your account is recorded privately so abuse can be stopped. Only a system administrator with direct database access could look it up. Your only way back to an anonymous conversation is the private link.')
            ->warning();
    }

    private function visibilityField(): Radio
    {
        return Radio::make('is_anonymous')
            ->label('Who can see this?')
            ->live()
            ->default(0)
            ->required()
            ->options([
                0 => 'Share my name',
                1 => 'Stay anonymous',
            ])
            ->descriptions([
                0 => 'The team sees who wrote it and can answer you in the system.',
                1 => 'The team sees a nickname instead of your name.',
            ]);
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function sendFeedback(array $data): void
    {
        try {
            $feedback = app(SubmitFeedback::class)->handle(
                [...$data, 'is_anonymous' => (bool) ($data['is_anonymous'] ?? false)],
                auth()->user(),
            );

            $this->thanks($feedback)->send();
        } catch (Throwable $throwable) {
            Toast::error($throwable);
        }
    }

    private function thanks(Feedback $feedback): Notification
    {
        $notification = Notification::make()
            ->success()
            ->title('Thank you for your feedback')
            ->body('Reference ' . $feedback->reference() . '.');

        return $feedback->wants_replies && $feedback->is_anonymous
            ? $notification->persistent()
                ->body('Reference ' . $feedback->reference() . '. Save your private link: it is the only way back to this conversation.')
                ->actions([
                    Action::make('open')->label('Open private link')->url($feedback->threadUrl(), shouldOpenInNewTab: true),
                ])
            : $notification;
    }
}
