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

    /**
     * Canonical college shortcut (CCE, CCJE, …) from a shortcut, alias, or long name.
     */
    public static function toShortcut(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $trimmed = trim($value);
        $key = strtoupper($trimmed);

        $aliases = config('school.department_aliases', []);
        if (isset($aliases[$key])) {
            $key = $aliases[$key];
        }

        $departments = config('school.departments', []);
        if (isset($departments[$key])) {
            return $key;
        }

        foreach ($departments as $shortcut => $long) {
            if (strcasecmp($trimmed, (string) $long) === 0) {
                return $shortcut;
            }
        }

        return null;
    }

    /** @return list<string> */
    public static function allShortcuts(): array
    {
        return array_keys(config('school.departments', []));
    }

    /**
     * All stored strings that mean the same college (shortcut, long name, aliases).
     *
     * @return list<string>
     */
    public static function equivalentKeys(?string $value): array
    {
        $shortcut = self::toShortcut($value);
        if ($shortcut === null) {
            return $value !== null && trim($value) !== '' ? [trim($value)] : [];
        }

        $keys = [$shortcut, (string) self::shortcutToLong($shortcut)];

        foreach (config('school.department_aliases', []) as $alias => $target) {
            if (strcasecmp((string) $target, $shortcut) === 0) {
                $keys[] = (string) $alias;
            }
        }

        return array_values(array_unique(array_filter($keys, fn ($key) => $key !== '')));
    }

    /**
     * Dropdown options for dean/user forms: value is the stored shortcut.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        $options = [];

        foreach (config('school.departments', []) as $shortcut => $name) {
            $options[] = [
                'value' => (string) $shortcut,
                'label' => $shortcut.' — '.$name,
            ];
        }

        return $options;
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
