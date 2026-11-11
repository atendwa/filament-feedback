<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

use Illuminate\Database\Eloquent\Model;

final class ContactEmail
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function for(array $data, ?Model $user): ?string
    {
        if (! ($data['wants_replies'] ?? false)) {
            return null;
        }

        $email = Fields::text($data, 'contact_email');

        return $data['is_anonymous'] ?? false ? $email : $email ?? Users::email($user);
    }
}
