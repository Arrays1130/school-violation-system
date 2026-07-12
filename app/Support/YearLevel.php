<?php

namespace App\Support;

class YearLevel
{
    public const LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

    private const ALIAS_MAP = [
        '1' => '1st Year',
        '1st' => '1st Year',
        '1st year' => '1st Year',
        'first' => '1st Year',
        'first year' => '1st Year',
        'year 1' => '1st Year',
        '2' => '2nd Year',
        '2nd' => '2nd Year',
        '2nd year' => '2nd Year',
        'second' => '2nd Year',
        'second year' => '2nd Year',
        'year 2' => '2nd Year',
        '3' => '3rd Year',
        '3rd' => '3rd Year',
        '3rd year' => '3rd Year',
        'third' => '3rd Year',
        'third year' => '3rd Year',
        'year 3' => '3rd Year',
        '4' => '4th Year',
        '4th' => '4th Year',
        '4th year' => '4th Year',
        'fourth' => '4th Year',
        'fourth year' => '4th Year',
        'year 4' => '4th Year',
    ];

    public static function canonical(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $trimmed = trim($value);

        if (in_array($trimmed, self::LEVELS, true)) {
            return $trimmed;
        }

        return self::ALIAS_MAP[strtolower($trimmed)] ?? $trimmed;
    }

    /**
     * @return list<string>
     */
    public static function aliasesFor(?string $value): array
    {
        $canonical = self::canonical($value);
        if ($canonical === null) {
            return [];
        }

        $aliases = [$canonical];

        foreach (self::ALIAS_MAP as $alias => $level) {
            if ($level === $canonical) {
                $aliases[] = $alias;
            }
        }

        return array_values(array_unique($aliases));
    }

    public static function next(?string $value): ?string
    {
        $canonical = self::canonical($value);
        $index = array_search($canonical, self::LEVELS, true);

        if ($index === false || $index >= count(self::LEVELS) - 1) {
            return null;
        }

        return self::LEVELS[$index + 1];
    }

    /**
     * @return list<string>
     */
    public static function promotableAliases(): array
    {
        return collect(array_slice(self::LEVELS, 0, 3))
            ->flatMap(fn (string $level) => self::aliasesFor($level))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function fourthYearAliases(): array
    {
        return self::aliasesFor('4th Year');
    }
}
