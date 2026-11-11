<?php

declare(strict_types=1);

use Atendwa\Feedback\Actions\AddComment;
use Atendwa\Feedback\Actions\SubmitFeedback;
use Atendwa\Feedback\Enums\Status;
use Atendwa\Feedback\Filament\Actions\GiveFeedback;
use Atendwa\Feedback\Filament\FeedbackPlugin;
use Atendwa\Feedback\Filament\Resources\FeedbackResource;
use Atendwa\Feedback\Filament\Resources\FeedbackResource\Pages\ListFeedback;
use Atendwa\Feedback\Filament\Resources\FeedbackResource\Pages\ViewFeedback;
use Atendwa\Feedback\Filament\Resources\MyFeedbackResource;
use Atendwa\Feedback\Filament\Resources\MyFeedbackResource\Pages\ListMyFeedback;
use Atendwa\Feedback\Filament\Resources\MyFeedbackResource\Pages\ViewMyFeedback;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Notifications\FeedbackReplied;
use Atendwa\Feedback\Notifications\TeamAlert;
use Atendwa\Feedback\Support\Tenant;
use Atendwa\Feedback\Tests\Fixtures\User;
use Filament\Clusters\Cluster;
use Filament\Facades\Filament;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use function Pest\Livewire\livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    usePanel(panelWith(FeedbackResource::class));
    Notification::fake();
});

function staffMember(string ...$permissions): User
{
    $user = User::factory()->create();
    collect($permissions)->each(fn (string $name) => $user->givePermissionTo(Permission::findOrCreate($name)));

    return $user;
}

it('shows staff the nickname of an anonymous submission and the name of a named one', function (): void {
    $named = Feedback::factory()->for(User::factory()->create(['name' => 'Wanjiru Kamau']))->create(['title' => 'Named']);
    $hidden = Feedback::factory()->anonymous()->for(User::factory()->create(['name' => 'Otieno Odhiambo']))->create(['title' => 'Hidden']);

    $this->actingAs(staffMember('ViewAny:Feedback', 'View:Feedback'));

    livewire(ListFeedback::class)->assertCanSeeTableRecords([$named, $hidden])
        ->assertSee('Wanjiru Kamau')->assertSee($hidden->alias)->assertDontSee('Otieno Odhiambo');

    livewire(ViewFeedback::class, ['record' => $hidden->getRouteKey()])->assertSee($hidden->alias)->assertDontSee('Otieno Odhiambo');
});

it('keeps the inbox to people with the permission', function (): void {
    $this->actingAs(staffMember());

    expect(FeedbackResource::canViewAny())->toBeFalse();

    $this->actingAs(staffMember('ViewAny:Feedback'));

    expect(FeedbackResource::canViewAny())->toBeTrue();
});

it('lets staff reply, add internal notes and resolve with a comment', function (): void {
    $feedback = Feedback::factory()->repliable('guest@example.com')->create();
    $this->actingAs($staff = staffMember('ViewAny:Feedback', 'View:Feedback', 'Respond:Feedback'));

    livewire(ViewFeedback::class, ['record' => $feedback->getRouteKey()])
        ->callAction('reply', ['body' => 'We are looking into it.'])->assertHasNoActionErrors()
        ->callAction('note', ['body' => 'Check the till logs.'])->assertHasNoActionErrors()
        ->callAction('resolve', ['comment' => 'Fixed on Monday.'])->assertHasNoActionErrors();

    $feedback->refresh();

    expect($feedback->status)->toBe(Status::Resolved)->and($feedback->closed_at)->not->toBeNull()
        ->and($feedback->comments()->count())->toBe(3);

    Notification::assertSentOnDemand(FeedbackReplied::class, fn ($n, $c, $notifiable): bool => $notifiable->routes['mail'] === 'guest@example.com');

    $this->get($feedback->threadUrl())->assertOk()->assertSee('We are looking into it.')->assertDontSee('Check the till logs.');
});

it('refuses public replies when the submitter opted out but still takes notes', function (): void {
    $feedback = Feedback::factory()->create();
    $this->actingAs(staffMember('ViewAny:Feedback', 'View:Feedback', 'Respond:Feedback'));

    livewire(ViewFeedback::class, ['record' => $feedback->getRouteKey()])
        ->assertActionHidden('reply')
        ->callAction('note', ['body' => 'Noted.'])->assertHasNoActionErrors();

    expect($feedback->comments()->where('is_internal', operator: true)->count())->toBe(1);
});

it("lists only a person's own named feedback and lets them reply in the system", function (): void {
    usePanel(panelWith(MyFeedbackResource::class));
    $me = staffMember();
    $mine = Feedback::factory()->for($me)->repliable()->create(['title' => 'Mine']);
    $anonymous = Feedback::factory()->for($me)->anonymous()->create(['title' => 'Anonymous']);
    $theirs = Feedback::factory()->for(User::factory()->create())->create(['title' => 'Theirs']);

    $this->actingAs($me);

    livewire(ListMyFeedback::class)->assertCanSeeTableRecords([$mine])->assertCanNotSeeTableRecords([$anonymous, $theirs]);

    expect(MyFeedbackResource::canView($anonymous))->toBeFalse()->and(MyFeedbackResource::canView($theirs))->toBeFalse();

    livewire(ViewMyFeedback::class, ['record' => $mine->getRouteKey()])
        ->callAction('reply', ['body' => 'Thanks, one more thing.'])->assertHasNoActionErrors();

    expect($mine->comments()->where('is_from_submitter', operator: true)->count())->toBe(1);
});

