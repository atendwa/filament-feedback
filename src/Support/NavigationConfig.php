<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Filament\Clusters\Cluster;

final class NavigationConfig
{
    /**
     * @return class-string<Cluster>|null
     */
    public static function cluster(): ?string
    {
        $cluster = config('feedback.navigation.cluster');

        return is_string($cluster) && is_subclass_of($cluster, Cluster::class) ? $cluster : null;
    }

    public static function group(string $key): ?string
    {
        $group = config('feedback.navigation.' . $key);

        return is_string($group) ? $group : null;
    }

    public static function sort(string $key): ?int
    {
        $sort = config('feedback.navigation.' . $key);

        return is_int($sort) ? $sort : null;
    }
}
