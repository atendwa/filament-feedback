<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Atendwa\Feedback\Models\Feedback;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

final class FeedbackSummary
{
    public static function section(string $viewer): Section
    {
        return Section::make('Feedback')->columns(['default' => 1, 'md' => 2, 'xl' => 3])->schema([
            TextEntry::make('reference')->state(fn (Feedback $record): string => $record->reference()),
            TextEntry::make('status')->badge(),
            TextEntry::make('category')->placeholder('None'),
            TextEntry::make('submitter')->label('From')
                ->state(fn (Feedback $record): string => $record->submitterName())
                ->icon(fn (Feedback $record): string => Labels::ANONYMITY_ICON[(int) $record->is_anonymous])
                ->hint(fn (Feedback $record): ?string => $record->is_anonymous && $viewer === 'staff' ? 'Anonymous by choice' : null),
            TextEntry::make('wants_replies')->label('Replies')
                ->state(fn (Feedback $record): string => Labels::REPLIES[(int) $record->wants_replies])
                ->badge()->color(fn (Feedback $record): string => Labels::REPLIES_COLOUR[(int) $record->wants_replies]),
            TextEntry::make('created_at')->label('Sent')->since()->dateTimeTooltip(),
        ]);
    }
}
