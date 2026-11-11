<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasColor, HasIcon, HasLabel
{
    case Open = 'open';
    case Resolved = 'resolved';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Resolved => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Open => 'heroicon-o-inbox',
            self::Resolved => 'heroicon-o-check-circle',
        };
    }

    public function isFinished(): bool
    {
        return $this === self::Resolved;
    }
}
