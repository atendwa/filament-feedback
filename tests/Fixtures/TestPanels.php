<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Tests\Fixtures;

use Atendwa\Feedback\Filament\FeedbackPlugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class TestPanels extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('home')->path('')->login()
            ->middleware([EncryptCookies::class, StartSession::class, ShareErrorsFromSession::class])
            ->plugins([FeedbackPlugin::make()->mineOnly()->loginLink()]);
    }

    public function register(): void
    {
        parent::register();

        Filament::registerPanel(fn (): Panel => Panel::make()->id('staff')->path('staff')
            ->middleware([EncryptCookies::class, StartSession::class, ShareErrorsFromSession::class])
            ->plugins([FeedbackPlugin::make()->inboxOnly()]));
    }
}
