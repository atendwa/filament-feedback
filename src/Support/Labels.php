<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

final class Labels
{
    public const array ANONYMITY_ICON = [0 => 'heroicon-o-user', 1 => 'heroicon-o-eye-slash'];

    public const array ANONYMITY_NOTE = [0 => null, 1 => 'Anonymous'];

    public const array REPLIES = [0 => 'Opted out of replies', 1 => 'Wants replies'];

    public const array REPLIES_COLOUR = [0 => 'gray', 1 => 'success'];
}
