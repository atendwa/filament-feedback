<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class Users
{
    /**
     * @return class-string<Model>
     *
     * @throws InvalidArgumentException
     */
    public static function model(): string
    {
        $model = config('feedback.user_model') ?? config('auth.providers.users.model');

        if (! is_string($model) || ! is_subclass_of($model, Model::class)) {
            throw new InvalidArgumentException('feedback.user_model must name an Eloquent user model.');
        }

        return $model;
    }

    public static function current(): ?Model
    {
        $user = auth()->user();

        return $user instanceof Model ? $user : null;
    }

    public static function name(?Model $user): ?string
    {
        $name = $user?->getAttribute('name');

        return is_string($name) && $name !== '' ? $name : null;
    }

    public static function email(?Model $user): ?string
    {
        $email = $user?->getAttribute('email');

        return is_string($email) && $email !== '' ? $email : null;
    }
}
