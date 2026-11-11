<?php

declare(strict_types=1);

use Atendwa\Feedback\Support\Responders;
use Atendwa\Feedback\Support\Users;
use Atendwa\Feedback\Tests\Fixtures\User;
use Illuminate\Foundation\Auth\User as PlainUser;
use Illuminate\Support\Facades\Blade;

it('shows the application name and no artwork on the public page by default', function (): void {
    config(['app.name' => 'Example App']);

    $this->get(route('feedback.create'))->assertOk()
        ->assertSee('Example App')
        ->assertDontSee('data-test="art"', escape: false);
});

it('shows the logo and artwork the application configures on the public page', function (): void {
    Blade::anonymousComponentPath(__DIR__ . '/../views', 'demo');
    config(['feedback.public.logo' => 'images/logo.png', 'feedback.public.decoration' => 'demo::art']);

    $this->get(route('feedback.create'))->assertOk()
        ->assertSee('images/logo.png')
        ->assertSee('data-test="art"', escape: false);
});

it('uses the configured user model, falling back to the auth provider', function (): void {
    expect(Users::model())->toBe(User::class);

    config(['feedback.user_model' => PlainUser::class]);

    expect(Users::model())->toBe(PlainUser::class);
});

it('alerts no responders when the user model has no permission support', function (): void {
    config(['feedback.user_model' => PlainUser::class]);

    expect(Responders::all())->toBeEmpty();
});
