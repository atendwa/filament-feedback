<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Livewire;

use Atendwa\Feedback\Actions\SubmitFeedback;
use Atendwa\Feedback\Models\Feedback;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout(name: 'feedback::layouts.public')]
#[Title(content: 'Give feedback')]
class SubmitForm extends Component
{
    public string $category = '';

    public string $title = '';

    public string $message = '';

    public string $is_anonymous = '0';

    public string $name = '';

    public bool $wants_replies = false;

    public string $contact_email = '';

    public string $website = '';

    public bool $submitted = false;

    public bool $emailed = false;

    public string $reference = '';

    public ?string $threadUrl = null;

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function submit(): void
    {
        $data = (array) $this->validate($this->validationRules());

        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        if ($this->throttled()) {
            $this->addError('throttle', 'You have sent a lot of feedback recently. Please try again later.');

            return;
        }

        $this->showReceipt(app(SubmitFeedback::class)->handle($data, auth()->user()));
    }

    public function again(): void
    {
        $this->reset();
    }

    public function render(): View
    {
        return view('feedback::livewire.submit-form', [
            'categories' => config()->array('feedback.categories'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function validationRules(): array
    {
        return [
            'category' => ['nullable', Rule::in(config()->array('feedback.categories'))],
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'is_anonymous' => ['required', 'in:0,1'],
            'name' => ['nullable', 'string', 'max:120'],
            'wants_replies' => ['boolean'],
            'contact_email' => ['nullable', 'email', 'max:190'],
        ];
    }

    private function throttled(): bool
    {
        $key = 'feedback:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, config()->integer('feedback.public_limit'))) {
            return true;
        }

        RateLimiter::hit($key, 3600);

        return false;
    }

    private function showReceipt(Feedback $feedback): void
    {
        $this->reference = $feedback->reference();
        $this->threadUrl = $feedback->wants_replies ? $feedback->threadUrl() : null;
        $this->emailed = filled($feedback->contact_email);
        $this->submitted = true;
    }
}
