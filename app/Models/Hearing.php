<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hearing extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::created(function ($hearing) {
            \App\Models\StudentCase::clearDashboardCache($hearing->case);
            try { event(new \App\Events\DashboardUpdated('Hearing scheduled')); } catch (\Exception $e) {}
        });
        static::updated(function ($hearing) {
            \App\Models\StudentCase::clearDashboardCache($hearing->case);
            try { event(new \App\Events\DashboardUpdated('Hearing updated')); } catch (\Exception $e) {}
        });
        static::deleted(function ($hearing) {
            \App\Models\StudentCase::clearDashboardCache($hearing->case);
            try { event(new \App\Events\DashboardUpdated('Hearing deleted')); } catch (\Exception $e) {}
        });
    }

    protected $fillable = [
        'case_id',
        'scheduled_at',
        'venue',
        'participants',
        'notes',
        'meeting_minutes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'participants' => 'array',
    ];

    public function case()
    {
        return $this->belongsTo(StudentCase::class, 'case_id');
    }
}
