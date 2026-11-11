<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

final class Responders
{
    public const string PERMISSION = 'Respond:Feedback';

    /**
     * @return list<Model>
     */
    public static function all(): array
    {
        $model = Users::model();

        if (! method_exists($model, 'scopePermission')) {
            return [];
        }

        try {
            $query = $model::query();
            $query->scopes(['permission' => [self::PERMISSION]]);

            return array_values($query->get()->all());
        } catch (Throwable $throwable) {
            Log::notice($throwable->getMessage());

            return [];
        }
    }
}
