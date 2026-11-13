<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Filament\Resources;

use Atendwa\Feedback\Filament\Resources\FeedbackResource\Pages\ListFeedback;
use Atendwa\Feedback\Filament\Resources\FeedbackResource\Pages\ViewFeedback;
use Atendwa\Feedback\Models\Feedback;
use Atendwa\Feedback\Support\InboxTable;
use Atendwa\Feedback\Support\NavigationConfig;
use Atendwa\Feedback\Support\Presentation;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FeedbackResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-inbox-stack';

    protected static ?string $navigationLabel = 'Feedback inbox';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $model = Feedback::class;

    protected static bool $isScopedToTenant = false;

    /**
     * @return class-string<Cluster>|null
     */
    public static function getCluster(): ?string
    {
        return NavigationConfig::cluster();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationConfig::group('group');
    }

    public static function getNavigationSort(): ?int
    {
        return NavigationConfig::sort('sort');
    }

    public static function getModelLabel(): string
    {
        return 'feedback';
    }

    public static function getPluralModelLabel(): string
    {
        return 'feedback';
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListFeedback::route('/'),
            'view' => ViewFeedback::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function infolist(Schema $schema): Schema
    {
        return Presentation::record($schema, 'staff');
    }

    public static function table(Table $table): Table
    {
        return InboxTable::configure(
            $table,
            fn (Feedback $record): string => static::getUrl('view', ['record' => $record]),
        );
    }
}
