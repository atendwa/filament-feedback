<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Atendwa\Feedback\Enums\Status;
use Atendwa\Feedback\Models\Feedback;
use Closure;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class InboxTable
{
    public static function configure(Table $table, Closure $url): Table
    {
        return $table
            ->defaultSort('last_activity_at', 'desc')
            ->columns([
                TextColumn::make('reference')->state(fn (Feedback $record): string => $record->reference())
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('id', $direction === 'desc' ? 'desc' : 'asc'))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('id', (int) preg_replace('/\D/', '', $search))),
                TextColumn::make('submitter')->label('From')->sortable(condition: false)->searchable(condition: false)
                    ->state(fn (Feedback $record): string => $record->submitterName())
                    ->icon(fn (Feedback $record): string => Labels::ANONYMITY_ICON[(int) $record->is_anonymous])
                    ->description(fn (Feedback $record): ?string => Labels::ANONYMITY_NOTE[(int) $record->is_anonymous]),
                TextColumn::make('category')->badge()->color('gray')->toggleable(),
                TextColumn::make('title')->searchable()->limit(50)->weight('medium')
                    ->description(fn (Feedback $record): string => str($record->message)->limit(70)->toString()),
                TextColumn::make('status')->badge()->sortable(),
                IconColumn::make('wants_replies')->label('Replies')->boolean()
                    ->trueIcon('heroicon-o-chat-bubble-left-right')->falseIcon('heroicon-o-no-symbol')
                    ->trueColor('success')->falseColor('gray')
                    ->tooltip(fn (Feedback $record): string => Labels::REPLIES[(int) $record->wants_replies]),
                TextColumn::make('comments_count')->searchable(condition: false)->counts(['comments' => fn (Builder $comments) => $comments->where('is_internal', operator: false)])->label('Messages')->badge()->color('gray')->alignCenter(),
                TextColumn::make('last_activity_at')->label('Last activity')->since()->sortable(),
            ])
            ->filters([
                Filter::make('needs_attention')->label('Needs attention')->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('status', Status::Open)),
                SelectFilter::make('status')->options(Status::class)->multiple(),
                SelectFilter::make('category')->options(fn (): array => Feedback::categories()),
                TernaryFilter::make('is_anonymous')->label('Anonymous'),
                TernaryFilter::make('wants_replies')->label('Wants replies'),
            ])
            ->recordUrl($url);
    }
}
