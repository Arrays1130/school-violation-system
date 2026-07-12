<?php

namespace App\Support;

class StudentImportRow
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string|null>|null
     */
    public static function fromArray(array $row): ?array
    {
        $get = function (array $keys) use ($row): ?string {
            foreach ($keys as $key) {
                if (! array_key_exists($key, $row)) {
                    continue;
                }

                $value = trim((string) $row[$key]);
                if ($value !== '') {
                    return $value;
                }
            }

            return null;
        };

        $firstName = $get(['first_name_required', 'first_name', 'firstnamerequired', 'firstname']) ?? '';
        $lastName = $get(['last_name_required', 'last_name', 'lastnamerequired', 'lastname']) ?? '';
        $fullName = trim("{$firstName} {$lastName}");

        if ($fullName === '') {
            $fullName = $get(['full_name', 'fullname', 'name']) ?? '';
        }

        $email = $get([
            'email_address_required',
            'email_address',
            'emailaddressrequired',
            'emailaddress',
            'email',
        ]);

        $department = $get(['department']);
        $section = $get(['section']);
        $yearLevel = YearLevel::canonical($get(['year_level', 'yearlevel', 'year']));
        $academicYear = $get(['academic_year', 'academicyear', 'school_year', 'schoolyear']);

        if ($department) {
            $department = DepartmentResolver::shortcutToLong($department) ?? $department;
        }

        if ($fullName === '' && $email) {
            $fullName = self::nameFromEmail($email);
        }

        if (empty($email)) {
            return null;
        }

        return [
            'full_name' => $fullName ?: $email,
            'email' => $email,
            'department' => $department,
            'section' => $section,
            'year_level' => $yearLevel,
            'academic_year' => $academicYear,
            'guardian_name' => $get(['guardian_name', 'guardianname']),
            'guardian_email' => $get(['guardian_email', 'guardianemail']),
            'guardian_phone' => $get(['guardian_phone', 'guardianphone']),
        ];
    }

    public static function nameFromEmail(string $email): string
    {
        $local = explode('@', $email)[0] ?? $email;

        return ucwords(str_replace(['.', '_', '-'], ' ', $local));
    }
}
