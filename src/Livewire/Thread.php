<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Livewire;

use Atendwa\Feedback\Actions\AddComment;
use Atendwa\Feedback\Models\Feedback;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout(name: 'feedback::layouts.public')]
#[Title(content: 'Your feedback')]
class Thread extends Component
{
    public Feedback $feedback;

    public string $body = '';

    public function mount(Feedback $feedback): void
    {
        abort_if(! $feedback->wants_replies, 404);

        $this->feedback = $feedback;
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function reply(): void
    {
        $data = (array) $this->validate(['body' => ['required', 'string', 'min:2', 'max:5000']]);

        $key = 'feedback-reply:' . $this->feedback->uuid;

        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->addError('body', 'Please wait a little before sending more replies.');

            return;
        }

        RateLimiter::hit($key, 3600);

        app(AddComment::class)->handle($this->feedback, Arr::string($data, 'body'));

        $this->reset('body');
        $this->feedback->refresh();
    }

    public function render(): View
    {
        return view('feedback::livewire.thread', [
            'comments' => $this->feedback->comments()->where('is_internal', operator: false)->with('feedback')->get(),
        ]);
    }
}
