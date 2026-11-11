<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Resources\MyFeedbackResource\Pages;

use Atendwa\Feedback\Filament\Actions\GiveFeedback;
use Atendwa\Feedback\Filament\Resources\MyFeedbackResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListMyFeedback extends ListRecords
{
    protected static string $resource = MyFeedbackResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [GiveFeedback::make()->color('primary')];
    }
}
