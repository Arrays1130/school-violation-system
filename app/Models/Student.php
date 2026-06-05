<?php

namespace App\Models;

use App\Models\Concerns\ScopedForUser;
use App\Support\DepartmentResolver;
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
            \App\Models\StudentCase::clearDashboardCache();
            try { event(new \App\Events\DashboardUpdated('New student registered')); } catch (\Exception $e) {}
        });
        static::updated(function ($student) {
            \App\Models\StudentCase::clearDashboardCache();
            try { event(new \App\Events\DashboardUpdated('Student updated')); } catch (\Exception $e) {}
        });
        static::deleted(function ($student) {
            \App\Models\StudentCase::clearDashboardCache();
            try { event(new \App\Events\DashboardUpdated('Student removed')); } catch (\Exception $e) {}
        });
    }

    protected $fillable = [
        'full_name',
        'section',
        'year_level',
        'department',
        'email',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'password',
        'password_changed_at',
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

    public function getDepartmentShortcutAttribute()
    {
        return DepartmentResolver::longToShortcut($this->department) ?? $this->department;
    }

    public static function resolveDepartmentLongName($acronym): ?string
    {
        return DepartmentResolver::shortcutToLong($acronym);
    }
}
