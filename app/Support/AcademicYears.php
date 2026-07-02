<?php

namespace App\Support;

class AcademicYears
{
    /**
     * @return array<int, string>
     */
    public static function options(int $range = 5): array
    {
        $currentYear = (int) date('Y');
        $years = [];

        for ($i = $currentYear - $range; $i <= $currentYear + $range; $i++) {
            $years[] = "SY {$i}-".($i + 1);
        }

        return $years;
    }
}
