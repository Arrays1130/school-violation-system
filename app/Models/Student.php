<?php

namespace App\Models;

use App\Models\Concerns\ScopedForUser;
use App\Support\DepartmentResolver;
use App\Support\DashboardCache;
use App\Support\YearLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Student extends Authenticatable
{
    use HasFactory, Notifiable, ScopedForUser, SoftDeletes, LogsActivity;

    protected static function booted()
    {
        static::created(function ($student) {
            DashboardCache::bust();
            try {
                event(new \App\Events\DashboardUpdated('New student registered'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard event dispatch failed after student create', ['error' => $e->getMessage()]);
            }
        });
        static::updated(function ($student) {
            DashboardCache::bust();
            try {
                event(new \App\Events\DashboardUpdated('Student updated'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard event dispatch failed after student update', ['error' => $e->getMessage()]);
            }
        });
        static::deleted(function ($student) {
            DashboardCache::bust();
            try {
                event(new \App\Events\DashboardUpdated('Student removed'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard event dispatch failed after student delete', ['error' => $e->getMessage()]);
            }
        });
    }

    protected $fillable = [
        'full_name',
        'section',
        'year_level',
        'academic_year',
        'department',
        'phone',
        'email',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'password',
        'password_changed_at',
        'academic_year_graduated',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function setYearLevelAttribute(?string $value): void
    {
        $this->attributes['year_level'] = $value === null || trim($value) === ''
            ? $value
            : (YearLevel::canonical($value) ?? $value);
    }

    public function cases()
    {
        return $this->hasMany(StudentCase::class, 'student_id');
    }

    public function hearings()
    {
        return $this->hasManyThrough(Hearing::class, StudentCase::class, 'student_id', 'case_id');
    }

    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->full_name);
        $initials = '';
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        return $initials ?: strtoupper(substr($this->full_name, 0, 2));
    }

    public function setFullNameAttribute($value)
    {
        $this->attributes['full_name'] = strtoupper($value);
    }

    public function getDepartmentShortcutAttribute()
    {
        return DepartmentResolver::longToShortcut($this->department) ?? $this->department;
    }

    public static function resolveDepartmentLongName($acronym): ?string
    {
        return DepartmentResolver::shortcutToLong($acronym);
    }

    public function scopeForDeanDepartment(Builder $query, ?string $deanDepartment): Builder
    {
        if ($deanDepartment === null || $deanDepartment === '') {
            return $query;
        }

        $longDept = DepartmentResolver::shortcutToLong($deanDepartment);

        return $query->where(function ($sub) use ($deanDepartment, $longDept) {
            $sub->whereRaw('TRIM(department) = ?', [trim($deanDepartment)]);
            if ($longDept && strcasecmp(trim((string) $longDept), trim($deanDepartment)) !== 0) {
                $sub->orWhereRaw('TRIM(department) = ?', [trim((string) $longDept)]);
            }
        });
    }

    /**
     * Route notifications for the Sms channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string|null
     */
    public function routeNotificationForSms($notification)
    {
        // First preference is the student's personal phone.
        // Fallback to guardian phone if they don't have one on file.
        return $this->phone ?: $this->guardian_phone;
    }
}
