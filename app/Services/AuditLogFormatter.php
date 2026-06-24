<?php

namespace App\Services;

use App\Models\Hearing;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class AuditLogFormatter
{
    protected static array $fieldLabels = [
        'full_name' => 'Full Name',
        'student_id' => 'Student ID',
        'section' => 'Section',
        'year_level' => 'Year Level',
        'department' => 'Department',
        'phone' => 'Phone',
        'email' => 'Email',
        'guardian_name' => 'Guardian Name',
        'guardian_email' => 'Guardian Email',
        'guardian_phone' => 'Guardian Phone',
        'status' => 'Status',
        'description' => 'Description',
        'sanction' => 'Sanction',
        'offense_level' => 'Offense Level',
        'occurred_at' => 'Date Occurred',
        'closed_at' => 'Closed At',
        'closed_by' => 'Closed By',
        'endorsed_at' => 'Endorsed At',
        'violation_id' => 'Violation',
        'scheduled_at' => 'Scheduled At',
        'venue' => 'Venue',
        'notes' => 'Notes',
        'title' => 'Title',
        'code' => 'Code',
        'severity' => 'Severity',
        'name' => 'Name',
        'role' => 'Role',
        'password' => 'Password',
    ];

    protected static array $hiddenFields = [
        'password',
        'remember_token',
        'fcm_token',
    ];

    protected static array $subjectLabels = [
        StudentCase::class => 'Violation Case',
        Student::class => 'Student',
        User::class => 'User Account',
        Hearing::class => 'Hearing',
        Violation::class => 'Violation Rule',
    ];

    public function subjectLabel(Activity $log): string
    {
        $type = $log->subject_type;

        if (! $type) {
            return 'System';
        }

        return self::$subjectLabels[$type] ?? class_basename($type);
    }

    public function subjectUrl(Activity $log): ?string
    {
        if (! $log->subject_type || ! $log->subject_id) {
            return null;
        }

        return match ($log->subject_type) {
            StudentCase::class => route('cases.show', $log->subject_id),
            Student::class => route('students.show', $log->subject_id),
            User::class => auth()->user()?->isSuperAdmin()
                ? route('users.edit', $log->subject_id)
                : null,
            Hearing::class => route('hearings.show', $log->subject_id),
            Violation::class => route('violations.show', $log->subject_id),
            default => null,
        };
    }

    public function eventIcon(string $event): string
    {
        return match ($event) {
            'created' => 'plus-circle',
            'updated' => 'pencil',
            'deleted' => 'trash-2',
            default => 'activity',
        };
    }

    public function eventColor(string $event): string
    {
        return match ($event) {
            'created' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'updated' => 'bg-blue-100 text-blue-800 ring-blue-200',
            'deleted' => 'bg-red-100 text-red-800 ring-red-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }

    /**
     * @return array<int, array{field: string, label: string, old: ?string, new: ?string}>
     */
    public function changedFields(Activity $log): array
    {
        $properties = $log->properties?->toArray() ?? [];
        $old = $properties['old'] ?? [];
        $new = $properties['attributes'] ?? [];
        $fields = [];

        if ($log->event === 'created' && ! empty($new)) {
            foreach ($new as $field => $value) {
                if ($this->shouldHideField($field)) {
                    continue;
                }
                $fields[] = [
                    'field' => $field,
                    'label' => $this->fieldLabel($field),
                    'old' => null,
                    'new' => $this->formatValue($field, $value),
                ];
            }

            return $fields;
        }

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($keys as $field) {
            if ($this->shouldHideField($field)) {
                continue;
            }

            $oldValue = $old[$field] ?? null;
            $newValue = $new[$field] ?? null;

            if ($oldValue == $newValue) {
                continue;
            }

            $fields[] = [
                'field' => $field,
                'label' => $this->fieldLabel($field),
                'old' => $this->formatValue($field, $oldValue),
                'new' => $this->formatValue($field, $newValue),
            ];
        }

        return $fields;
    }

    public function changeSummary(Activity $log): string
    {
        $fields = $this->changedFields($log);

        if (empty($fields)) {
            return $log->description ?: 'No field changes recorded';
        }

        $labels = array_map(fn ($f) => $f['label'], $fields);

        if (count($labels) <= 3) {
            return implode(', ', $labels);
        }

        return implode(', ', array_slice($labels, 0, 3)).' +'.(count($labels) - 3).' more';
    }

    public function fieldLabel(string $field): string
    {
        return self::$fieldLabels[$field] ?? Str::title(str_replace('_', ' ', $field));
    }

    protected function shouldHideField(string $field): bool
    {
        return in_array($field, self::$hiddenFields, true);
    }

    protected function formatValue(string $field, mixed $value): string
    {
        if (in_array($field, self::$hiddenFields, true)) {
            return '[changed]';
        }

        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (in_array($field, ['closed_by', 'created_by', 'uploaded_by'], true) && is_numeric($value)) {
            $name = User::whereKey($value)->value('name');

            return $name ? "{$name} (#{$value})" : "#{$value}";
        }

        if (in_array($field, ['violation_id'], true) && is_numeric($value)) {
            $title = Violation::whereKey($value)->value('title');

            return $title ? "{$title} (#{$value})" : "#{$value}";
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            try {
                return \Carbon\Carbon::parse($value)->format('M d, Y h:i A');
            } catch (\Exception) {
                return (string) $value;
            }
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
