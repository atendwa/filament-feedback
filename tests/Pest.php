<?php

declare(strict_types=1);

use Atendwa\Feedback\Tests\TestCase;
use Filament\Facades\Filament;
use Filament\Panel;

uses(TestCase::class)->in('Feature', 'Unit');

function panelWith(string $resource): string
{
    return collect(Filament::getPanels())->first(fn (Panel $panel): bool => in_array($resource, $panel->getResources(), strict: true))->getId();
}

function usePanel(string $id): void
{
    Filament::setCurrentPanel(Filament::getPanel($id));
}
