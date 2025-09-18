<?php

namespace App\Helpers;

class InputHelper
{
    public static function sanitize(string $raw): string
    {
        $cleaned = preg_replace(
            ['/\/\*.*?\*\//', '/--+/', '/[;`\'"<>]/', '/\s+/'],
            ['', ' ', ' ', ' '],
            $raw
        );

        return trim(substr($cleaned, 0, 50));
    }

    public static function filterColleagues(array $colleagues, string $search): array
    {
        $cleaned = self::sanitize($search);
        $filter = strtolower($cleaned);

        if ($filter === '') {
            return $colleagues;
        }

        return array_filter($colleagues, function ($colleague) use ($filter) {
            return str_contains(strtolower($colleague['Name']), $filter);
        });
    }
}
