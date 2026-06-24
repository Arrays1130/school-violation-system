<?php

namespace App\Models;

use App\Models\User;
use App\Support\DepartmentResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StudentCase extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public static function clearDashboardCache($case = null)
    {
        try {
            \Illuminate\Support\Facades\Cache::forget('dashboard.data');
            if ($case && $case->student) {
                \Illuminate\Support\Facades\Cache::forget('dean_dashboard.data.' . md5($case->student->department));
            }
            foreach (DepartmentResolver::cacheKeysForDeanDashboard() as $cacheKey) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }
        } catch (\Exception $e) {}
    }

    protected static function booted()
    {
        static::created(function ($case) {
            static::clearDashboardCache($case);
            try { event(new \App\Events\DashboardUpdated('New case recorded')); } catch (\Exception $e) {}
        });
        static::updated(function ($case) {
            static::clearDashboardCache($case);
            try { event(new \App\Events\DashboardUpdated('Case updated')); } catch (\Exception $e) {}
        });
        static::deleted(function ($case) {
            static::clearDashboardCache($case);
            try { event(new \App\Events\DashboardUpdated('Case deleted')); } catch (\Exception $e) {}
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
        'status',
        'created_by',
        'offense_level',
        'sanction',
        'endorsed_at',
        'closed_at',
        'closed_by',
        'is_archived',
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
