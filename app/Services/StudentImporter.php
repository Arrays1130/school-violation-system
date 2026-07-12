<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentCase;
use App\Support\SchoolSettings;
use App\Support\StudentImportRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentImporter
{
    private string $passwordHash;

    private string $defaultAcademicYear;

    private int $imported = 0;

    private int $skipped = 0;

    /** @var list<array<string, mixed>> */
    private array $buffer = [];

    public function __construct(?string $defaultAcademicYear = null)
    {
        $configured = config('school.student_default_password');
        $this->passwordHash = $configured
            ? Hash::make($configured)
            : Hash::make(Str::random(24));
        $this->defaultAcademicYear = $defaultAcademicYear
            ?? (string) SchoolSettings::get('current_academic_year', 'SY 2024-2025');
    }

    public function importedCount(): int
    {
        return $this->imported;
    }

    public function skippedCount(): int
    {
        return $this->skipped;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function addRow(array $row): void
    {
        $parsed = StudentImportRow::fromArray($row);

        if ($parsed === null) {
            $this->skipped++;

            return;
        }

        $this->buffer[] = [
            'full_name' => $parsed['full_name'],
            'section' => $parsed['section'] ?? '',
            'year_level' => $parsed['year_level'],
            'department' => $parsed['department'] ?? '',
            'academic_year' => $parsed['academic_year'] ?? $this->defaultAcademicYear,
            'email' => $parsed['email'],
            'guardian_name' => $parsed['guardian_name'],
            'guardian_email' => $parsed['guardian_email'],
            'guardian_phone' => $parsed['guardian_phone'],
            'password' => $this->passwordHash,
            'password_changed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (count($this->buffer) >= 500) {
            $this->flush();
        }
    }

    public function finish(): int
    {
        $this->flush();
        StudentCase::clearDashboardCache();

        return $this->imported;
    }

    private function flush(): void
    {
        if ($this->buffer === []) {
            return;
        }

        Student::upsert(
            $this->buffer,
            ['email'],
            ['full_name', 'department', 'year_level', 'section', 'academic_year', 'updated_at']
        );

        $this->imported += count($this->buffer);
        $this->buffer = [];
    }
}
