<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Illuminate\Database\Eloquent\Model;

final class Tenant
{
    public static function resolve(): ?Model
    {
        $resolver = config('feedback.tenant');
        $tenant = is_callable($resolver) ? $resolver() : null;

        return $tenant instanceof Model ? $tenant : null;
    }
}
