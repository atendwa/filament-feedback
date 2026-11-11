<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Resources;

use Atendwa\Feedback\Filament\Resources\MyFeedbackResource\Pages\ListMyFeedback;
use Atendwa\Feedback\Filament\Resources\MyFeedbackResource\Pages\ViewMyFeedback;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Support\NavigationConfig;
use Atendwa\Feedback\Support\Presentation;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class MyFeedbackResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'My feedback';

    protected static ?string $slug = 'my-feedback';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $model = Feedback::class;

    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationConfig::group('mine_group');
    }

    public static function getNavigationSort(): ?int
    {
        return NavigationConfig::sort('mine_sort');
    }

    public static function getModelLabel(): string
    {
        return 'feedback';
    }

    public static function getPluralModelLabel(): string
    {
        return 'my feedback';
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canView(Model $record): bool
    {
        return $record instanceof Feedback && $record->isNamedFor(auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id())->where('is_anonymous', operator: false);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListMyFeedback::route('/'),
            'view' => ViewMyFeedback::route('/{record}/view'),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return Presentation::record($schema, 'submitter');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_activity_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->weight('medium')->limit(60),
                TextColumn::make('category')->badge()->color('gray'),
                TextColumn::make('status')->badge(),
                TextColumn::make('last_activity_at')->label('Last activity')->since()->sortable(),
            ])
            ->recordUrl(fn (Feedback $record): string => static::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('You have not sent any feedback under your name')
            ->emptyStateDescription('Use Give Feedback on the dashboard. Anonymous feedback is not listed here; use the private link you were given.');
    }
}
