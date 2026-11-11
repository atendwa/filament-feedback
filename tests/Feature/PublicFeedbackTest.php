<?php

declare(strict_types=1);

use Atendwa\Feedback\Livewire\SubmitForm;
use Atendwa\Feedback\Livewire\Thread;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Notifications\FeedbackReceived;
use Atendwa\Feedback\Notifications\StaffAlert;
use Atendwa\Feedback\Tests\Fixtures\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use function Pest\Livewire\livewire;
use Spatie\Permission\Models\Permission;

function responder(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('Respond:Feedback'));

    return $user;
}

function submission(array $extra = []): array
{
    return ['title' => 'Slow queue', 'message' => 'The lunch queue is far too slow.', 'category' => 'General Feedback', ...$extra];
}

it('serves the public form to anyone', function (): void {
    $this->get('/' . config()->string('feedback.path'))->assertOk()->assertSee('Tell us what you think')->assertSee('Stay anonymous');
});

it('lets a guest send anonymous feedback and get a private link by email', function (): void {
    Notification::fake();
    $staff = responder();

    livewire(SubmitForm::class)
        ->set('title', 'Slow queue')->set('message', 'The lunch queue is far too slow.')
        ->set('is_anonymous', '1')->set('wants_replies', value: true)->set('contact_email', 'guest@example.com')
        ->call('submit')->assertHasNoErrors()
        ->assertSet('submitted', true)->assertSet('emailed', true);

    $feedback = Feedback::query()->firstOrFail();

    expect($feedback->is_anonymous)->toBeTrue()->and($feedback->user_id)->toBeNull()
        ->and($feedback->submitterName())->toBe($feedback->alias)
        ->and($feedback->contact_email)->toBe('guest@example.com');

    Notification::assertSentOnDemand(FeedbackReceived::class, fn ($notification, array $channels, $notifiable): bool => $notifiable->routes['mail'] === 'guest@example.com');
    Notification::assertSentTo($staff, StaffAlert::class);

    expect(DB::table('feedback_entries')->value('contact_email'))->not->toBe('guest@example.com');
});

it('opens the conversation only through its signed link and lets the guest reply', function (): void {
    Notification::fake();
    $staff = responder();
    $feedback = Feedback::factory()->repliable()->create(submission());

    $this->get(route('feedback.thread', ['feedback' => $feedback->uuid]))->assertForbidden();
    $this->get($feedback->threadUrl() . 'tampered')->assertForbidden();
    $this->get($feedback->threadUrl())->assertOk()->assertSee('Slow queue');

    livewire(Thread::class, ['feedback' => $feedback])->set('body', 'Any update on this?')->call('reply')->assertHasNoErrors();

    expect($feedback->comments()->where('is_from_submitter', operator: true)->count())->toBe(1);
    Notification::assertSentTo($staff, StaffAlert::class);
});

it('creates no link and opens nothing when the guest wants no replies', function (): void {
    Notification::fake();

    livewire(SubmitForm::class)
        ->set('title', 'Slow queue')->set('message', 'The lunch queue is far too slow.')
        ->call('submit')->assertSet('threadUrl', value: null)->assertSet('submitted', value: true);

    $feedback = Feedback::query()->firstOrFail();

    expect($feedback->wants_replies)->toBeFalse();
    $this->get($feedback->threadUrl())->assertNotFound();
    Notification::assertNotSentTo(new AnonymousNotifiable, FeedbackReceived::class);
});

it('validates the form, ignores bots and limits how much one address can send', function (): void {
    livewire(SubmitForm::class)->call('submit')->assertHasErrors(['title', 'message']);

    livewire(SubmitForm::class)
        ->set('title', 'Buy now')->set('message', 'Cheap pills are on sale today.')->set('website', 'http://spam')
        ->call('submit')->assertSet('submitted', value: true);

    expect(Feedback::query()->count())->toBe(0);

    RateLimiter::clear('feedback:127.0.0.1');
    config(['feedback.public_limit' => 1]);

    $form = fn () => livewire(SubmitForm::class)->set('title', 'Again')->set('message', 'Another message to send.')->call('submit');
    $form()->assertSet('submitted', value: true);
    $form()->assertHasErrors('throttle');

    expect(Feedback::query()->count())->toBe(1);
});

it('shows a signed-in person feedback under their name unless they stay anonymous', function (): void {
    Notification::fake();
    $this->actingAs($user = User::factory()->create(['name' => 'Wanjiru Kamau']));

    livewire(SubmitForm::class)->set('title', 'Named')->set('message', 'This one carries my name.')->call('submit');
    livewire(SubmitForm::class)->set('title', 'Hidden')->set('message', 'This one does not show me.')->set('is_anonymous', '1')->call('submit');

    $named = Feedback::query()->where('title', 'Named')->firstOrFail();
    $hidden = Feedback::query()->where('title', 'Hidden')->firstOrFail();

    expect($named->submitterName())->toBe('Wanjiru Kamau')
        ->and($hidden->submitterName())->not->toContain('Wanjiru')
        ->and($hidden->user_id)->toBe($user->getKey())
        ->and($hidden->toArray())->not->toHaveKeys(['user_id', 'contact_email']);
});
