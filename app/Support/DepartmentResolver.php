<?php

namespace App\Support;

class DepartmentResolver
{
    public static function shortcutToLong(?string $shortcut): ?string
    {
        if ($shortcut === null || $shortcut === '') {
            return $shortcut;
        }

        $key = strtoupper(trim($shortcut));

        $aliases = config('school.department_aliases', []);
        if (isset($aliases[$key])) {
            $key = $aliases[$key];
        }

        return config("school.departments.{$key}") ?? $shortcut;
    }

    public static function longToShortcut(?string $longName): ?string
    {
        if ($longName === null || $longName === '') {
            return $longName;
        }

        $trimmed = trim($longName);
        foreach (config('school.departments', []) as $shortcut => $long) {
            if (strcasecmp($trimmed, $long) === 0) {
                return $shortcut;
            }
        }

        return $longName;
    }

    /** @return list<string> */
    public static function allLongNames(): array
    {
        return array_values(array_unique(config('school.departments', [])));
    }

    /** @return list<string> */
    public static function cacheKeysForDeanDashboard(): array
    {
        $keys = ['dean_dashboard.data.' . md5('All Departments')];

        foreach (self::allLongNames() as $dept) {
            $keys[] = 'dean_dashboard.data.' . md5($dept);
        }

        return $keys;
    }
}
