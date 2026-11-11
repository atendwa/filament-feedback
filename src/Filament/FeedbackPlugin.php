<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament;

use Atendwa\Feedback\Filament\Resources\FeedbackResource;
use Atendwa\Feedback\Filament\Resources\MyFeedbackResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;

final class FeedbackPlugin implements Plugin
{
    private bool $inbox = true;

    private bool $mine = true;

    private bool $loginLink = false;

    public static function make(): self
    {
        return new self;
    }

    public function inboxOnly(): self
    {
        $this->mine = false;

        return $this;
    }

    public function mineOnly(): self
    {
        $this->inbox = false;

        return $this;
    }

    public function loginLink(): self
    {
        $this->loginLink = true;

        return $this;
    }

    /**
     * @param  class-string  $cluster
     */
    public function cluster(string $cluster): self
    {
        config(['feedback.navigation.cluster' => $cluster]);

        return $this;
    }

    public function sort(int $sort): self
    {
        config(['feedback.navigation.sort' => $sort]);

        return $this;
    }

    public function navigationGroup(string $group): self
    {
        config(['feedback.navigation.group' => $group]);

        return $this;
    }

    public function getId(): string
    {
        return 'feedback';
    }

    public function register(Panel $panel): void
    {
        if ($this->loginLink) {
            $panel->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn (): View => view('feedback::partials.login-link'));
        }

        $panel->resources(array_filter([$this->inbox ? FeedbackResource::class : null, $this->mine ? MyFeedbackResource::class : null]));
    }

    public function boot(Panel $panel): void {}
}
