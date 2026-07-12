<?php

namespace App\Support;

use App\Models\SystemSetting;

class SchoolSettings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = SystemSetting::where('key', $key)->value('value');

        return $value !== null ? $value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
