<?php

namespace App\Support;

use App\Models\Student;

class StudentPromotion
{
    /**
     * Promote 1st–3rd year students one level. Highest levels first so rows are not advanced twice.
     */
    public static function promoteYearLevels(): int
    {
        $steps = [
            '3rd Year' => '4th Year',
            '2nd Year' => '3rd Year',
            '1st Year' => '2nd Year',
        ];

        $count = 0;
        foreach ($steps as $from => $to) {
            $count += Student::query()
                ->whereIn('year_level', YearLevel::aliasesFor($from))
                ->update(['year_level' => $to]);
        }

        return $count;
    }

    /**
     * Stamp all active (non-deleted) students with the new academic year.
     */
    public static function rollForwardAcademicYear(string $newYear): int
    {
        return Student::query()
            ->whereNull('deleted_at')
            ->update(['academic_year' => $newYear]);
    }
}
