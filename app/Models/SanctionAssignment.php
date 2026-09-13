<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SanctionAssignment extends Model
{
    use HasFactory;

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SUBMITTED_TO_OSA = 'submitted_to_osa';

    protected $fillable = [
        'case_id',
        'assigned_by',
        'required_hours',
        'status',
        'notes',
        'completed_at',
        'completed_by',
        'submitted_to_osa_at',
    ];

    protected $casts = [
        'required_hours' => 'float',
        'completed_at' => 'datetime',
        'submitted_to_osa_at' => 'datetime',
    ];

    public function studentCase(): BelongsTo
    {
        return $this->belongsTo(StudentCase::class, 'case_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function dtrEntries(): HasMany
    {
        return $this->hasMany(DtrEntry::class)->orderByDesc('time_in');
    }

    public function hoursServed(): float
    {
        return round((float) $this->dtrEntries()->sum('hours_served'), 2);
    }

    public function openDtrEntry(): ?DtrEntry
    {
        return $this->dtrEntries()->whereNull('time_out')->latest('time_in')->first();
    }

    public function requirementsMet(): bool
    {
        return $this->hoursServed() >= (float) $this->required_hours;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function toApiArray(): array
    {
        $this->loadMissing(['studentCase.student', 'studentCase.violation', 'dtrEntries.loggedBy']);

        $hoursServed = $this->hoursServed();
        $required = (float) $this->required_hours;
        $open = $this->openDtrEntry();

        return [
            'id' => $this->id,
            'case_id' => $this->case_id,
            'status' => $this->status,
            'required_hours' => $required,
            'hours_served' => $hoursServed,
            'remaining_hours' => max(0, round($required - $hoursServed, 2)),
            'requirements_met' => $hoursServed >= $required,
            'notes' => $this->notes,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'submitted_to_osa_at' => $this->submitted_to_osa_at?->toIso8601String(),
            'has_open_dtr' => $open !== null,
            'open_dtr' => $open?->toApiArray(),
            'case' => [
                'id' => $this->studentCase?->id,
                'case_code' => $this->studentCase?->display_code,
                'status' => $this->studentCase?->status,
                'sanction' => $this->studentCase?->sanction,
                'student' => [
                    'id' => $this->studentCase?->student?->id,
                    'full_name' => $this->studentCase?->student?->full_name,
                    'id_number' => $this->studentCase?->student?->id_number,
                    'department' => $this->studentCase?->student?->department,
                ],
                'violation' => [
                    'id' => $this->studentCase?->violation?->id,
                    'title' => $this->studentCase?->violation?->title,
                    'severity' => $this->studentCase?->violation?->severity,
                ],
            ],
            'dtr_entries' => $this->dtrEntries->map(fn (DtrEntry $entry) => $entry->toApiArray())->values()->all(),
        ];
    }
}
