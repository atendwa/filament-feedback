<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

final class Fields
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function text(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
