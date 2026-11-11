<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Resources\FeedbackResource\Pages;

use Atendwa\Feedback\Filament\Resources\FeedbackResource;
use Filament\Resources\Pages\ListRecords;

class ListFeedback extends ListRecords
{
    protected static string $resource = FeedbackResource::class;
}
