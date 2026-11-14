<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Support;

final class Alias
{
    private const array ADJECTIVES = [
        'Calm',
        'Bright',
        'Quiet',
        'Swift',
        'Kind',
        'Brave',
        'Gentle',
        'Witty',
        'Steady',
        'Sunny',
        'Curious',
        'Humble',
    ];

    private const array ANIMALS = [
        'Heron',
        'Zebra',
        'Lion',
        'Otter',
        'Falcon',
        'Gazelle',
        'Hare',
        'Ibis',
        'Kudu',
        'Lynx',
        'Oryx',
        'Plover',
    ];

    public static function for(string $seed): string
    {
        $hash = crc32($seed);

        return self::ADJECTIVES[$hash % count(self::ADJECTIVES)] . ' ' . self::ANIMALS[intdiv($hash, 7) % count(self::ANIMALS)];
    }
}
