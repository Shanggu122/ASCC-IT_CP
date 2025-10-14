<?php

namespace App\Helpers;

class InputHelperITIS
{
    public static function sanitize(string $raw, int $maxLength = 50): string
    {
        $stripped = strip_tags($raw);

        $cleaned = preg_replace(
            ['/\/\*.*?\*\//', '/--+/', '/[;`\'"<>]/', '/\s+/'],
            ['', ' ', ' ', ' '],
            $stripped
        );

        return trim(mb_substr($cleaned ?? '', 0, $maxLength));
    }

    public static function filterColleagues(array $colleagues, string $search): array
    {
        $filter = strtolower(self::sanitize($search));

        if ($filter === '') {
            return $colleagues;
        }

        return array_filter($colleagues, function ($colleague) use ($filter) {
            $name = strtolower($colleague['Name'] ?? '');
            return str_contains($name, $filter);
        });
    }
}
