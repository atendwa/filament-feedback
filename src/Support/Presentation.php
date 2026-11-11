<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Atendwa\Feedback\Models\Feedback;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class Presentation
{
    public static function record(Schema $schema, string $viewer): Schema
    {
        return $schema->columns(1)->components([
            FeedbackSummary::section($viewer),

            Callout::make('This person opted out of replies')
                ->description('There is no private link for them, so only internal notes can be added.')
                ->info()
                ->visible(fn (Feedback $record): bool => $viewer === 'staff' && ! $record->wants_replies),

            Section::make('Conversation')->schema([
                ViewEntry::make('thread')->hiddenLabel()->view('feedback::filament.thread')->viewData(['viewer' => $viewer])->columnSpanFull(),
            ]),
        ]);
    }
}