it('gives signed-in people the feedback action with the anonymity choice', function (): void {
    usePanel(panelWith(MyFeedbackResource::class));
    $this->actingAs($user = staffMember());

    livewire(ListMyFeedback::class)
        ->callAction(GiveFeedback::class, ['title' => 'From the app', 'message' => 'Anonymous but wants replies.', 'is_anonymous' => 1, 'wants_replies' => true, 'contact_email' => 'me@example.com'])
        ->assertHasNoActionErrors();

    $feedback = Feedback::query()->firstOrFail();

    expect($feedback->is_anonymous)->toBeTrue()->and($feedback->user_id)->toBe($user->getKey())
        ->and(Filament::getPanel(panelWith(MyFeedbackResource::class))->getPlugin('feedback'))->not->toBeNull();
});

it('registers the inbox and "My feedback" in a panel each', function (): void {
    expect(panelWith(FeedbackResource::class))->toBeString()->and(panelWith(MyFeedbackResource::class))->toBeString();
});

it('places the inbox where the config says and "My feedback" at the top level', function (): void {
    config(['feedback.navigation.cluster' => FeedbackStubCluster::class, 'feedback.navigation.group' => 'Support']);

    expect(FeedbackResource::getCluster())->toBe(FeedbackStubCluster::class)
        ->and(FeedbackResource::getNavigationGroup())->toBe('Support')
        ->and(MyFeedbackResource::getNavigationGroup())->toBeNull();

    config(['feedback.navigation.cluster' => null]);

    expect(FeedbackResource::getCluster())->toBeNull();

    FeedbackPlugin::make()->cluster(FeedbackStubCluster::class)->navigationGroup('Help');

    expect(FeedbackResource::getCluster())->toBe(FeedbackStubCluster::class)->and(FeedbackResource::getNavigationGroup())->toBe('Help');
});

it('will not resolve without a comment, and sends the comment to the submitter as the last reply', function (): void {
    $feedback = Feedback::factory()->repliable('guest@example.com')->create();
    $this->actingAs(staffMember('ViewAny:Feedback', 'View:Feedback', 'Respond:Feedback'));

    livewire(ViewFeedback::class, ['record' => $feedback->getRouteKey()])
        ->callAction('resolve', ['comment' => ''])->assertHasActionErrors(['comment' => 'required']);

    expect($feedback->refresh()->status)->toBe(Status::Open);

    livewire(ViewFeedback::class, ['record' => $feedback->getRouteKey()])
        ->callAction('resolve', ['comment' => 'All sorted, thank you.'])->assertHasNoActionErrors();

    expect($feedback->refresh()->status)->toBe(Status::Resolved)
        ->and($feedback->comments()->latest('id')->first())->body->toBe('All sorted, thank you.')->is_internal->toBeFalse();

    Notification::assertSentOnDemand(FeedbackReplied::class, fn (FeedbackReplied $notification): bool => $notification->resolved);
});

it('keeps the resolution comment internal when the submitter opted out of replies', function (): void {
    $feedback = Feedback::factory()->create();
    $this->actingAs(staffMember('ViewAny:Feedback', 'View:Feedback', 'Respond:Feedback'));

    livewire(ViewFeedback::class, ['record' => $feedback->getRouteKey()])
        ->callAction('resolve', ['comment' => 'Nothing more to do.'])->assertHasNoActionErrors();

    expect($feedback->refresh()->status)->toBe(Status::Resolved)
        ->and($feedback->comments()->first()->is_internal)->toBeTrue();

    Notification::assertNotSentTo(new AnonymousNotifiable, FeedbackReplied::class);
});

it('reopens a resolved conversation and offers only open and resolved', function (): void {
    $feedback = Feedback::factory()->create(['status' => Status::Resolved, 'closed_at' => now()]);
    $this->actingAs(staffMember('ViewAny:Feedback', 'View:Feedback', 'Respond:Feedback'));

    livewire(ViewFeedback::class, ['record' => $feedback->getRouteKey()])
        ->assertActionHidden('resolve')->callAction('reopen')->assertHasNoActionErrors();

    expect($feedback->refresh()->status)->toBe(Status::Open)->and($feedback->closed_at)->toBeNull()
        ->and(Status::cases())->toHaveCount(2);
});

it('emails the team about new feedback and new replies only when an address is configured', function (): void {
    config(['feedback.notify_email' => 'team@example.com, not-an-address']);
    $feedback = app(SubmitFeedback::class)->handle(['title' => 'Cold food', 'message' => 'The food was cold.', 'wants_replies' => true, 'contact_email' => 'guest@example.com']);
    app(AddComment::class)->handle($feedback, 'Any update?');

    Notification::assertSentOnDemandTimes(TeamAlert::class, 2);
    Notification::assertSentOnDemand(TeamAlert::class, fn ($n, $channels, $notifiable): bool => $notifiable->routes['mail'] === ['team@example.com']);

    Notification::assertSentOnDemand(TeamAlert::class, fn (TeamAlert $alert): bool => $alert->toMail(new AnonymousNotifiable)->actionUrl === FeedbackResource::getUrl('view', ['record' => $feedback], tenant: Tenant::resolve())
        && str_contains($alert->toMail(new AnonymousNotifiable)->actionUrl, (string) $feedback->getRouteKey()));

    Notification::fake();
    config(['feedback.notify_email' => null]);
    app(SubmitFeedback::class)->handle(['title' => 'Again', 'message' => 'Another message here.']);

    Notification::assertNotSentTo(new AnonymousNotifiable, TeamAlert::class);
    Notification::assertSentOnDemandTimes(TeamAlert::class, 0);
});

class FeedbackStubCluster extends Cluster {}
