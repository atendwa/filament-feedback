<?php

declare(strict_types=1);

namespace Atendwa\Feedback;

use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Policies\FeedbackPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FeedbackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/feedback.php', 'feedback');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'feedback');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        Blade::anonymousComponentPath(__DIR__ . '/../resources/views', 'feedback');

        Gate::policy(Feedback::class, FeedbackPolicy::class);
    }
}
