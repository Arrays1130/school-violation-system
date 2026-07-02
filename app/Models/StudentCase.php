<?php

namespace App\Models;

use App\Models\User;
use App\Support\DepartmentResolver;
use App\Support\DashboardCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StudentCase extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public static function clearDashboardCache($case = null): void
    {
        DashboardCache::bust();
    }

    /**
     * Create a case with workflow fields set outside mass assignment.
     */
    public static function createForStaff(array $attributes, int $createdBy): self
    {
        $case = new static($attributes);
        $case->forceFill([
            'status' => 'Pending',
            'created_by' => $createdBy,
        ]);
        $case->save();

        return $case->fresh();
    }

    public function transitionStatus(string $status): void
    {
        $this->forceFill(['status' => $status])->save();
    }

    public function markClosed(int $closedBy): void
    {
        $this->forceFill([
            'status' => 'Closed',
            'closed_at' => now(),
            'closed_by' => $closedBy,
        ])->save();
    }

    public function markEndorsed(): void
    {
        $this->forceFill(['endorsed_at' => now()])->save();
    }

    protected static function booted()
    {
        static::created(function ($case) {
            static::clearDashboardCache($case);
            try {
                event(new \App\Events\DashboardUpdated('New case recorded'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard event dispatch failed after case create', ['error' => $e->getMessage()]);
            }
        });
        static::updated(function ($case) {
            static::clearDashboardCache($case);
            try {
                event(new \App\Events\DashboardUpdated('Case updated'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard event dispatch failed after case update', ['error' => $e->getMessage()]);
            }
        });
        static::deleted(function ($case) {
            static::clearDashboardCache($case);
            try {
                event(new \App\Events\DashboardUpdated('Case deleted'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard event dispatch failed after case delete', ['error' => $e->getMessage()]);
            }
        });
    }

    protected $table = 'cases';

    /**
     * Valid case statuses in workflow order.
     */
    public const STATUSES = [
        'Pending',
        'Hearing Scheduled',
        'Hearing',
        'Closed'
    ];

    protected $fillable = [
        'student_id',
        'violation_id',
        'description',
        'witness',
        'occurred_at',
        'offense_level',
        'sanction',
    ];

    protected $casts = [
        'occurred_at'  => 'datetime',
        'endorsed_at'  => 'datetime',
        'closed_at'    => 'datetime',
        'is_archived'  => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }

        if ($user->isDean()) {
            $department = DepartmentResolver::shortcutToLong($user->department);

            return $query->whereHas('student', function (Builder $q) use ($department) {
                $q->whereRaw('TRIM(department) = ?', [trim((string) $department)]);
            });
        }

        return $query->whereRaw('0 = 1');
    }

    // ─── Relationships ──────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function hearing()
    {
        return $this->hasOne(Hearing::class, 'case_id')->latestOfMany('scheduled_at');
    }

    public function hearings()
    {
        return $this->hasMany(Hearing::class, 'case_id')->orderBy('scheduled_at', 'desc');
    }

    public function actions()
    {
        return $this->hasMany(CaseAction::class, 'case_id')->orderBy('created_at', 'asc');
    }

    public function attachments()
    {
        return $this->hasMany(CaseAttachment::class, 'case_id')->orderBy('created_at', 'desc');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Whether the case has been endorsed to the Grievance Committee.
     */
    public function isEndorsedToGrievance(): bool
    {
        return $this->actions()->where('endorsed_to_grievance', true)->exists();
    }

    /**
     * Whether the case can be endorsed (at least 1 OSA action exists).
     */
    public function canEndorseToGrievance(): bool
    {
        return $this->actions()->where('endorsed_to_grievance', false)->exists();
    }

    /**
     * Whether the violation is major/critical severity.
     */
    public function isMajorOffense(): bool
    {
        return in_array($this->violation?->severity, ['Major', 'Critical']);
    }

    public function endorseBlockReason(): ?string
    {
        if ($this->endorsed_at) {
            return 'This case has already been endorsed to the Grievance Committee.';
        }

        if ($this->isMajorOffense() && ! $this->canEndorseToGrievance()) {
            return 'Document at least one OSA intervention before endorsing a major offense.';
        }

        return null;
    }

    public function canEndorse(): bool
    {
        return $this->endorseBlockReason() === null;
    }

    public function closureBlockReason(): ?string
    {
        if ($this->status === 'Closed') {
            return 'This case is already closed.';
        }

        if ($this->isMajorOffense() && ! $this->canEndorseToGrievance() && ! in_array($this->status, ['Hearing', 'Hearing Scheduled'], true)) {
            return 'Major offenses require at least one OSA intervention before closing without a hearing.';
        }

        return null;
    }

    public function canClose(): bool
    {
        return $this->closureBlockReason() === null;
    }

    /**
     * Get the status step index (0-based) for the progress bar.
     */
    public function getStatusStepAttribute(): int
    {
        $steps = ['Pending', 'Hearing Scheduled', 'Hearing', 'Closed'];

        $idx = array_search($this->status, $steps);
        return $idx !== false ? $idx : 0; 
    }
}
