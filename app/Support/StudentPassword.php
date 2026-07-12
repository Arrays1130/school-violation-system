<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentPassword
{
    public static function hash(?string $configured = null): string
    {
        $password = $configured ?? config('school.student_default_password');

        return Hash::make($password ?: Str::random(24));
    }
}
